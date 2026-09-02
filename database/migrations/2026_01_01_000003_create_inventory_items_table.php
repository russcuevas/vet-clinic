<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique(); // e.g. ITM-001
            $table->string('name');
            $table->enum('category', ['pet_supplies', 'medicine', 'grooming_supply', 'vaccine', 'accessories'])->default('pet_supplies');
            $table->text('description')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('unit')->default('pcs'); // pcs, bottle, box, tablet, etc.
            $table->decimal('unit_price', 10, 2); // Selling price
            $table->decimal('cost_price', 10, 2)->default(0.00);
            $table->integer('reorder_level')->default(5);
            $table->string('image')->nullable(); // in public/uploads/supplies/
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
