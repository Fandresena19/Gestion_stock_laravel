<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\articles;
use App\Models\Achat;
use App\Models\Stocks;
use App\Models\ImportedFile;
use App\Models\ImportedRow;
use App\Imports\AchatsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminController extends Controller
{
    // =========================================================================
    // ARTICLES
    // =========================================================================

    public function viewArticles(Request $request)
    {
        $search = $request->search;

        $articles = articles::when($search, function ($query) use ($search) {
            $query->where('Code', 'like', "%{$search}%")
                ->orWhere('Liblong', 'like', "%{$search}%");
        })
            ->orderBy('Code', 'asc')
            ->paginate(50);

        return view('admin.viewArticles', compact('articles'));
    }

    public function deleteArticle($Code)
    {
        articles::findOrFail($Code)->delete();
        return redirect('/viewArticles');
    }

    public function addArticles()
    {
        return view('admin.addArticles');
    }

    public function postAddArticles(Request $request)
    {
        $article          = new articles();
        $article->Code    = $request->input('Code');
        $article->Liblong = $request->input('Liblong');
        $article->save();

        return redirect()->back();
    }

    public function updateArticle($Code)
    {
        $articles = articles::findOrFail($Code);
        return view('admin.updateArticle', compact('articles'));
    }

    public function postUpdateArticle(Request $request, $Code)
    {
        $articles          = articles::findOrFail($Code);
        $articles->Liblong = $request->Liblong;
        $articles->save();
        return redirect('/viewArticles');
    }

    // =========================================================================
    // ACHATS
    // =========================================================================

    /**
     * Liste des achats avec recherche optionnelle.
     */
    public function viewAchats(Request $request)
    {
        $search = $request->get('search');

        $achats = Achat::when($search, function ($query) use ($search) {
            $query->where('Code', 'like', "%{$search}%")
                ->orWhere('Liblong', 'like', "%{$search}%");
        })
            ->orderBy('date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.achats', compact('achats'));
    }

    /**
     * Import manuel de fichiers Excel via le formulaire.
     *
     * Logique :
     *  - Le fichier est identifié par son nom d'origine.
     *  - Si le nom est déjà connu, on scanne quand même toutes les lignes.
     *  - Seules les lignes dont le hash est NOUVEAU sont insérées.
     *  - Aucun doublon dans `achats`, quelle que soit la date d'upload.
     */
    public function importAchats(Request $request)
    {
        $request->validate([
            'files'   => 'required',
            'files.*' => 'mimes:xlsx,xls,csv|max:20480',
        ]);

        $totalNew     = 0;
        $totalSkipped = 0;
        $details      = [];

        // Date du jour pour les nouvelles lignes importées manuellement
        $importDate = now()->format('Y-m-d');

        foreach ($request->file('files') as $file) {
            $filename = $file->getClientOriginalName();
            $path     = $file->getPathname();

            // Récupérer l'état actuel avant import pour le message de retour
            $existingFile  = ImportedFile::where('filename', $filename)->first();
            $existingCount = $existingFile
                ? ImportedRow::where('imported_file_id', $existingFile->id)->count()
                : 0;

            $importer = new AchatsImport($filename, $path, $importDate);
            Excel::import($importer, $file);

            $newRows     = $importer->getNewRowsCount();
            $skippedRows = $importer->getSkippedRowsCount();

            $totalNew     += $newRows;
            $totalSkipped += $skippedRows;

            $details[] = [
                'filename'     => $filename,
                'new'          => $newRows,
                'skipped'      => $skippedRows,
                'was_existing' => $existingCount > 0,
            ];
        }

        // Construction du message flash
        $message = "{$totalNew} nouvelle(s) ligne(s) insérée(s)";
        if ($totalSkipped > 0) {
            $message .= ", {$totalSkipped} ligne(s) ignorée(s) (déjà présentes ou doublons).";
        }

        return redirect()->route('admin.achats')->with('success', $message);
    }

    // =========================================================================
    // VENTES
    // =========================================================================

    public function Ventes()
    {
        $yesterday = Carbon::yesterday();
        $tableName = 'servmcljournal' . $yesterday->format('Ymd');

        if (!Schema::hasTable($tableName)) {
            return view('admin.ventes', [
                'ventes'     => collect(),
                'topProduit' => null,
                'yesterday'  => $yesterday,
                'error'      => "La table $tableName n'existe pas.",
            ]);
        }

        $ventes = DB::table($tableName)
            ->orderBy('idquand', 'asc')
            ->paginate(50);

        $topProduit = DB::table($tableName)
            ->select('idcint', 'idlib', DB::raw('SUM(E1) as total_vendu'))
            ->groupBy('idcint', 'idlib')
            ->orderByDesc('total_vendu')
            ->first();

        return view('admin.ventes', compact('ventes', 'topProduit', 'yesterday'));
    }

    // =========================================================================
    // STOCKS
    // =========================================================================

    private function calculStock()
    {
        $articles = DB::table('achats')
            ->select('code', 'liblong')
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->distinct()
            ->get();

        $tables = DB::select("SHOW TABLES LIKE 'servmcljournal%'");

        foreach ($articles as $article) {
            $totalAchats = DB::table('achats')
                ->where('code', $article->code)
                ->sum('quantiteachat');

            $totalVentes = 0;

            foreach ($tables as $table) {
                $tableName    = array_values((array) $table)[0];
                $totalVentes += DB::table($tableName)
                    ->where('idcint', $article->code)
                    ->sum('E1');
            }

            Stocks::updateOrCreate(
                ['code' => $article->code],
                [
                    'liblong'       => $article->liblong,
                    'quantitestock' => $totalAchats - $totalVentes,
                ]
            );
        }
    }

    public function stocks(Request $request)
    {
        $this->calculStock();

        $search = $request->search;

        $stocks = Stocks::when($search, function ($query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('liblong', 'like', "%{$search}%");
        })
            ->orderBy('code', 'asc')
            ->paginate(50);

        return view('admin.stocks', compact('stocks'));
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'Code'     => 'required',
            'quantite' => 'required|numeric|min:0',
        ]);

        $updated = Stocks::where('Code', $request->Code)
            ->update(['QuantiteStock' => $request->quantite]);

        return response()->json(['success' => (bool) $updated]);
    }
}
