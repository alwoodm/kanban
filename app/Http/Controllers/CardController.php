<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Column;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new card.
     */
    public function create(Request $request)
    {
        $column = Column::findOrFail($request->column_id);
        
        // Sprawdź czy użytkownik ma uprawnienia do kolumny
        if (Auth::id() !== $column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('cards.create', [
            'column' => $column,
            'users' => User::all(),
        ]);
    }

    /**
     * Store a newly created card in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'column_id' => 'required|exists:columns,id',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:' . implode(',', [
                Card::PRIORITY_LOW, 
                Card::PRIORITY_MEDIUM, 
                Card::PRIORITY_HIGH
            ]),
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : redirect()->back()->withErrors($validator)->withInput();
        }

        $column = Column::findOrFail($request->column_id);
        
        // Sprawdź czy użytkownik ma uprawnienia do kolumny
        if (Auth::id() !== $column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Sprawdź limit kart w kolumnie
        if ($column->card_limit && $column->cards()->count() >= $column->card_limit) {
            $error = 'Column card limit reached';
            return $request->expectsJson()
                ? response()->json(['error' => $error], 422)
                : redirect()->back()->withErrors(['limit' => $error])->withInput();
        }

        // Określ kolejność dla nowej karty (na końcu kolumny)
        $maxOrder = Card::where('column_id', $request->column_id)->max('order') ?? 0;

        $card = Card::create([
            'title' => $request->title,
            'description' => $request->description,
            'column_id' => $request->column_id,
            'order' => $maxOrder + 1,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? Card::PRIORITY_MEDIUM,
        ]);

        // Przypisz użytkowników do karty
        if ($request->has('users')) {
            $card->users()->sync($request->users);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Card created successfully',
                'card' => $card->load('users'),
            ], 201);
        }

        return redirect()->route('kanban-boards.show', $column->board->id)
            ->with('success', 'Card created successfully');
    }

    /**
     * Display the specified card.
     */
    public function show(Card $card)
    {
        // Sprawdź czy użytkownik ma uprawnienia do karty
        if (Auth::id() !== $card->column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Załaduj relacje
        $card->load(['users', 'comments.user']);

        if (request()->expectsJson()) {
            return response()->json(['card' => $card]);
        }

        return view('cards.show', compact('card'));
    }

    /**
     * Show the form for editing the specified card.
     */
    public function edit(Card $card)
    {
        // Sprawdź czy użytkownik ma uprawnienia do karty
        if (Auth::id() !== $card->column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::all();
        $board = $card->column->board;
        $columns = $board->columns;

        return view('cards.edit', compact('card', 'users', 'columns'));
    }

    /**
     * Update the specified card in storage.
     */
    public function update(Request $request, Card $card)
    {
        // Sprawdź czy użytkownik ma uprawnienia do karty
        if (Auth::id() !== $card->column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'column_id' => 'sometimes|exists:columns,id',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:' . implode(',', [
                Card::PRIORITY_LOW, 
                Card::PRIORITY_MEDIUM, 
                Card::PRIORITY_HIGH
            ]),
            'order' => 'sometimes|integer|min:0',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : redirect()->back()->withErrors($validator)->withInput();
        }

        $oldColumnId = $card->column_id;
        $newColumnId = $request->column_id ?? $oldColumnId;

        // Jeśli zmieniono kolumnę
        if ($newColumnId != $oldColumnId) {
            $newColumn = Column::findOrFail($newColumnId);
            
            // Sprawdź czy kolumna należy do tej samej tablicy
            if ($newColumn->board->id !== $card->column->board->id) {
                abort(403, 'Cannot move card to a different board');
            }

            // Sprawdź limit kart w nowej kolumnie
            if ($newColumn->card_limit && $newColumn->cards()->count() >= $newColumn->card_limit) {
                $error = 'Column card limit reached';
                return $request->expectsJson()
                    ? response()->json(['error' => $error], 422)
                    : redirect()->back()->withErrors(['limit' => $error])->withInput();
            }
        }

        // Przenieś lub zmień pozycję karty
        if ($request->has('order') || $newColumnId != $oldColumnId) {
            $this->moveCard($card, $newColumnId, $request->order ?? null);
        }

        // Aktualizuj pozostałe dane karty
        $card->update([
            'title' => $request->title ?? $card->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
        ]);

        // Aktualizuj przypisanych użytkowników
        if ($request->has('users')) {
            $card->users()->sync($request->users);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Card updated successfully',
                'card' => $card->fresh(['users']),
            ]);
        }

        return redirect()->route('kanban-boards.show', $card->column->board->id)
            ->with('success', 'Card updated successfully');
    }

    /**
     * Remove the specified card from storage.
     */
    public function destroy(Card $card)
    {
        // Sprawdź czy użytkownik ma uprawnienia do karty
        if (Auth::id() !== $card->column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $columnId = $card->column_id;
        $boardId = $card->column->board->id;
        
        $card->delete();
        
        // Aktualizuj kolejność pozostałych kart
        $this->normalizeCardOrder($columnId);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Card deleted successfully'
            ]);
        }

        return redirect()->route('kanban-boards.show', $boardId)
            ->with('success', 'Card deleted successfully');
    }

    /**
     * Aktualizuje pozycję karty (zmiana kolejności i/lub kolumny)
     *
     * @param Request $request
     * @param Card $card
     * @return JsonResponse
     */
    public function move(Request $request, Card $card)
    {
        // Sprawdź czy użytkownik ma uprawnienia do karty
        if (Auth::id() !== $card->column->board->user_id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'column_id' => 'required|exists:columns,id',
            'order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $oldColumnId = $card->column_id;
        $newColumnId = $request->column_id;
        $oldOrder = $card->order;
        $newOrder = $request->order;

        // Jeśli zmieniono kolumnę
        if ($newColumnId != $oldColumnId) {
            $newColumn = Column::findOrFail($newColumnId);
            
            // Sprawdź czy kolumna należy do tej samej tablicy
            if ($newColumn->board->id !== $card->column->board->id) {
                return response()->json(['error' => 'Cannot move card to a different board'], 422);
            }

            // Sprawdź limit kart w nowej kolumnie
            if ($newColumn->card_limit && $newColumn->cards()->count() >= $newColumn->card_limit) {
                return response()->json(['error' => 'Column card limit reached'], 422);
            }
        }

        DB::transaction(function () use ($card, $oldColumnId, $newColumnId, $oldOrder, $newOrder) {
            // Jeśli karta jest przenoszona do innej kolumny
            if ($newColumnId != $oldColumnId) {
                // Aktualizuj kolejność w starej kolumnie po usunięciu karty
                Card::where('column_id', $oldColumnId)
                    ->where('order', '>', $oldOrder)
                    ->decrement('order');
                
                // Przesuń karty w nowej kolumnie, aby zrobić miejsce
                Card::where('column_id', $newColumnId)
                    ->where('order', '>=', $newOrder)
                    ->increment('order');
                
                // Zaktualizuj kolumnę i pozycję karty
                $card->column_id = $newColumnId;
                $card->order = $newOrder;
                $card->save();
            } 
            // Jeśli tylko zmiana pozycji w tej samej kolumnie
            elseif ($newOrder != $oldOrder) {
                if ($newOrder > $oldOrder) {
                    // Przesuń w dół: zmniejsz order dla kart między starą i nową pozycją
                    Card::where('column_id', $oldColumnId)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    // Przesuń w górę: zwiększ order dla kart między nową i starą pozycją
                    Card::where('column_id', $oldColumnId)
                        ->where('order', '<', $oldOrder)
                        ->where('order', '>=', $newOrder)
                        ->increment('order');
                }
                
                // Zaktualizuj pozycję karty
                $card->order = $newOrder;
                $card->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Card position updated successfully',
            'card' => $card->fresh()
        ]);
    }

    /**
     * Normalizuje kolejność kart po usunięciu (bez luk)
     */
    private function normalizeCardOrder(int $columnId): void
    {
        $cards = Card::where('column_id', $columnId)
            ->orderBy('order')
            ->get();

        foreach ($cards as $index => $card) {
            $card->order = $index + 1;
            $card->save();
        }
    }
}
