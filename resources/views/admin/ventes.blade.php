<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{'Vente '}}{{ \Carbon\Carbon::yesterday()->format('d/m/Y') }}
            </h2>
            @if($topProduit)
            <div class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <strong>Produit le plus vendu {{ \Carbon\Carbon::yesterday()->format('d/m/Y') }} :</strong> {{ $topProduit->idlib }} <br>
                Quantité vendue : {{ $topProduit->total_vendu }}
            </div>
            @endif
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
                                    <th class="px-4 py-2">Code</th>
                                    <th class="px-4 py-2">Article (Libellé)</th>
                                    <th class="px-4 py-2">Quantite</th>
                                </tr>
                            </thead>

                            <tbody id="achatsTable">
                                @forelse ($ventes as $vente)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-center">
                                    <td class="px-4 py-2 text-sm">
                                        {{ $vente->idcint }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $vente->idlib }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-right">
                                        {{ $vente->E1 }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">
                                        Aucune vente trouvé
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

    {{ $ventes->links() }}
</x-app-layout>