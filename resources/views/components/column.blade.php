@props(['column', 'cards'])

<div class="kanban-column card me-3" data-column-id="{{ $column->id }}" style="min-width: 300px; max-width: 300px;">
    <!-- Nagłówek kolumny -->
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
        <div>
            <h5 class="mb-0 d-inline">{{ $column->name }}</h5>
            <span class="badge bg-secondary ms-1">
                {{ $cards->count() }}{{ $column->card_limit ? '/'.$column->card_limit : '' }}
            </span>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item edit-column-btn" href="#" 
                       data-column-id="{{ $column->id }}" 
                       data-column-name="{{ $column->name }}" 
                       data-column-limit="{{ $column->card_limit }}">
                        <i class="bi bi-pencil me-2"></i> {{ __('Edytuj') }}
                    </a>
                </li>
                <li>
                    <form action="{{ route('columns.destroy', $column->id) }}" method="POST" 
                          onsubmit="return confirm('{{ __('Czy na pewno chcesz usunąć tę kolumnę wraz ze wszystkimi kartami?') }}')">
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
    
    <!-- Zawartość kolumny -->
    <div class="card-body p-2">
        <!-- Przycisk dodawania karty -->
        <button class="btn btn-sm btn-outline-primary w-100 mb-2 add-card-btn" 
                data-column-id="{{ $column->id }}">
            <i class="bi bi-plus-lg me-1"></i> {{ __('Dodaj kartę') }}
        </button>
        
        <!-- Kontener na karty (dla drag-and-drop) -->
        <div class="kanban-cards" id="column-cards-{{ $column->id }}">
            @if($cards->isEmpty())
                <div class="text-center text-muted py-3 empty-column-placeholder">
                    <i class="bi bi-sticky"></i>
                    <p class="mb-0 small">{{ __('Brak kart') }}</p>
                </div>
            @else
                @foreach($cards->sortBy('order') as $card)
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
                                            <form action="{{ route('cards.destroy', $card->id) }}" method="POST" 
                                                  onsubmit="return confirm('{{ __('Czy na pewno chcesz usunąć tę kartę?') }}')">
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
