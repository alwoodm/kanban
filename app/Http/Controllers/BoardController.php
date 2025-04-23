<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BoardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the boards.
     */
    public function index()
    {
        $boards = Auth::user()->boards;
        return view('boards.index', compact('boards'));
    }

    /**
     * Show the form for creating a new board.
     */
    public function create()
    {
        return view('boards.create');
    }

    /**
     * Store a newly created board in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Auth::user()->boards()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('boards.index')
            ->with('success', 'Board created successfully.');
    }

    /**
     * Display the specified board with columns.
     */
    public function show(Board $board)
    {
        // Check if user owns this board
        if (Auth::id() !== $board->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load columns to reduce database queries
        $board->load('columns.cards');
        
        return view('boards.show', compact('board'));
    }

    /**
     * Show the form for editing the specified board.
     */
    public function edit(Board $board)
    {
        // Check if user owns this board
        if (Auth::id() !== $board->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('boards.edit', compact('board'));
    }

    /**
     * Update the specified board in storage.
     */
    public function update(Request $request, Board $board)
    {
        // Check if user owns this board
        if (Auth::id() !== $board->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $board->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('boards.index')
            ->with('success', 'Board updated successfully.');
    }

    /**
     * Remove the specified board from storage.
     */
    public function destroy(Board $board)
    {
        // Check if user owns this board
        if (Auth::id() !== $board->user_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $board->delete();

        return redirect()->route('boards.index')
            ->with('success', 'Board deleted successfully.');
    }
}
