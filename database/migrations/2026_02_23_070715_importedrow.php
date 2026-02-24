<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crée la table `imported_rows` pour stocker les hashes SHA-256
 * de chaque ligne déjà importée, permettant la déduplication
 * inter-imports (même fichier, dates différentes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imported_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imported_file_id')
                ->constrained('imported_files')
                ->onDelete('cascade');
            $table->string('row_hash', 64); // SHA-256
            $table->timestamps();

            // Un hash est unique PAR FICHIER : évite les doublons entre imports successifs
            $table->unique(['imported_file_id', 'row_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imported_rows');
    }
};
