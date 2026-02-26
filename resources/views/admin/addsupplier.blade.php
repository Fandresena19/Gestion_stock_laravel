{{-- resources/views/admin/addSupplier.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Ajout Fournisseur') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('success'))
                    <div class="mb-4 p-3 rounded bg-green-500 text-white text-center">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-4 p-3 rounded bg-red-500 text-white">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.postaddfournisseur') }}" method="POST">
                        @csrf
                        <input
                            type="text"
                            name="fournisseur"
                            placeholder="Nom du fournisseur"
                            value="{{ old('fournisseur') }}"
                            class="border border-gray-300 rounded-md p-2 w-full mb-4 text-gray-900"
                            required>
                        <input
                            type="submit"
                            value="Ajouter Fournisseur"
                            class="bg-blue-500 text-white rounded-md p-2 hover:bg-blue-600 cursor-pointer">
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>