{{-- resources/views/admin/stocks.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Stock
            </h2>
            <form method="GET" action="{{ route('admin.stocks') }}" class="flex gap-2 flex-wrap">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Rechercher par Code, Libellé ou Fournisseur..."
                    class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500
                           dark:bg-gray-700 dark:text-white">

                {{-- Filtre par fournisseur --}}
                <select
                    name="fournisseur"
                    class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500
                           dark:bg-gray-700 dark:text-white">
                    <option value="">-- Tous les fournisseurs --</option>
                    @foreach($fournisseursList as $four)
                    <option value="{{ $four }}" {{ request('fournisseur') === $four ? 'selected' : '' }}>
                        {{ $four }}
                    </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Filtrer
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <div id="success-message"
                            class="hidden mb-4 p-3 rounded bg-green-500 text-white text-center">
                            Stock modifié avec succès !
                        </div>

                        <table class="w-full border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-800 text-center">
                                <tr>
                                    <th class="px-4 py-2">Code</th>
                                    <th class="px-4 py-2">Article (Libellé)</th>
                                    <th class="px-4 py-2">Fournisseur</th>
                                    <th class="px-4 py-2">Quantité</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody id="achatsTable">
                                @forelse ($stocks as $stock)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-center">
                                    <td class="px-4 py-2 text-sm">{{ $stock->code }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $stock->liblong }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $stock->fournisseur ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        <span class="quantite-text-{{ $stock->code }}">
                                            {{ $stock->quantitestock }}
                                        </span>
                                        <input
                                            type="number"
                                            value="{{ $stock->quantitestock }}"
                                            data-code="{{ $stock->code }}"
                                            class="quantite-input-{{ $stock->code }} hidden w-24 px-2 py-1 border rounded-md text-center
                                                   dark:bg-gray-700 dark:text-white">
                                    </td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        <button
                                            onclick="editStock('{{ $stock->code }}')"
                                            class="edit-btn-{{ $stock->code }} bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                            Modifier
                                        </button>
                                        <button
                                            onclick="saveStock('{{ $stock->code }}')"
                                            class="save-btn-{{ $stock->code }} hidden bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                            Enregistrer
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-500">
                                        Aucun stock trouvé
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $stocks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editStock(code) {
            document.querySelector('.quantite-text-' + code).classList.add('hidden');
            document.querySelector('.quantite-input-' + code).classList.remove('hidden');
            document.querySelector('.edit-btn-' + code).classList.add('hidden');
            document.querySelector('.save-btn-' + code).classList.remove('hidden');
        }

        function saveStock(code) {
            let input = document.querySelector('.quantite-input-' + code);
            let quantite = input.value;

            fetch("{{ route('admin.updateStock') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        Code: code,
                        quantite: quantite
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.quantite-text-' + code).innerText = quantite;
                        document.querySelector('.quantite-text-' + code).classList.remove('hidden');
                        document.querySelector('.quantite-input-' + code).classList.add('hidden');
                        document.querySelector('.edit-btn-' + code).classList.remove('hidden');
                        document.querySelector('.save-btn-' + code).classList.add('hidden');

                        document.getElementById('success-message').classList.remove('hidden');
                        setTimeout(() => {
                            document.getElementById('success-message').classList.add('hidden');
                        }, 3000);
                    }
                });
        }
    </script>
</x-app-layout>