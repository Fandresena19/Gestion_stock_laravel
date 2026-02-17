<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ 'Stock '}}
            </h2>
            <form action="{{ route('admin.stocks') }}" method="GETs">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by Code or Libellé..."
                    class="px-4 py-2 border rounded-md 
               focus:outline-none focus:ring-2 focus:ring-blue-500
               dark:bg-gray-700 dark:text-white">
            </form>


            <form action="{{ route('admin.calculStock') }}" method="POST">
                @csrf
                <button type="submit" class=" bg-blue-500 text-white rounded-md p-2 hover:bg-blue-600">
                    Recalculer le stock
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
                                    <th class="px-4 py-2">Quantite</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>

                            <tbody id="achatsTable">
                                @forelse ($stocks as $stock)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-center">
                                    <td class="px-4 py-2 text-sm">
                                        {{ $stock->Code }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $stock->Liblong }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        <span class="quantite-text-{{ $stock->Code }}">
                                            {{ $stock->QuantiteStock }}
                                        </span>

                                        <input type="number"
                                            value="{{ $stock->QuantiteStock }}"
                                            data-code="{{ $stock->Code }}"
                                            class="quantite-input-{{ $stock->Code }} hidden w-24 px-2 py-1 border rounded-md text-center
        dark:bg-gray-700 dark:text-white">
                                    </td>

                                    <td class="px-4 py-2 text-sm text-center">
                                        <button onclick="editStock('{{ $stock->Code }}')"
                                            class="edit-btn-{{ $stock->Code }} bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                            Modifier
                                        </button>

                                        <button onclick="saveStock('{{ $stock->Code }}')"
                                            class="save-btn-{{ $stock->Code }} hidden bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                            Enregistrer
                                        </button>
                                    </td>


                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">
                                        Aucun stock trouvé
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
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

    {{ $stocks->links() }}
</x-app-layout>