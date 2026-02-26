<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\fournisseur;
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
                ->orWhere('Liblong', 'like', "%{$search}%")
                ->orWhere('fournisseur', 'like', "%{$search}%");
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
        $fournisseurs = fournisseur::orderBy('fournisseur', 'asc')->get();
        return view('admin.addArticles', compact('fournisseurs'));
    }

    public function postAddArticles(Request $request)
    {
        $article              = new articles();
        $article->Code        = $request->input('Code');
        $article->Liblong     = $request->input('Liblong');
        $article->fournisseur = $request->input('fournisseur') ?: null;
        $article->save();

        return redirect()->back()->with('success', 'Article ajouté avec succès.');
    }

    public function updateArticle($Code)
    {
        $articles     = articles::findOrFail($Code);
        $fournisseurs = fournisseur::orderBy('fournisseur', 'asc')->get();
        return view('admin.updateArticle', compact('articles', 'fournisseurs'));
    }

    public function postUpdateArticle(Request $request, $Code)
    {
        $articles              = articles::findOrFail($Code);
        $articles->Liblong     = $request->Liblong;
        $articles->fournisseur = $request->input('fournisseur') ?: null;
        $articles->save();
        return redirect('/viewArticles');
    }

    // =========================================================================
    // FOURNISSEURS
    // =========================================================================

    public function addFournisseur()
    {
        return view('admin.addSupplier');
    }

    public function postAddFournisseur(Request $request)
    {
        $request->validate([
            'fournisseur' => 'required|string|max:255|unique:fournisseurs,fournisseur',
        ]);

        fournisseur::create([
            'fournisseur' => $request->input('fournisseur'),
        ]);

        return redirect()->back()->with('success', 'Fournisseur ajouté avec succès.');
    }

    public function viewFournisseurs(Request $request)
    {
        $search = $request->search;

        $fournisseurs = fournisseur::when($search, function ($query) use ($search) {
            $query->where('fournisseur', 'like', "%{$search}%");
        })
            ->orderBy('fournisseur', 'asc')
            ->paginate(50);

        return view('admin.viewSupplier', compact('fournisseurs'));
    }

    public function deleteFournisseur($id)
    {
        fournisseur::findOrFail($id)->delete();
        return redirect()->route('admin.viewSupplier');
    }

    public function updateFournisseur($id)
    {
        $fournisseur = fournisseur::findOrFail($id);
        return view('admin.updateSupplier', compact('fournisseur'));
    }

    public function postUpdateFournisseur(Request $request, $id)
    {

        $request->validate([
            'fournisseur' => 'required|string|max:255|unique:fournisseurs,fournisseur,' . $id . ',id_fournisseur',
        ]);

        $fournisseur = fournisseur::findOrFail($id);
        $fournisseur->fournisseur = $request->fournisseur;
        $fournisseur->save();

        return redirect()->route('admin.viewSupplier');
    }

    // =========================================================================
    // ACHATS
    // =========================================================================

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

    public function importAchats(Request $request)
    {
        $request->validate([
            'files'   => 'required',
            'files.*' => 'mimes:xlsx,xls,csv|max:20480',
        ]);

        $totalNew     = 0;
        $totalSkipped = 0;
        $importDate   = now()->format('Y-m-d');

        foreach ($request->file('files') as $file) {
            $filename = $file->getClientOriginalName();
            $path     = $file->getPathname();

            $importer = new AchatsImport($filename, $path, $importDate);
            Excel::import($importer, $file);

            $totalNew     += $importer->getNewRowsCount();
            $totalSkipped += $importer->getSkippedRowsCount();
        }

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

    /**
     * Recalcule les stocks depuis la table articles.
     * La colonne articles.fournisseur (texte) est copiée directement dans stocks.fournisseur.
     * quantitestock = total achats - total ventes (toutes tables servmcljournalYMD)
     */
    private function calculStock()
    {
        $articlesList = articles::all();

        $tables = DB::select("SHOW TABLES LIKE 'servmcljournal%'");

        foreach ($articlesList as $article) {
            $totalAchats = DB::table('achats')
                ->where('code', $article->Code)
                ->sum('quantiteachat');

            $totalVentes = 0;
            foreach ($tables as $table) {
                $tableName    = array_values((array) $table)[0];
                $totalVentes += DB::table($tableName)
                    ->where('idcint', $article->Code)
                    ->sum('E1');
            }

            Stocks::updateOrCreate(
                ['code' => $article->Code],
                [
                    'liblong'       => $article->Liblong,
                    'fournisseur'   => $article->fournisseur,
                    'quantitestock' => $totalAchats - $totalVentes,
                ]
            );
        }
    }

    public function stocks(Request $request)
    {
        $this->calculStock();

        $search      = $request->search;
        $fournisseur = $request->fournisseur;

        // Liste des fournisseurs distincts dans stocks pour le filtre dropdown
        $fournisseursList = Stocks::whereNotNull('fournisseur')
            ->distinct()
            ->orderBy('fournisseur')
            ->pluck('fournisseur');

        $stocks = Stocks::when($search, function ($query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('liblong', 'like', "%{$search}%")
                ->orWhere('fournisseur', 'like', "%{$search}%");
        })
            ->when($fournisseur, function ($query) use ($fournisseur) {
                $query->where('fournisseur', $fournisseur);
            })
            ->orderBy('code', 'asc')
            ->paginate(50)
            ->withQueryString();

        return view('admin.stocks', compact('stocks', 'fournisseursList'));
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'Code'     => 'required',
            'quantite' => 'required|numeric|min:0',
        ]);

        $updated = Stocks::where('code', $request->Code)
            ->update(['quantitestock' => $request->quantite]);

        return response()->json(['success' => (bool) $updated]);
    }
}
