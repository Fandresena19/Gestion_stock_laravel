<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\articles;
use App\Models\Achat;
use App\Models\Stocks;
use App\Imports\AchatsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminController extends Controller
{
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
        $articles = articles::findOrFail($Code);
        $articles->delete();

        return redirect('/viewArticles');
    }

    public function addArticles()
    {
        return view('admin.addArticles');
    }

    public function postAddArticles(Request $request)
    {
        $articles = new articles();

        $articles->Code = $request->input('Code');
        $articles->Liblong = $request->input('Liblong');

        $articles->save();

        return redirect()->back();
    }

    public function updateArticle($Code)
    {
        $articles = articles::findOrFail($Code);
        return view('admin.updateArticle', compact('articles'));
    }

    public function postUpdateArticle(request $request, $Code)
    {
        $articles = articles::findOrFail($Code);
        $articles->Liblong = $request->Liblong;
        $articles->save();
        return redirect('/viewArticles');
    }

    public function viewAchats()
    {
        $achats = Achat::orderBy('date', 'desc')->paginate(20);
        return view('admin.achats', compact('achats'));
    }

    public function viewAchat(Request $request)
    {
        $search = $request->search;

        $achats = Achat::when($search, function ($query) use ($search) {
            $query->where('Code', 'like', "%{$search}%")
                ->orWhere('Liblong', 'like', "%{$search}%");
        })

            ->orderBy('date', 'asc')
            ->paginate(20);
        return view('admin.achats', compact('achats'));
    }

    public function importAchats(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new AchatsImport, $request->file('file'));

        return redirect()->route('admin.achats')
            ->with('success', 'Importation réussie');
    }

    public function Ventes()
    {
        $yesterday = Carbon::yesterday();
        $tableName = 'servmcljournal' . $yesterday->format('Ymd');

        $topProduit = null; //  toujours initialiser

        if (!Schema::hasTable($tableName)) {
            return view('admin.ventes', [
                'ventes' => collect(),
                'topProduit' => null,
                'yesterday' => $yesterday,
                'error' => "La table $tableName n'existe pas."
            ]);
        }

        $ventes = DB::table($tableName)
            ->orderBy('idquand', 'asc')
            ->paginate(20);

        $topProduit = DB::table($tableName)
            ->select('idcint', 'idlib', DB::raw('SUM(E1) as total_vendu'))
            ->groupBy('idcint', 'idlib')
            ->orderByDesc('total_vendu')
            ->first();

        return view('admin.ventes', compact('ventes', 'topProduit', 'yesterday'));
    }



    public function calculStock()
    {
        //  Récupérer tous les articles depuis achats
        $articles = DB::table('achats')
            ->select('code', 'liblong')
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->distinct()
            ->get();


        foreach ($articles as $article) {

            // Total achats
            $totalAchats = DB::table('achats')
                ->where('code', $article->code)
                ->sum('quantiteachat');

            //  Total ventes (toutes les tables servmcljournal)
            $totalVentes = 0;

            $tables = DB::select("SHOW TABLES LIKE 'servmcljournal%'");

            foreach ($tables as $table) {

                $tableName = array_values((array)$table)[0];

                $totalVentes += DB::table($tableName)
                    ->where('idcint', $article->code)
                    ->sum('E1');
            }

            //  Calcul final
            $stockFinal = $totalAchats - $totalVentes;

            //  Insert ou update
            Stocks::updateOrCreate(
                ['code' => $article->code],
                [
                    'liblong' => $article->liblong,
                    'quantitestock' => $stockFinal
                ]
            );
        }

        return back()->with('success', 'Stock calculé avec succès.');
    }

    public function stocks(Request $request)
    {
        $search = $request->search;

        $stocks = Stocks::when($search, function ($query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('liblong', 'like', "%{$search}%");
        })
            ->orderBy('code', 'asc')
            ->paginate(20);

        return view('admin.stocks', compact('stocks'));
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'Code' => 'required',
            'quantite' => 'required|numeric|min:0'
        ]);

        $updated = Stocks::where('Code', $request->Code)
            ->update([
                'QuantiteStock' => $request->quantite
            ]);

        return response()->json([
            'success' => $updated ? true : false
        ]);
    }
}
