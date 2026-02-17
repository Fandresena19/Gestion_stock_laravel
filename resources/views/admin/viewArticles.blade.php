<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Articles
            </h2>

            <form method="GET" action="{{ route('admin.viewArticles') }}">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by Code or Libellé..."
                    class="px-4 py-2 border rounded-md 
               focus:outline-none focus:ring-2 focus:ring-blue-500
               dark:bg-gray-700 dark:text-white">
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
                                    <th class="px-4 py-2">Code</th>
                                    <th class="px-4 py-2">Article (Libellé)</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>

                            <tbody id="articleTable">
                                @forelse ($articles as $article)
                                <tr class="border-t border-gray-200 dark:border-gray-700 text-center">
                                    <td class="px-4 py-2 text-sm">
                                        {{ $article->Code }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $article->Liblong }}
                                    </td>
                                    <td class="px-4 py-2 text-sm flex gap-2 justify-center">
                                        <a href="{{ route('admin.updateArticle', $article->Code) }}"
                                            style="background-color: #EBA709;"
                                            class="text-white font-semibold py-2 px-4 rounded shadow-sm">
                                            Update
                                        </a>

                                        <a href="{{ route('admin.deleteArticle', $article->Code) }}"
                                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded shadow-sm"
                                            onclick="return confirm('Are you sure you want to delete this article?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">
                                        Aucun article trouvé
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

            fetch(`{{ route('admin.viewArticles') }}?search=${search}`)
                .then(response => response.text())
                .then(data => {

                    let parser = new DOMParser();
                    let doc = parser.parseFromString(data, 'text/html');
                    let newTable = doc.getElementById('articleTable').innerHTML;

                    document.getElementById('articleTable').innerHTML = newTable;
                });
        });
    </script>
    {{ $articles->links() }}
</x-app-layout>