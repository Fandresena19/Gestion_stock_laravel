<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [UserController::class, 'Dashboard'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/addArticles', [AdminController::class, 'addArticles'])->name('admin.addArticles');
    Route::post('/addArticles', [AdminController::class, 'postAddArticles'])->name('admin.postaddarticles');

    // ✅ CORRECTION : une seule route GET /viewArticles (les deux étaient identiques)
    Route::get('/viewArticles', [AdminController::class, 'viewArticles'])->name('admin.viewArticles');

    Route::get('/deleteArticle/{Code}', [AdminController::class, 'deleteArticle'])->name('admin.deleteArticle');
    Route::get('/updateArticle/{Code}', [AdminController::class, 'updateArticle'])->name('admin.updateArticle');
    Route::post('/updateArticle/{Code}', [AdminController::class, 'postUpdateArticle'])->name('admin.postUptadeArticles');

    // ✅ CORRECTION : deux routes GET /achats avec le même nom → supprimé le doublon
    // viewAchat (avec recherche) est géré dans viewAchats via $request->search
    Route::get('/achats', [AdminController::class, 'viewAchats'])->name('admin.achats');
    Route::post('/achats/import', [AdminController::class, 'importAchats'])->name('achats.import');

    Route::get('/ventes', [AdminController::class, 'Ventes'])->name('admin.ventes');

    // ✅ CORRECTION : doublon supprimé sur /stocks
    Route::get('/stocks', [AdminController::class, 'stocks'])->name('admin.stocks');
    Route::post('/stocks/calcul', [AdminController::class, 'calculStock'])->name('admin.calculStock');
    Route::post('/update-stock', [AdminController::class, 'updateStock'])->name('admin.updateStock');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
