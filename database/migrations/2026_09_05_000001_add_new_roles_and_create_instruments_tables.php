<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update users table role column to support new roles
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cashier', 'veterinarian', 'manager', 'inventory_officer', 'back_office') DEFAULT 'admin'");
        } catch (\Exception $e) {
            // Fallback for sqlite / generic drivers
        }

        // 2. Create Instruments table (Clinic & Surgical tools, separate from POS)
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique(); // e.g. INST-0001
            $table->string('name');
            $table->string('category')->default('general_equipment'); 
            // e.g. surgical, diagnostic, dental, general_equipment, laboratory, sterilization, consumable_tools
            $table->text('description')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('unit')->default('pcs'); // pcs, sets, kits, units, boxes
            $table->integer('reorder_level')->default(3); // minimum threshold
            $table->string('storage_location')->nullable(); // e.g. Cabinet A, OR 1, Sterilization Room
            $table->string('status')->default('in_stock'); // in_stock, low_stock, out_of_stock
            $table->timestamps();
        });

        // 3. Create Restock Logs / Audit Trail
        Schema::create('instrument_restock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained('instruments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Restocked By
            $table->integer('quantity_added');
            $table->integer('quantity_before')->default(0);
            $table->integer('quantity_after')->default(0);
            $table->string('action_type')->default('restock'); // initial_stock, restock, adjustment
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_restock_logs');
        Schema::dropIfExists('instruments');

        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cashier', 'veterinarian', 'manager') DEFAULT 'admin'");
        } catch (\Exception $e) {
            // Fallback
        }
    }
};
