{{-- resources/views/admin/viewSupplier.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Fournisseurs
            </h2>
            <form method="GET" action="{{ route('admin.viewSupplier') }}">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Rechercher un fournisseur..."
                    class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500
                           dark:bg-gray-700 dark:text-white">
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('success'))
                    <div class="mb-4 p-3 rounded bg-green-500 text-white text-center">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-800 text-center">
                                <tr>
                                    <th class="px-4 py-2">ID</th>
                                    <th class="px-4 py-2">Fournisseur</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fournisseurs as $fournisseur)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-center">
                                    <td class="px-4 py-2 text-sm">{{ $fournisseur->id_fournisseur }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $fournisseur->fournisseur }}</td>
                                    <td class="px-4 py-2 text-sm flex gap-2 justify-center">
                                        <a href="{{ route('admin.updateFournisseur', $fournisseur->id_fournisseur) }}"
                                            style="background-color: #EBA709;"
                                            class="text-white font-semibold py-2 px-4 rounded shadow-sm">
                                            Modifier
                                        </a>
                                        <a href="{{ route('admin.deleteFournisseur', $fournisseur->id_fournisseur) }}"
                                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded shadow-sm"
                                            onclick="return confirm('Supprimer ce fournisseur ?');">
                                            Supprimer
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">
                                        Aucun fournisseur trouvé
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $fournisseurs->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>