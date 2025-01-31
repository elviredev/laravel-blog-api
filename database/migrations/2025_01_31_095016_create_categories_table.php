<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('categories', function (Blueprint $table) {
          $table->id();
          $table->string('name')->unique();
          $table->timestamps();
      });

      // Ajout de la colonne category_id dans la table posts avec une clé étrangère
      // qui ne supprime pas les posts si la catégorie est supprimée (nullOnDelete()).
      Schema::table('posts', function (Blueprint $table) {
        $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('posts', function (Blueprint $table) {
        $table->dropForeign(['category_id']);
        $table->dropColumn('category_id');
      });

      Schema::dropIfExists('categories');
    }
};
