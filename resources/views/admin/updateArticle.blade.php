{{-- resources/views/admin/updateArticle.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modifier Article') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if($errors->any())
                    <div class="mb-4 p-3 rounded bg-red-500 text-white">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.postUptadeArticles', $articles->Code) }}" method="POST">
                        @csrf

                        {{-- Code non modifiable --}}
                        <input
                            type="text"
                            value="{{ $articles->Code }}"
                            class="border border-gray-300 rounded-md p-2 w-full mb-4 text-gray-400 bg-gray-100"
                            disabled>

                        <input
                            type="text"
                            name="Liblong"
                            value="{{ old('Liblong', $articles->Liblong) }}"
                            placeholder="Libellé"
                            class="border border-gray-300 rounded-md p-2 w-full mb-4 text-gray-900"
                            required>

                        {{-- Sélection du fournisseur (stocke le nom texte) --}}
                        <select
                            name="fournisseur"
                            class="border border-gray-300 rounded-md p-2 w-full mb-4 text-gray-900">
                            <option value="">-- Aucun fournisseur --</option>
                            @foreach($fournisseurs as $four)
                            <option value="{{ $four->fournisseur }}"
                                {{ old('fournisseur', $articles->fournisseur) === $four->fournisseur ? 'selected' : '' }}>
                                {{ $four->fournisseur }}
                            </option>
                            @endforeach
                        </select>

                        <input
                            type="submit"
                            value="Enregistrer"
                            class="bg-blue-500 text-white rounded-md p-2 hover:bg-blue-600 cursor-pointer">
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>