<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 text-gray-800 font-weight-bold">
                {{ __('Edytuj kartę') }}
            </h2>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">{{ $card->title }}</h5>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="{{ route('cards.update', $card->id) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">{{ __('Tytuł') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $card->title) }}" required autofocus>
                                @error('title')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('Opis') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $card->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">{{ __('Priorytet') }}</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                        <option value="niski" {{ old('priority', $card->priority) == 'niski' ? 'selected' : '' }}>{{ __('Niski') }}</option>
                                        <option value="średni" {{ old('priority', $card->priority) == 'średni' ? 'selected' : '' }}>{{ __('Średni') }}</option>
                                        <option value="wysoki" {{ old('priority', $card->priority) == 'wysoki' ? 'selected' : '' }}>{{ __('Wysoki') }}</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="due_date" class="form-label">{{ __('Termin') }}</label>
                                    <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" 
                                        value="{{ old('due_date', $card->due_date ? $card->due_date->format('Y-m-d') : '') }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="column_id" class="form-label">{{ __('Kolumna') }}</label>
                                <select class="form-select @error('column_id') is-invalid @enderror" id="column_id" name="column_id">
                                    @foreach($columns as $column)
                                        <option value="{{ $column->id }}" 
                                            {{ old('column_id', $card->column_id) == $column->id ? 'selected' : '' }}>
                                            {{ $column->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('column_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="users" class="form-label">{{ __('Przypisz użytkowników') }}</label>
                                <select class="form-select @error('users') is-invalid @enderror" id="users" name="users[]" multiple data-bs-toggle="select2">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                            {{ in_array($user->id, old('users', $card->users->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('users')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            @if(isset($labels) && count($labels) > 0)
                                <div class="mb-3">
                                    <label for="labels" class="form-label">{{ __('Etykiety') }}</label>
                                    <select class="form-select @error('labels') is-invalid @enderror" id="labels" name="labels[]" multiple data-bs-toggle="select2">
                                        @foreach($labels as $label)
                                            <option value="{{ $label->id }}" 
                                                {{ in_array($label->id, old('labels', $card->labels->pluck('id')->toArray())) ? 'selected' : '' }}
                                                style="background-color: {{ $label->color }}20">
                                                {{ $label->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('labels')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            @endif
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('boards.show', $board->id) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> {{ __('Wróć do tablicy') }}
                                </a>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> {{ __('Zapisz zmiany') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicjalizacja Select2 dla multi-selectów
            $('[data-bs-toggle="select2"]').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });
    </script>
    @endpush
    
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
        }
    </style>
    @endpush
</x-app-layout>
