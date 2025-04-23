<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\KanbanBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ColumnController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Store a newly created column in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kanban_board_id' => 'required|exists:kanban_boards,id',
        ]);

        // Sprawdź czy użytkownik ma uprawnienia do tablicy
        $board = KanbanBoard::findOrFail($request->kanban_board_id);
        if (Auth::id() !== $board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Określ kolejność dla nowej kolumny (na końcu)
        $maxOrder = Column::where('kanban_board_id', $request->kanban_board_id)
            ->max('order') ?? 0;

        $column = Column::create([
            'name' => $request->name,
            'kanban_board_id' => $request->kanban_board_id,
            'order' => $maxOrder + 1,
            'card_limit' => $request->card_limit,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Column created successfully',
                'column' => $column,
            ], 201);
        }

        return redirect()->back()
            ->with('success', 'Column created successfully');
    }

    /**
     * Update the specified column in storage.
     */
    public function update(Request $request, Column $column)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'order' => 'sometimes|required|integer|min:0',
            'card_limit' => 'nullable|integer|min:1',
        ]);

        // Sprawdź czy użytkownik ma uprawnienia do kolumny
        if (Auth::id() !== $column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Jeśli zmieniono pozycję, zaktualizuj kolejność kolumn
        if ($request->has('order') && $request->order != $column->order) {
            $this->reorderColumns($column, $request->order);
        }

        $column->update($request->only(['name', 'card_limit']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Column updated successfully',
                'column' => $column,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Column updated successfully');
    }

    /**
     * Remove the specified column from storage.
     */
    public function destroy(Column $column)
    {
        // Sprawdź czy użytkownik ma uprawnienia do kolumny
        if (Auth::id() !== $column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Zapisz ID tablicy przed usunięciem kolumny
        $boardId = $column->kanban_board_id;
        
        $column->delete();

        // Aktualizuj kolejność pozostałych kolumn
        $this->normalizeColumnOrder($boardId);

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Column deleted successfully'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Column deleted successfully');
    }

    /**
     * Zmienia kolejność kolumny i aktualizuje porządek innych kolumn
     */
    private function reorderColumns(Column $column, int $newOrder): void
    {
        $oldOrder = $column->order;
        $boardId = $column->kanban_board_id;

        if ($newOrder > $oldOrder) {
            // Przesuń kolumnę w dół: zmniejsz order dla kolumn między starym i nowym porządkiem
            Column::where('kanban_board_id', $boardId)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->decrement('order');
        } else {
            // Przesuń kolumnę w górę: zwiększ order dla kolumn między nowym i starym porządkiem
            Column::where('kanban_board_id', $boardId)
                ->where('order', '<', $oldOrder)
                ->where('order', '>=', $newOrder)
                ->increment('order');
        }

        // Ustaw nową pozycję dla bieżącej kolumny
        $column->order = $newOrder;
        $column->save();
    }

    /**
     * Normalizuje kolejność kolumn po usunięciu (bez luk)
     */
    private function normalizeColumnOrder(int $boardId): void
    {
        $columns = Column::where('kanban_board_id', $boardId)
            ->orderBy('order')
            ->get();

        foreach ($columns as $index => $column) {
            $column->order = $index + 1;
            $column->save();
        }
    }
}
