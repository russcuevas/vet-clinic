<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Instrument;
use App\Models\InstrumentRestockLog;
use Carbon\Carbon;

class InstrumentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Inventory Officer user exists
        $inventoryOfficer = User::firstOrCreate(
            ['email' => 'inventory@sanmodesto.com'],
            [
                'name' => 'Mark Alvarez',
                'password' => Hash::make('password'),
                'role' => 'inventory_officer',
                'contact_number' => '0925-678-9012',
                'status' => 'active',
            ]
        );

        // 2. Ensure Back Office user exists
        $backOffice = User::firstOrCreate(
            ['email' => 'backoffice@sanmodesto.com'],
            [
                'name' => 'Patricia Ramos',
                'password' => Hash::make('password'),
                'role' => 'back_office',
                'contact_number' => '0926-789-0123',
                'status' => 'active',
            ]
        );

        // 3. Sample Instruments
        $sampleInstruments = [
            [
                'item_code' => 'SRG-0001',
                'name' => 'Metzenbaum Dissecting Scissors (Curved, 14cm)',
                'category' => 'surgical',
                'description' => 'Medical grade stainless steel curved scissors for fine soft tissue dissection.',
                'stock_quantity' => 12,
                'unit' => 'pcs',
                'reorder_level' => 4,
                'storage_location' => 'Operating Room Cabinet 1',
                'status' => 'in_stock',
            ],
            [
                'item_code' => 'SRG-0002',
                'name' => 'Halsted Mosquito Hemostatic Forceps (Straight, 12.5cm)',
                'category' => 'surgical',
                'description' => 'Fine pointed tip with transverse serrations for delicate blood vessel clamping.',
                'stock_quantity' => 16,
                'unit' => 'pcs',
                'reorder_level' => 5,
                'storage_location' => 'Operating Room Cabinet 1',
                'status' => 'in_stock',
            ],
            [
                'item_code' => 'DXT-0001',
                'name' => 'Welch Allyn Pocket LED Otoscope Diagnostic Kit',
                'category' => 'diagnostic',
                'description' => 'Fiber optic lighting veterinary ear examination device with reusable specula.',
                'stock_quantity' => 3,
                'unit' => 'sets',
                'reorder_level' => 2,
                'storage_location' => 'Consultation Room 1 Shelf',
                'status' => 'in_stock',
            ],
            [
                'item_code' => 'DXT-0002',
                'name' => 'Littmann Master Classic II Veterinary Stethoscope',
                'category' => 'diagnostic',
                'description' => 'Tunable diaphragm acoustic stethoscope engineered for small and large animals.',
                'stock_quantity' => 5,
                'unit' => 'pcs',
                'reorder_level' => 2,
                'storage_location' => 'Doctor Station A',
                'status' => 'in_stock',
            ],
            [
                'item_code' => 'DNT-0001',
                'name' => 'Ultrasonic Veterinary Dental Scaler & Polisher Kit',
                'category' => 'dental',
                'description' => 'High frequency piezoceramic scaler for pet tartar and calculus prophylaxis.',
                'stock_quantity' => 2,
                'unit' => 'kits',
                'reorder_level' => 1,
                'storage_location' => 'Dental Prophylaxis Station',
                'status' => 'in_stock',
            ],
            [
                'item_code' => 'LAB-0001',
                'name' => 'Handheld Vet Microchip Scanner & RFID Reader',
                'category' => 'laboratory',
                'description' => 'Universal ISO 11784/11785 FDX-B pet identification microchip scanner.',
                'stock_quantity' => 4,
                'unit' => 'units',
                'reorder_level' => 2,
                'storage_location' => 'Triage Desk Drawer',
                'status' => 'in_stock',
            ],
            [
                'item_code' => 'STZ-0001',
                'name' => 'Self-Sealing Autoclave Sterilization Pouches (200s)',
                'category' => 'sterilization',
                'description' => 'Triple-seal medical grade pouches with steam indicator for instrument pack.',
                'stock_quantity' => 2,
                'unit' => 'boxes',
                'reorder_level' => 3,
                'storage_location' => 'Sterilization Supply Rack',
                'status' => 'low_stock',
            ],
            [
                'item_code' => 'INST-0001',
                'name' => 'Digital Fast-Read Veterinary Rectal Thermometer',
                'category' => 'consumable_tools',
                'description' => 'Waterproof 10-second rapid measurement flexible probe digital thermometer.',
                'stock_quantity' => 8,
                'unit' => 'pcs',
                'reorder_level' => 3,
                'storage_location' => 'Treatment Room Counter',
                'status' => 'in_stock',
            ],
        ];

        foreach ($sampleInstruments as $insData) {
            $instrument = Instrument::firstOrCreate(
                ['item_code' => $insData['item_code']],
                $insData
            );

            // Log Initial Stock if not already logged
            if ($instrument->restockLogs()->count() === 0) {
                InstrumentRestockLog::create([
                    'instrument_id' => $instrument->id,
                    'user_id' => $inventoryOfficer->id,
                    'quantity_added' => $instrument->stock_quantity,
                    'quantity_before' => 0,
                    'quantity_after' => $instrument->stock_quantity,
                    'action_type' => 'initial_stock',
                    'remarks' => 'Initial clinic instrument inventory registration',
                    'created_at' => Carbon::now()->subDays(3),
                ]);
            }
        }

        // Add a recent restock entry by Back Office
        $firstInstrument = Instrument::where('item_code', 'SRG-0001')->first();
        if ($firstInstrument && $firstInstrument->restockLogs()->count() < 2) {
            InstrumentRestockLog::create([
                'instrument_id' => $firstInstrument->id,
                'user_id' => $backOffice->id,
                'quantity_added' => 5,
                'quantity_before' => 7,
                'quantity_after' => 12,
                'action_type' => 'restock',
                'remarks' => 'Restocked from Medical Supply Co. batch #VET-2026-09',
                'created_at' => Carbon::now()->subHours(5),
            ]);
        }
    }
}
