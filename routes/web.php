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


    // -------------------------------------------------------------------------
    // ARTICLES
    // -------------------------------------------------------------------------
    Route::get('/addArticles',  [AdminController::class, 'addArticles'])->name('admin.addArticles');
    Route::post('/addArticles', [AdminController::class, 'postAddArticles'])->name('admin.postaddarticles');

    Route::get('/viewArticles', [AdminController::class, 'viewArticles'])->name('admin.viewArticles');

    Route::get('/deleteArticle/{Code}',  [AdminController::class, 'deleteArticle'])->name('admin.deleteArticle');
    Route::get('/updateArticle/{Code}',  [AdminController::class, 'updateArticle'])->name('admin.updateArticle');
    Route::post('/updateArticle/{Code}', [AdminController::class, 'postUpdateArticle'])->name('admin.postUptadeArticles');

    // -------------------------------------------------------------------------
    // FOURNISSEURS
    // -------------------------------------------------------------------------
    Route::get('/addFournisseurs',  [AdminController::class, 'addFournisseur'])->name('admin.addSupplier');
    Route::post('/addFournisseurs', [AdminController::class, 'postAddFournisseur'])->name('admin.postaddfournisseur');

    Route::get('/viewFournisseurs', [AdminController::class, 'viewFournisseurs'])->name('admin.viewSupplier');

    Route::get('/deleteFournisseur/{id}',  [AdminController::class, 'deleteFournisseur'])->name('admin.deleteFournisseur');
    Route::get('/updateFournisseur/{id}',  [AdminController::class, 'updateFournisseur'])->name('admin.updateFournisseur');
    Route::post('/updateFournisseur/{id}', [AdminController::class, 'postUpdateFournisseur'])->name('admin.postUpdateFournisseur');

    // -------------------------------------------------------------------------
    // ACHATS
    // -------------------------------------------------------------------------
    Route::get('/achats',        [AdminController::class, 'viewAchats'])->name('admin.achats');
    Route::post('/achats/import', [AdminController::class, 'importAchats'])->name('achats.import');

    // -------------------------------------------------------------------------
    // VENTES
    // -------------------------------------------------------------------------
    Route::get('/ventes', [AdminController::class, 'Ventes'])->name('admin.ventes');

    // -------------------------------------------------------------------------
    // STOCKS
    // -------------------------------------------------------------------------
    Route::get('/stocks',       [AdminController::class, 'stocks'])->name('admin.stocks');
    Route::post('/update-stock', [AdminController::class, 'updateStock'])->name('admin.updateStock');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
