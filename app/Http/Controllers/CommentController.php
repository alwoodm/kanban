<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'card_id' => 'required|exists:cards,id',
        ]);

        // Pobierz kartę i sprawdź uprawnienia
        $card = Card::with('column.board')->findOrFail($request->card_id);
        
        // Sprawdź czy użytkownik ma dostęp do tablicy, w której znajduje się karta
        if (Auth::id() !== $card->column->board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Utwórz komentarz
        $comment = Comment::create([
            'content' => $request->content,
            'card_id' => $card->id,
            'user_id' => Auth::id(),
        ]);

        // Załaduj użytkownika dla wyświetlenia danych w odpowiedzi
        $comment->load('user');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Comment added successfully',
                'comment' => $comment,
            ], 201);
        }

        return redirect()->back()->with('success', 'Comment added successfully');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment)
    {
        // Sprawdź czy użytkownik jest autorem komentarza lub właścicielem tablicy
        $isAuthor = Auth::id() === $comment->user_id;
        $isBoardOwner = Auth::id() === $comment->card->column->board->user_id;
        
        if (!$isAuthor && !$isBoardOwner) {
            abort(403, 'Unauthorized action.');
        }

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Comment deleted successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Comment deleted successfully');
    }
}
