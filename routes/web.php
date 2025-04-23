<?php

use App\Http\Controllers\KanbanBoardController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Publiczne trasy
Route::get('/', function () {
    return view('welcome');
});

// Trasy chronione autoryzacją
Route::middleware(['auth'])->group(function () {
    // Strona główna/dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Profil użytkownika
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Tablice Kanban
    Route::resource('kanban-boards', KanbanBoardController::class);
    
    // Alternatywne trasy dla tablic z innym kontrolerem (opcjonalnie)
    Route::resource('boards', BoardController::class);
    
    // Kolumny
    Route::post('/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::put('/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::delete('/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
    
    // Karty
    Route::resource('cards', CardController::class)->except(['index']);
    
    // Dodatkowa trasa dla drag-and-drop (aktualizacja pozycji karty)
    Route::post('/cards/{card}/move', [CardController::class, 'move'])->name('cards.move')->middleware('auth');
    
    // Komentarze
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// Trasy autoryzacji (login, register, logout) są zdefiniowane w pliku auth.php
require __DIR__.'/auth.php';
