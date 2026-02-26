<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter colonne fournisseur (texte) dans stocks si elle n'existe pas
        if (!Schema::hasColumn('stocks', 'fournisseur')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->string('fournisseur')->nullable()->after('liblong');
            });
        }
        // Note : articles.fournisseur existe déjà, rien à faire
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('fournisseur');
        });
    }
};
