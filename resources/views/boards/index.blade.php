<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 text-gray-800 font-weight-bold">
                {{ __('Tablice Kanban') }}
            </h2>
            <a href="{{ route('boards.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> {{ __('Nowa tablica') }}
            </a>
        </div>
    </x-slot>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Main Content -->
    <div class="row mt-4">
        @if($boards->isEmpty())
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-clipboard-x fs-1 text-muted mb-3"></i>
                        <p class="mb-0">{{ __('Nie znaleziono tablic Kanban. Utwórz swoją pierwszą tablicę!') }}</p>
                        <a href="{{ route('boards.create') }}" class="btn btn-primary mt-3">
                            {{ __('Utwórz tablicę') }}
                        </a>
                    </div>
                </div>
            </div>
        @else
            @foreach($boards as $board)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow">
                        <div class="card-header bg-transparent border-bottom-0 pb-0">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-secondary text-light">
                                    <i class="bi bi-calendar3"></i> {{ $board->created_at->format('d.m.Y') }}
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('boards.edit', $board->id) }}">
                                                <i class="bi bi-pencil me-2"></i> {{ __('Edytuj') }}
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('boards.destroy', $board->id) }}" method="POST" onsubmit="return confirm('{{ __('Czy na pewno chcesz usunąć tę tablicę?') }}')">
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
                        </div>
                        <div class="card-body pt-2">
                            <h5 class="card-title fw-bold mb-3">{{ $board->name }}</h5>
                            <p class="card-text text-muted mb-3">
                                {{ $board->description ?? __('Brak opisu') }}
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <a href="{{ route('boards.show', $board->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                {{ __('Otwórz tablicę') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Pagination -->
    @if($boards->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $boards->links() }}
        </div>
    @endif
</x-app-layout>
