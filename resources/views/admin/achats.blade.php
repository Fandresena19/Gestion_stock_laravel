<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Achats
            </h2>

            <form method="GET" action="{{ route('admin.achats') }}">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by Code or Libellé..."
                    class="px-4 py-2 border rounded-md 
               focus:outline-none focus:ring-2 focus:ring-blue-500
               dark:bg-gray-700 dark:text-white">
            </form>

            <form action="{{ route('achats.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file"
                    name="files[]"
                    class="px-4 py-2 border rounded-md 
               focus:outline-none focus:ring-2 focus:ring-blue-500
               dark:bg-gray-700 dark:text-white" multiple required>
                <!-- <button type="submit">Importer</button> -->
                <input type="submit" name="submit" value="Importer" class="bg-blue-500 text-white rounded-md p-2 hover:bg-blue-600">
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-800 text-center">
                                <tr>
                                    <th class="px-4 py-2">Code16</th>
                                    <th class="px-4 py-2">Code</th>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Article (Libellé)</th>
                                    <th class="px-4 py-2">Prix Unitaire</th>
                                    <th class="px-4 py-2">Quantite</th>
                                </tr>
                            </thead>

                            <tbody id="achatsTable">
                                @forelse ($achats as $achat)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-center">
                                    <td class="px-4 py-2 text-sm">
                                        {{ $achat->Code16 }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $achat->Code }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ \Carbon\Carbon::parse($achat->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $achat->Liblong }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        {{ ($achat->PrixU) }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-center">
                                        {{ $achat->QuantiteAchat }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">
                                        Aucun achat trouvé
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

    <!-- Live Search Script -->
    <script>
        document.getElementById('search').addEventListener('keyup', function() {

            let search = this.value;

            fetch(`{{ route('admin.achats') }}?search=${search}`)
                .then(response => response.text())
                .then(data => {

                    let parser = new DOMParser();
                    let doc = parser.parseFromString(data, 'text/html');
                    let newTable = doc.getElementById('achatsTable').innerHTML;

                    document.getElementById('achatsTable').innerHTML = newTable;
                });
        });
    </script>
    {{ $achats->links() }}
</x-app-layout>