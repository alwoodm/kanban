<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 text-gray-800 font-weight-bold">
                {{ $board->name }}
            </h2>
            <div class="d-flex">
                <a href="{{ route('boards.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('Wróć do listy') }}
                </a>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="boardActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear me-1"></i> {{ __('Akcje') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="boardActionsDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('boards.edit', $board->id) }}">
                                <i class="bi bi-pencil me-2"></i> {{ __('Edytuj tablicę') }}
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('boards.destroy', $board->id) }}" method="POST" onsubmit="return confirm('{{ __('Czy na pewno chcesz usunąć tę tablicę?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-trash me-2"></i> {{ __('Usuń tablicę') }}
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
    
    <!-- Board Description -->
    @if($board->description)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title h6">{{ __('Opis tablicy') }}</h5>
                <p class="card-text mb-0">{{ $board->description }}</p>
            </div>
        </div>
    @endif

    <!-- Add Column Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('columns.store') }}" class="row g-2 align-items-center">
                @csrf
                <input type="hidden" name="board_id" value="{{ $board->id }}">
                
                <div class="col-md-4">
                    <label for="column_name" class="visually-hidden">{{ __('Nazwa kolumny') }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="column_name" name="name" placeholder="{{ __('Nazwa nowej kolumny') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-2">
                    <label for="card_limit" class="visually-hidden">{{ __('Limit kart') }}</label>
                    <input type="number" class="form-control" id="card_limit" name="card_limit" 
                           placeholder="{{ __('Limit kart (opcjonalnie)') }}" min="0">
                </div>
                
                <div class="col-auto">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('Dodaj kolumnę') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Columns Container -->
    <div class="kanban-board-container mb-4">
        @if($board->columns->isEmpty())
            <div class="alert alert-info">
                {{ __('Ta tablica nie ma jeszcze żadnych kolumn. Dodaj pierwszą kolumnę używając formularza powyżej.') }}
            </div>
        @else
            <div class="kanban-board d-flex overflow-auto pb-3" id="kanban-board">
                @foreach($board->columns->sortBy('order') as $column)
                    <div class="kanban-column card me-3" data-column-id="{{ $column->id }}" style="min-width: 300px; max-width: 300px;">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                            <div>
                                <h5 class="mb-0 d-inline">{{ $column->name }}</h5>
                                <span class="badge bg-secondary ms-1">{{ $column->cards->count() }}{{ $column->card_limit ? '/'.$column->card_limit : '' }}</span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item edit-column-btn" href="#" data-column-id="{{ $column->id }}" data-column-name="{{ $column->name }}" data-column-limit="{{ $column->card_limit }}">
                                            <i class="bi bi-pencil me-2"></i> {{ __('Edytuj') }}
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('columns.destroy', $column->id) }}" method="POST" onsubmit="return confirm('{{ __('Czy na pewno chcesz usunąć tę kolumnę wraz ze wszystkimi kartami?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i> {{ __('Usuń') }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-body p-2">
                            <button class="btn btn-sm btn-outline-primary w-100 mb-2 add-card-btn" data-column-id="{{ $column->id }}">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Dodaj kartę') }}
                            </button>
                            
                            <div class="kanban-cards" id="column-cards-{{ $column->id }}">
                                @if($column->cards->isEmpty())
                                    <div class="text-center text-muted py-3 empty-column-placeholder">
                                        <i class="bi bi-sticky"></i>
                                        <p class="mb-0 small">{{ __('Brak kart') }}</p>
                                    </div>
                                @else
                                    @foreach($column->cards->sortBy('order') as $card)
                                        <div class="card mb-2 kanban-card" data-card-id="{{ $card->id }}">
                                            <div class="card-body p-2">
                                                @php
                                                    $priorityClass = [
                                                        'niski' => 'bg-success',
                                                        'średni' => 'bg-warning',
                                                        'wysoki' => 'bg-danger'
                                                    ][$card->priority] ?? 'bg-secondary';
                                                @endphp
                                                
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge {{ $priorityClass }} me-2">{{ $card->priority }}</span>
                                                    <h6 class="mb-0 card-title flex-grow-1">{{ $card->title }}</h6>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm text-muted p-0 dropdown-toggle-no-arrow" type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item edit-card-btn" href="#" data-card-id="{{ $card->id }}">
                                                                    <i class="bi bi-pencil me-2"></i> {{ __('Edytuj') }}
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('cards.destroy', $card->id) }}" method="POST" onsubmit="return confirm('{{ __('Czy na pewno chcesz usunąć tę kartę?') }}')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="bi bi-trash me-2"></i> {{ __('Usuń') }}
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                
                                                @if($card->description)
                                                    <p class="card-text small mb-2">{{ Str::limit($card->description, 100) }}</p>
                                                @endif
                                                
                                                @if($card->due_date)
                                                    <div class="small text-muted">
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        {{ \Carbon\Carbon::parse($card->due_date)->format('d.m.Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Add Card Modal -->
    <div class="modal fade" id="addCardModal" tabindex="-1" aria-labelledby="addCardModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addCardForm" method="POST" action="{{ route('cards.store') }}">
                    @csrf
                    <input type="hidden" name="column_id" id="column_id_input">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCardModalLabel">{{ __('Dodaj nową kartę') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="card_title" class="form-label">{{ __('Tytuł') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="card_title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="card_description" class="form-label">{{ __('Opis') }}</label>
                            <textarea class="form-control" id="card_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="card_due_date" class="form-label">{{ __('Termin') }}</label>
                                <input type="date" class="form-control" id="card_due_date" name="due_date">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="card_priority" class="form-label">{{ __('Priorytet') }}</label>
                                <select class="form-select" id="card_priority" name="priority">
                                    <option value="niski">{{ __('Niski') }}</option>
                                    <option value="średni" selected>{{ __('Średni') }}</option>
                                    <option value="wysoki">{{ __('Wysoki') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Anuluj') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Dodaj kartę') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Column Modal -->
    <div class="modal fade" id="editColumnModal" tabindex="-1" aria-labelledby="editColumnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editColumnForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="editColumnModalLabel">{{ __('Edytuj kolumnę') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_column_name" class="form-label">{{ __('Nazwa') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_column_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_card_limit" class="form-label">{{ __('Limit kart') }}</label>
                            <input type="number" class="form-control" id="edit_card_limit" name="card_limit" min="0">
                            <div class="form-text">{{ __('Ustaw 0 lub pozostaw puste dla braku limitu.') }}</div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Anuluj') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Zapisz zmiany') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Card Modal -->
    <div class="modal fade" id="editCardModal" tabindex="-1" aria-labelledby="editCardModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCardForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCardModalLabel">{{ __('Edytuj kartę') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_card_title" class="form-label">{{ __('Tytuł') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_card_title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_card_description" class="form-label">{{ __('Opis') }}</label>
                            <textarea class="form-control" id="edit_card_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_card_due_date" class="form-label">{{ __('Termin') }}</label>
                                <input type="date" class="form-control" id="edit_card_due_date" name="due_date">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_card_priority" class="form-label">{{ __('Priorytet') }}</label>
                                <select class="form-select" id="edit_card_priority" name="priority">
                                    <option value="niski">{{ __('Niski') }}</option>
                                    <option value="średni">{{ __('Średni') }}</option>
                                    <option value="wysoki">{{ __('Wysoki') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Anuluj') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Zapisz zmiany') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/kanban.js') }}" defer></script>
    @endpush
</x-app-layout>
