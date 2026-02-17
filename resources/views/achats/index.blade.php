<h2>Import des achats</h2>

@if(session('success'))
<div style="color: green;">
    {{ session('success') }}
</div>
@endif

<form action="{{ route('achats.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Importer</button>
</form>

<hr>

<h3>Liste des achats</h3>

<table border="1" width="100%">
    <thead>
        <tr>
            <th>Code16</th>
            <th>Date</th>
            <th>Article</th>
            <th>Prix</th>
            <th>Quantité</th>
        </tr>
    </thead>
    <tbody>
        @foreach($achats as $achat)
        <tr>
            <td>{{ $achat->Code16 }}</td>
            <td>{{ $achat->date }}</td>
            <td>{{ $achat->Liblong }}</td>
            <td>{{ $achat->PrixU }}</td>
            <td>{{ $achat->QuantiteAchat }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $achats->links() }}