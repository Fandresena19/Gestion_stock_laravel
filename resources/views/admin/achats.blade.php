<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Achats
            </h2>

            {{-- ✅ CORRECTION : id="search" ajouté pour que le JS live-search fonctionne --}}
            <form method="GET" action="{{ route('admin.achats') }}" class="flex items-center gap-2">
                <input
                    id="search"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Rechercher par Code ou Libellé..."
                    class="px-4 py-2 border rounded-md
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           dark:bg-gray-700 dark:text-white">
                <button type="submit"
                    class="px-3 py-2 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm">
                    🔍
                </button>
            </form>

            <form action="{{ route('achats.import') }}" method="POST" enctype="multipart/form-data"
                class="flex items-center gap-2">
                @csrf
                <input type="file"
                    name="files[]"
                    class="px-4 py-2 border rounded-md
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           dark:bg-gray-700 dark:text-white"
                    multiple required>
                <input type="submit" value="Importer"
                    class="bg-blue-500 text-white rounded-md px-4 py-2 hover:bg-blue-600 cursor-pointer">
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- ✅ Message de succès après import --}}
            @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-md">
                ✅ {{ session('success') }}
            </div>
            @endif

            {{-- ✅ Message d'erreur éventuel --}}
            @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-md">
                ❌ {{ session('error') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Résumé de la recherche --}}
                    @if (request('search'))
                    <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">
                        Résultats pour <strong>"{{ request('search') }}"</strong>
                        — {{ $achats->total() }} achat(s) trouvé(s).
                        <a href="{{ route('admin.achats') }}" class="text-blue-500 hover:underline ml-2">
                            Effacer la recherche
                        </a>
                    </p>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-center">
                                <tr>
                                    <th class="px-4 py-2 text-sm font-semibold">Code</th>
                                    <th class="px-4 py-2 text-sm font-semibold">Date</th>
                                    <th class="px-4 py-2 text-sm font-semibold">Article (Libellé)</th>
                                    <th class="px-4 py-2 text-sm font-semibold">Prix Unitaire</th>
                                    <th class="px-4 py-2 text-sm font-semibold">Quantité</th>
                                </tr>
                            </thead>

                            <tbody id="achatsTable">
                                @forelse ($achats as $achat)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-center hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-4 py-2 text-sm font-mono">{{ $achat->Code }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ \Carbon\Carbon::parse($achat->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-left">{{ $achat->Liblong }}</td>
                                    <td class="px-4 py-2 text-sm text-right">
                                        {{ number_format($achat->PrixU, 2, ',', ' ') }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right">
                                        {{ number_format($achat->QuantiteAchat, 0, ',', ' ') }}
                                    </td>
                                </tr>
                                @empty
                                {{-- ✅ CORRECTION : colspan correct (5 colonnes) + message adapté selon recherche --}}
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                        @if (request('search'))
                                        Aucun achat trouvé pour "{{ request('search') }}"
                                        @else
                                        Aucun achat enregistré
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $achats->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ CORRECTION Live Search : écoute sur #search qui existe maintenant --}}
    <script>
        const searchInput = document.getElementById('search');

        if (searchInput) {
            let debounceTimer;

            searchInput.addEventListener('keyup', function() {
                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(() => {
                    const search = encodeURIComponent(this.value.trim());
                    const url = `{{ route('admin.achats') }}?search=${search}`;

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(data => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(data, 'text/html');
                            const newTable = doc.getElementById('achatsTable');

                            if (newTable) {
                                document.getElementById('achatsTable').innerHTML = newTable.innerHTML;
                            }
                        })
                        .catch(err => console.error('Erreur live search :', err));
                }, 300); // délai 300ms pour éviter trop de requêtes
            });
        }
    </script>
</x-app-layout>