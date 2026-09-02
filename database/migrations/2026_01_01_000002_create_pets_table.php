<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('pet_code')->unique(); // Generated key code (e.g. PET-2026-0001)
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->string('name');
            $table->string('species'); // Dog, Cat, Bird, etc.
            $table->string('breed');
            $table->string('age'); // e.g. 2 years, 6 months
            $table->string('sex'); // Male, Female, Neutered Male, Spayed Female
            $table->string('color')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo')->nullable(); // Saved in public/uploads/pets/
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
