<?php

namespace App\Http\Controllers;

use App\Models\KanbanBoard;
use Illuminate\Http\Request;

class KanbanBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $boards = auth()->user()->kanbanBoards;
        return view('kanban.index', compact('boards'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kanban.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        auth()->user()->kanbanBoards()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('kanban-boards.index')
            ->with('success', 'Kanban board created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KanbanBoard $kanbanBoard)
    {
        return view('kanban.show', compact('kanbanBoard'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KanbanBoard $kanbanBoard)
    {
        return view('kanban.edit', compact('kanbanBoard'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KanbanBoard $kanbanBoard)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kanbanBoard->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('kanban-boards.index')
            ->with('success', 'Kanban board updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KanbanBoard $kanbanBoard)
    {
        $kanbanBoard->delete();

        return redirect()->route('kanban-boards.index')
            ->with('success', 'Kanban board deleted successfully.');
    }
}
