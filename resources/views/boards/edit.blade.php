<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 text-gray-800 font-weight-bold">
                {{ __('Edycja tablicy Kanban') }}
            </h2>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">{{ $board->name }}</h5>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="{{ route('boards.update', $board->id) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('Nazwa tablicy') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $board->name) }}" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('Opis') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $board->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text text-muted">
                                    {{ __('Krótki opis tablicy (opcjonalnie)') }}
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('boards.show', $board->id) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> {{ __('Wróć do tablicy') }}
                                </a>
                                
                                <div>
                                    <a href="{{ route('boards.index') }}" class="btn btn-secondary me-2">
                                        {{ __('Anuluj') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> {{ __('Zapisz zmiany') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
