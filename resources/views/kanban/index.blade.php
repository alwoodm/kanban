<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kanban Boards') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-end">
                <a href="{{ route('kanban-boards.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Create New Board') }}
                </a>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($boards->isEmpty())
                        <p>{{ __('No kanban boards found. Create your first board!') }}</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($boards as $board)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
                                    <h3 class="text-lg font-semibold mb-2">{{ $board->name }}</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $board->description ?? 'No description' }}</p>
                                    <div class="flex justify-between">
                                        <a href="{{ route('kanban-boards.show', $board) }}" class="text-blue-500 hover:text-blue-700">{{ __('View') }}</a>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('kanban-boards.edit', $board) }}" class="text-yellow-500 hover:text-yellow-700">{{ __('Edit') }}</a>
                                            <form action="{{ route('kanban-boards.destroy', $board) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this board?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
