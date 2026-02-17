<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modifier article') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.postUptadeArticles', $articles->Code) }}" method="POST">
                        @csrf
                        <input type="text" name="Code" value="{{ $articles->Code }}" class="border border-gray-300 rounded-md p-2 w-full mb-4 text-gray-900" required>
                        <input type="text" name="Liblong" value="{{ $articles->Liblong }}" class="border border-gray-300 rounded-md p-2 w-full mb-4 text-gray-900" required>
                        <input type="submit" name="submit" value="Update Article" class="bg-blue-500 text-white rounded-md p-2 hover:bg-blue-600">
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>