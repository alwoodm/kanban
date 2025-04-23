<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 text-gray-800 font-weight-bold">
                {{ $card->title }}
            </h2>
            <div class="d-flex">
                <a href="{{ route('boards.show', $card->column->board->id) }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('Wróć do tablicy') }}
                </a>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="cardActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear me-1"></i> {{ __('Akcje') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="cardActionsDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('cards.edit', $card->id) }}">
                                <i class="bi bi-pencil me-2"></i> {{ __('Edytuj kartę') }}
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('cards.destroy', $card->id) }}" method="POST" onsubmit="return confirm('{{ __('Czy na pewno chcesz usunąć tę kartę?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-trash me-2"></i> {{ __('Usuń kartę') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mt-4">
        <!-- Lewa kolumna - szczegóły karty -->
        <div class="col-md-8">
            <!-- Podstawowe informacje -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ __('Szczegóły karty') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">{{ __('Opis') }}</h6>
                        <div class="p-3 bg-light rounded">
                            @if($card->description)
                                <p class="mb-0">{{ $card->description }}</p>
                            @else
                                <p class="text-muted mb-0 fst-italic">{{ __('Brak opisu') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">{{ __('Priorytet') }}</h6>
                            @php
                                $priorityClass = [
                                    'niski' => 'bg-success',
                                    'średni' => 'bg-warning',
                                    'wysoki' => 'bg-danger'
                                ][$card->priority] ?? 'bg-secondary';
                                
                                $priorityIcon = [
                                    'niski' => 'arrow-down',
                                    'średni' => 'arrow-right',
                                    'wysoki' => 'arrow-up'
                                ][$card->priority] ?? 'dash';
                            @endphp
                            <span class="badge {{ $priorityClass }} p-2">
                                <i class="bi bi-{{ $priorityIcon }}"></i> {{ $card->priority }}
                            </span>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">{{ __('Termin') }}</h6>
                            @if($card->due_date)
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-event me-2"></i>
                                    <span>{{ \Carbon\Carbon::parse($card->due_date)->format('d.m.Y') }}</span>
                                    
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $dueDate = \Carbon\Carbon::parse($card->due_date);
                                        $diffDays = $now->diffInDays($dueDate, false);
                                    @endphp
                                    
                                    @if($diffDays < 0)
                                        <span class="badge bg-danger ms-2">{{ __('Termin minął') }}</span>
                                    @elseif($diffDays < 2)
                                        <span class="badge bg-warning ms-2">{{ __('Wkrótce') }}</span>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted mb-0 fst-italic">{{ __('Brak terminu') }}</p>
                            @endif
                        </div>

                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">{{ __('Kolumna') }}</h6>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-layout-three-columns me-2"></i>
                                <span>{{ $card->column->name }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">{{ __('Data utworzenia') }}</h6>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history me-2"></i>
                                <span>{{ $card->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Komentarze -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ __('Komentarze') }}</h5>
                </div>
                <div class="card-body">
                    <!-- Lista komentarzy -->
                    @if($card->comments && $card->comments->count() > 0)
                        <div class="mb-4">
                            @foreach($card->comments as $comment)
                                <div class="d-flex mb-3 p-3 bg-light rounded">
                                    <div class="flex-shrink-0 me-3">
                                        @if($comment->user->avatar)
                                            <img src="{{ asset('storage/' . $comment->user->avatar) }}" 
                                                alt="{{ $comment->user->name }}" class="rounded-circle" width="40" height="40">
                                        @else
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                                style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0">{{ $comment->user->name }}</h6>
                                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-0">{{ $comment->content }}</p>
                                        
                                        @if(Auth::id() === $comment->user_id)
                                            <div class="mt-2 text-end">
                                                <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" 
                                                    class="d-inline" onsubmit="return confirm('{{ __('Czy na pewno chcesz usunąć ten komentarz?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                                        <i class="bi bi-trash"></i> {{ __('Usuń') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-4">{{ __('Brak komentarzy') }}</p>
                    @endif

                    <!-- Formularz dodawania komentarza -->
                    <form method="POST" action="{{ route('comments.store') }}">
                        @csrf
                        <input type="hidden" name="card_id" value="{{ $card->id }}">
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">{{ __('Dodaj komentarz') }}</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="3" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> {{ __('Wyślij komentarz') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Prawa kolumna - metadane i przypisania -->
        <div class="col-md-4">
            <!-- Przypisani użytkownicy -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ __('Przypisani użytkownicy') }}</h5>
                </div>
                <div class="card-body">
                    @if($card->users && $card->users->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($card->users as $user)
                                <li class="list-group-item d-flex align-items-center px-0">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" 
                                            alt="{{ $user->name }}" class="rounded-circle me-3" width="32" height="32">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                            style="width: 32px; height: 32px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span>{{ $user->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center mb-0">{{ __('Brak przypisanych użytkowników') }}</p>
                    @endif
                </div>
            </div>

            <!-- Etykiety -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ __('Etykiety') }}</h5>
                </div>
                <div class="card-body">
                    @if($card->labels && $card->labels->count() > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($card->labels as $label)
                                <div class="badge p-2" style="background-color: {{ $label->color }}">
                                    {{ $label->name }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">{{ __('Brak etykiet') }}</p>
                    @endif
                </div>
            </div>

            <!-- Działania -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ __('Działania') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('cards.edit', $card->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> {{ __('Edytuj kartę') }}
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
                            <i class="bi bi-printer me-1"></i> {{ __('Drukuj') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
