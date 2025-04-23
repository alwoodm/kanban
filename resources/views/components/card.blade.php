@props(['card', 'showActions' => true])

<div {{ $attributes->merge(['class' => 'card mb-2 kanban-card shadow-sm']) }} 
     data-card-id="{{ $card->id }}">
    <div class="card-body p-2">
        <!-- Nagłówek karty z tytułem i priorytetem -->
        <div class="d-flex align-items-center mb-2">
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
            
            <span class="badge {{ $priorityClass }} me-2">
                <i class="bi bi-{{ $priorityIcon }}"></i> {{ $card->priority }}
            </span>
            
            <h6 class="mb-0 card-title flex-grow-1 text-truncate">{{ $card->title }}</h6>
            
            @if($showActions)
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
            @endif
        </div>
        
        <!-- Opis (opcjonalny, skrócony) -->
        @if($card->description)
            <p class="card-text small mb-2 text-muted">
                {{ Str::limit($card->description, 60) }}
            </p>
        @endif
        
        <!-- Etykiety (jeśli istnieją) -->
        @if($card->labels && $card->labels->isNotEmpty())
            <div class="mb-2">
                @foreach($card->labels as $label)
                    <span class="badge" style="background-color: {{ $label->color }}">
                        {{ $label->name }}
                    </span>
                @endforeach
            </div>
        @endif
        
        <!-- Dolna sekcja karty z terminem i przypisanymi użytkownikami -->
        <div class="d-flex justify-content-between align-items-center mt-2">
            <!-- Termin wykonania (opcjonalny) -->
            @if($card->due_date)
                <div class="small text-muted">
                    <i class="bi bi-calendar-event me-1"></i>
                    {{ \Carbon\Carbon::parse($card->due_date)->format('d.m.Y') }}
                </div>
            @else
                <div></div>
            @endif
            
            <!-- Przypisani użytkownicy (jeśli istnieją) -->
            <div class="d-flex align-items-center">
                @if($card->users && $card->users->isNotEmpty())
                    <div class="avatar-group">
                        @foreach($card->users->take(3) as $user)
                            <div class="avatar avatar-xs" data-bs-toggle="tooltip" title="{{ $user->name }}">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" 
                                         class="rounded-circle" width="24" height="24">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                         style="width: 24px; height: 24px; font-size: 10px;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                        @if($card->users->count() > 3)
                            <div class="avatar avatar-xs">
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                     style="width: 24px; height: 24px; font-size: 10px;">
                                    +{{ $card->users->count() - 3 }}
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Link do szczegółów -->
                <a href="{{ route('cards.show', $card->id) }}" class="btn btn-sm btn-link text-primary p-0 ms-2" 
                   data-bs-toggle="tooltip" title="{{ __('Zobacz szczegóły') }}">
                    <i class="bi bi-eye"></i>
                </a>
            </div>
        </div>
    </div>
</div>
