<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imported_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique(); // Nom du fichier (clé de déduplication)
            $table->string('path');
            $table->timestamp('imported_at');     // Date du dernier import réussi
            $table->integer('total_rows')->default(0); // Nombre total de lignes importées (cumulé)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imported_files');
    }
};
