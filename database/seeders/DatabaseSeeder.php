<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\InventoryItem;
use App\Models\Instrument;
use App\Models\InstrumentRestockLog;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\GroomingRecord;
use App\Models\Bill;
use App\Models\BillItem;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Users for all Roles (including Inventory Officer & Back Office)
        $admin = User::create([
            'name' => 'Cen Tutor',
            'email' => 'admin@sanmodesto.com',
            'password' => Hash::make('123456789'),
            'role' => 'admin',
            'license_no' => 'PRC-VET-001099',
            'contact_number' => '0917-123-4567',
            'status' => 'active',
        ]);

        $cashier = User::create([
            'name' => 'Maria Santos',
            'email' => 'cashier@sanmodesto.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'contact_number' => '0918-234-5678',
            'status' => 'active',
        ]);

        $vet = User::create([
            'name' => 'Dr. Juan Dela Cruz, DVM',
            'email' => 'vet@sanmodesto.com',
            'password' => Hash::make('password'),
            'role' => 'veterinarian',
            'license_no' => 'PRC-VET-009821',
            'contact_number' => '0920-345-6789',
            'status' => 'active',
        ]);

        $manager = User::create([
            'name' => 'Carlos Mendoza',
            'email' => 'manager@sanmodesto.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'contact_number' => '0922-456-7890',
            'status' => 'active',
        ]);

        $inventoryOfficer = User::create([
            'name' => 'Mark Alvarez',
            'email' => 'inventory@sanmodesto.com',
            'password' => Hash::make('password'),
            'role' => 'inventory_officer',
            'contact_number' => '0925-678-9012',
            'status' => 'active',
        ]);

        $backOffice = User::create([
            'name' => 'Patricia Ramos',
            'email' => 'backoffice@sanmodesto.com',
            'password' => Hash::make('password'),
            'role' => 'back_office',
            'contact_number' => '0926-789-0123',
            'status' => 'active',
        ]);

        $receptionist = User::create([
            'name' => 'Ella Mae Dizon',
            'email' => 'receptionist@sanmodesto.com',
            'password' => Hash::make('password'),
            'role' => 'receptionist',
            'contact_number' => '0928-111-2233',
            'status' => 'active',
        ]);

        // 2. Create Initial Inventory Items (Pet Supplies, Medicines, Grooming)
        $items = [
            [
                'item_code' => 'ITM-0001',
                'name' => 'Royal Canin Mini Adult (1kg)',
                'category' => 'pet_supplies',
                'description' => 'Nutritious formula for small adult dogs up to 10kg.',
                'stock_quantity' => 28,
                'unit' => 'bag',
                'unit_price' => 580.00,
                'cost_price' => 450.00,
                'reorder_level' => 5,
            ],
            [
                'item_code' => 'ITM-0002',
                'name' => 'Whiskas Ocean Fish Dry Cat Food (1.2kg)',
                'category' => 'pet_supplies',
                'description' => 'Complete and balanced nutrition for adult cats.',
                'stock_quantity' => 35,
                'unit' => 'bag',
                'unit_price' => 395.00,
                'cost_price' => 310.00,
                'reorder_level' => 8,
            ],
            [
                'item_code' => 'ITM-0003',
                'name' => 'Bentonite Clumping Cat Litter (10L)',
                'category' => 'pet_supplies',
                'description' => 'Lavender scented dust-free fast clumping litter.',
                'stock_quantity' => 15,
                'unit' => 'bag',
                'unit_price' => 320.00,
                'cost_price' => 240.00,
                'reorder_level' => 5,
            ],
            [
                'item_code' => 'ITM-0004',
                'name' => 'NexGard Spectra Medium (7.5-15kg)',
                'category' => 'medicine',
                'description' => 'Monthly chewable treatment for fleas, ticks, heartworm, and worms.',
                'stock_quantity' => 40,
                'unit' => 'chewable',
                'unit_price' => 750.00,
                'cost_price' => 600.00,
                'reorder_level' => 10,
            ],
            [
                'item_code' => 'ITM-0005',
                'name' => 'Broad-Spectrum Canex Dewormer Tablet',
                'category' => 'medicine',
                'description' => 'Effective single dose deworming for dogs and puppies.',
                'stock_quantity' => 50,
                'unit' => 'tablet',
                'unit_price' => 180.00,
                'cost_price' => 120.00,
                'reorder_level' => 15,
            ],
            [
                'item_code' => 'ITM-0006',
                'name' => 'Vanguard Plus 5 (DHPPi Vaccine)',
                'category' => 'vaccine',
                'description' => 'Core 5-in-1 immunization for puppies and dogs.',
                'stock_quantity' => 22,
                'unit' => 'vial',
                'unit_price' => 650.00,
                'cost_price' => 480.00,
                'reorder_level' => 6,
            ],
            [
                'item_code' => 'ITM-0007',
                'name' => 'Hypoallergenic Oatmeal Dog Shampoo (500ml)',
                'category' => 'grooming_supply',
                'description' => 'Gentle formula for sensitive, itchy skin.',
                'stock_quantity' => 18,
                'unit' => 'bottle',
                'unit_price' => 420.00,
                'cost_price' => 300.00,
                'reorder_level' => 4,
            ],
            [
                'item_code' => 'ITM-0008',
                'name' => 'Pet Odor Eliminator Spray (250ml)',
                'category' => 'pet_supplies',
                'description' => 'Fast-acting neutralizing spray for cages and floors.',
                'stock_quantity' => 3,
                'unit' => 'bottle',
                'unit_price' => 280.00,
                'cost_price' => 190.00,
                'reorder_level' => 5, // Triggers low stock alert!
            ],
        ];

        foreach ($items as $item) {
            InventoryItem::create($item);
        }

        // 3. Create Sample Owners (Flowchart: Owner Information Database)
        $owner1 = Owner::create([
            'client_code' => 'OWN-2026-0001',
            'full_name' => 'Angelo Tan',
            'contact_number' => '0917-889-1029',
            'address' => 'Blk 12 Lot 4, San Lorenzo Subd., San Modesto',
            'email' => 'angelo.tan@gmail.com',
            'status' => 'active',
        ]);

        $owner2 = Owner::create([
            'client_code' => 'OWN-2026-0002',
            'full_name' => 'Clarissa Villanueva',
            'contact_number' => '0928-554-3210',
            'address' => '142 Rizal Ave, Poblacion, San Modesto',
            'email' => 'clarissa.v@yahoo.com',
            'status' => 'active',
        ]);

        $owner3 = Owner::create([
            'client_code' => 'OWN-2026-0003',
            'full_name' => 'Roberto Bautista',
            'contact_number' => '0919-772-6543',
            'address' => 'Phase 3, Villa Modesto Homes, San Modesto',
            'email' => 'robert.b@outlook.com',
            'status' => 'active',
        ]);

        // 4. Create Sample Pets (Flowchart: Pet Information Database)
        $pet1 = Pet::create([
            'pet_code' => 'PET-2026-0001',
            'owner_id' => $owner1->id,
            'name' => 'Milo',
            'species' => 'Dog',
            'breed' => 'Golden Retriever',
            'age' => '2 years old',
            'sex' => 'Male',
            'color' => 'Golden Honey',
            'birth_date' => Carbon::now()->subYears(2),
        ]);

        $pet2 = Pet::create([
            'pet_code' => 'PET-2026-0002',
            'owner_id' => $owner2->id,
            'name' => 'Luna',
            'species' => 'Cat',
            'breed' => 'Persian',
            'age' => '1 year old',
            'sex' => 'Female',
            'color' => 'Pure White',
            'birth_date' => Carbon::now()->subYear(1),
        ]);

        $pet3 = Pet::create([
            'pet_code' => 'PET-2026-0003',
            'owner_id' => $owner3->id,
            'name' => 'Bantay',
            'species' => 'Dog',
            'breed' => 'Shih Tzu',
            'age' => '3 years old',
            'sex' => 'Neutered Male',
            'color' => 'Tri-color (White, Black & Brown)',
            'birth_date' => Carbon::now()->subYears(3),
        ]);

        // 5. Create Veterinary Medical Record & Prescription (Flowchart: Medical Record + Prescription)
        $med1 = MedicalRecord::create([
            'record_code' => 'MED-2026-0001',
            'owner_id' => $owner1->id,
            'pet_id' => $pet1->id,
            'veterinarian_id' => $vet->id,
            'service_type' => 'consultation',
            'body_weight' => '28.5 kg',
            'temperature' => '38.4 °C',
            'body_score' => '3/5 Ideal',
            'history_taking' => 'Owner noticed mild itching around ears and occasional shaking of head for 3 days. Eating normally.',
            'diagnosis' => 'Mild bilateral otitis externa (ear infection), non-ulcerative.',
            'veterinarians_notes' => 'Cleaned both ear canals with mild antiseptic ear flush. Advised owner to avoid water entry during bathing. Follow up in 7 days.',
            'service_fee' => 450.00,
            'status' => 'billed',
        ]);

        $rx1 = Prescription::create([
            'prescription_code' => 'RX-2026-0001',
            'medical_record_id' => $med1->id,
            'owner_id' => $owner1->id,
            'pet_id' => $pet1->id,
            'veterinarian_id' => $vet->id,
            'veterinarian_name' => 'Dr. Juan Dela Cruz, DVM',
            'license_no' => 'PRC-VET-009821',
            'body_weight' => '28.5 kg',
            'rx_details' => "1. Otic Ear Drops (Gentamicin + Betamethasone)\n   - Apply 4 drops into both ear canals BID (twice daily) for 7 days.\n   - Gently massage ear base after instillation.\n\n2. Apoquel 16mg\n   - 1 tablet once daily for 5 days for pruritus relief.",
            'instructions' => 'Keep ears dry. Recheck if redness persists beyond 5 days.',
            'date_issued' => Carbon::now()->format('Y-m-d'),
        ]);

        // Follow up record
        MedicalRecord::create([
            'record_code' => 'MED-2026-0002',
            'owner_id' => $owner2->id,
            'pet_id' => $pet2->id,
            'veterinarian_id' => $vet->id,
            'service_type' => 'wellness',
            'body_weight' => '3.8 kg',
            'temperature' => '38.2 °C',
            'body_score' => '3/5 Ideal',
            'history_taking' => 'Routine annual wellness exam and core vaccination booster.',
            'diagnosis' => 'Healthy adult feline in good physical condition.',
            'veterinarians_notes' => 'Administered Tricat vaccine and topical dewormer. Next visit due in 12 months.',
            'service_fee' => 600.00,
            'status' => 'ongoing',
        ]);

        // 6. Create Grooming Record (Flowchart: Grooming Database)
        $groom1 = GroomingRecord::create([
            'grooming_code' => 'GRM-2026-0001',
            'owner_id' => $owner3->id,
            'pet_id' => $pet3->id,
            'body_weight' => '5.6 kg',
            'temperature' => '38.1 °C',
            'body_score' => '3/5 Ideal',
            'style' => 'Shih Tzu Teddy Bear Cut with Ear Trim & Paw Pad Shave',
            'groomer_observation_notes' => 'Pet was very cooperative. Coat had minor mats near armpits, successfully brushed out. Nail clipping and ear cleaning completed.',
            'price' => 650.00,
            'status' => 'billed',
        ]);

        // 7. Create Central Billing Records & Line Items (Flowchart: Billing Data Base -> Sales Report)
        // Bill 1: Veterinary Consultation + Meds
        $bill1 = Bill::create([
            'invoice_no' => 'INV-2026-0001',
            'owner_id' => $owner1->id,
            'pet_id' => $pet1->id,
            'cashier_id' => $cashier->id,
            'medical_record_id' => $med1->id,
            'client_name' => $owner1->full_name,
            'service_type' => 'veterinary',
            'subtotal' => 1200.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_amount' => 1200.00,
            'paid_amount' => 1500.00,
            'change_amount' => 300.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => Carbon::now()->subHours(4),
            'notes' => 'Consultation + Otic drops prescription',
        ]);

        BillItem::create([
            'bill_id' => $bill1->id,
            'item_name' => 'Veterinary Consultation Fee',
            'item_type' => 'service',
            'quantity' => 1,
            'unit_price' => 450.00,
            'total_price' => 450.00,
        ]);
        BillItem::create([
            'bill_id' => $bill1->id,
            'item_name' => 'Otic Ear Drops & Treatment',
            'item_type' => 'medicine',
            'quantity' => 1,
            'unit_price' => 750.00,
            'total_price' => 750.00,
        ]);

        // Bill 2: Grooming Record
        $bill2 = Bill::create([
            'invoice_no' => 'INV-2026-0002',
            'owner_id' => $owner3->id,
            'pet_id' => $pet3->id,
            'cashier_id' => $cashier->id,
            'grooming_record_id' => $groom1->id,
            'client_name' => $owner3->full_name,
            'service_type' => 'grooming',
            'subtotal' => 650.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_amount' => 650.00,
            'paid_amount' => 650.00,
            'change_amount' => 0.00,
            'payment_method' => 'gcash',
            'payment_status' => 'paid',
            'transaction_date' => Carbon::now()->subHours(2),
            'notes' => 'Full Grooming Teddy Bear Cut',
        ]);

        BillItem::create([
            'bill_id' => $bill2->id,
            'item_name' => 'Full Grooming (Shih Tzu Teddy Bear Cut)',
            'item_type' => 'grooming',
            'quantity' => 1,
            'unit_price' => 650.00,
            'total_price' => 650.00,
        ]);

        // Bill 3: Pet Supplies Purchase (Flowchart: Pet Supplies -> Billing Data Base)
        $bill3 = Bill::create([
            'invoice_no' => 'INV-2026-0003',
            'owner_id' => $owner2->id,
            'pet_id' => $pet2->id,
            'cashier_id' => $cashier->id,
            'client_name' => $owner2->full_name,
            'service_type' => 'pet_supplies',
            'subtotal' => 715.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'total_amount' => 715.00,
            'paid_amount' => 1000.00,
            'change_amount' => 285.00,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => Carbon::now()->subHour(1),
            'notes' => 'Supplies purchase: Whiskas food + Bentonite cat litter',
        ]);

        BillItem::create([
            'bill_id' => $bill3->id,
            'item_name' => 'Whiskas Ocean Fish Dry Cat Food (1.2kg)',
            'item_type' => 'product',
            'quantity' => 1,
            'unit_price' => 395.00,
            'total_price' => 395.00,
        ]);
        BillItem::create([
            'bill_id' => $bill3->id,
            'item_name' => 'Bentonite Clumping Cat Litter (10L)',
            'item_type' => 'product',
            'quantity' => 1,
            'unit_price' => 320.00,
            'total_price' => 320.00,
        ]);

        // 7. Seed Clinic Instruments & Equipment (Separate Non-POS Inventory)
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
            $instrument = Instrument::create($insData);

            // Log Initial Stock (attributing to Inventory Officer)
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

        // Add a recent restock entry by Back Office
        $firstInstrument = Instrument::where('item_code', 'SRG-0001')->first();
        if ($firstInstrument) {
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
