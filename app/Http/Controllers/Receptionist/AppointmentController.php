<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\User;
use App\Models\MedicalRecord;
use App\Models\GroomingRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category'); // 'clinic', 'grooming'
        $status = $request->query('status'); // 'pending', 'confirmed', 'checked_in', 'completed', 'cancelled'
        $dateFilter = $request->query('date_filter', 'today'); // 'today', 'upcoming', 'all', 'past'
        $search = $request->query('search');

        $query = Appointment::with(['owner', 'pet', 'bookedBy', 'veterinarian'])
            ->latest('appointment_date')
            ->latest('appointment_time');

        if ($category && in_array($category, ['clinic', 'grooming'])) {
            $query->where('service_category', $category);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $today = Carbon::today();
        if ($dateFilter === 'today') {
            $query->whereDate('appointment_date', $today);
        } elseif ($dateFilter === 'upcoming') {
            $query->whereDate('appointment_date', '>=', $today);
        } elseif ($dateFilter === 'past') {
            $query->whereDate('appointment_date', '<', $today);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                  ->orWhere('purpose_examination_notes', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('contact_number', 'like', "%{$search}%")
                         ->orWhere('client_code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pet', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('pet_code', 'like', "%{$search}%");
                  });
            });
        }

        $appointments = $query->paginate(15)->withQueryString();

        // Statistics
        $todayTotal = Appointment::whereDate('appointment_date', $today)->count();
        $todayClinic = Appointment::whereDate('appointment_date', $today)->where('service_category', 'clinic')->count();
        $todayGrooming = Appointment::whereDate('appointment_date', $today)->where('service_category', 'grooming')->count();
        $todayCheckedIn = Appointment::whereDate('appointment_date', $today)->where('status', 'checked_in')->count();
        $upcomingCount = Appointment::whereDate('appointment_date', '>=', $today)->count();

        // Load all active owners with their pets for dynamic booking modal
        $owners = Owner::with('pets')->orderBy('full_name')->get();
        $veterinarians = User::where('role', 'veterinarian')->where('status', 'active')->orderBy('name')->get();

        return view('receptionist.appointments.index', compact(
            'appointments',
            'todayTotal',
            'todayClinic',
            'todayGrooming',
            'todayCheckedIn',
            'upcomingCount',
            'owners',
            'veterinarians',
            'category',
            'status',
            'dateFilter',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $serviceCategory = $request->input('service_category', 'clinic');
        $clientMode = $request->input('client_mode', 'existing'); // 'existing' or 'new'
        $petMode = $request->input('pet_mode', 'existing'); // 'existing' or 'new'

        $rules = [
            'service_category' => 'required|in:clinic,grooming',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'purpose_examination_notes' => 'nullable|string|max:1000',
            'veterinarian_id' => 'nullable|exists:users,id',
        ];

        if ($serviceCategory === 'clinic') {
            $rules['service_type'] = 'required|in:consultation,follow_up,wellness';
        } else {
            $rules['service_type'] = 'nullable|string';
        }

        if ($clientMode === 'existing') {
            $rules['owner_id'] = 'required|exists:owners,id';
            if ($petMode === 'existing') {
                $rules['pet_id'] = 'required|exists:pets,id';
            } else {
                $rules['pet_name'] = 'required|string|max:100';
                $rules['pet_species'] = 'required|string|max:50';
                $rules['pet_breed'] = 'nullable|string|max:100';
                $rules['pet_birth_date'] = 'nullable|date';
                $rules['pet_age'] = 'nullable|string|max:50';
                $rules['pet_sex'] = 'nullable|string|max:20';
                $rules['pet_color'] = 'nullable|string|max:100';
            }
        } else {
            // New Client
            $rules['owner_name'] = 'required|string|max:150';
            $rules['owner_contact'] = 'required|string|max:50';
            $rules['owner_address'] = 'required|string|max:255';
            $rules['owner_email'] = 'nullable|email|max:150';

            // New Pet
            $rules['pet_name'] = 'required|string|max:100';
            $rules['pet_species'] = 'required|string|max:50';
            $rules['pet_breed'] = 'nullable|string|max:100';
            $rules['pet_birth_date'] = 'nullable|date';
            $rules['pet_age'] = 'nullable|string|max:50';
            $rules['pet_sex'] = 'nullable|string|max:20';
            $rules['pet_color'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $isNewClient = false;
            $isNewPet = false;

            // 1. Resolve Owner
            if ($clientMode === 'new') {
                $owner = Owner::create([
                    'client_code' => Owner::generateClientCode(),
                    'full_name' => $validated['owner_name'],
                    'contact_number' => $validated['owner_contact'],
                    'address' => $validated['owner_address'],
                    'email' => $validated['owner_email'] ?? null,
                    'status' => 'active',
                ]);
                $isNewClient = true;
            } else {
                $owner = Owner::findOrFail($validated['owner_id']);
            }

            // 2. Resolve Pet
            if ($clientMode === 'new' || $petMode === 'new') {
                $pet = Pet::create([
                    'pet_code' => Pet::generatePetCode(),
                    'owner_id' => $owner->id,
                    'name' => $validated['pet_name'],
                    'species' => $validated['pet_species'],
                    'breed' => $validated['pet_breed'] ?? null,
                    'birth_date' => $validated['pet_birth_date'] ?? null,
                    'age' => $validated['pet_age'] ?? null,
                    'sex' => $validated['pet_sex'] ?? null,
                    'color' => $validated['pet_color'] ?? null,
                ]);
                $isNewPet = true;
            } else {
                $pet = Pet::findOrFail($validated['pet_id']);
            }

            // 3. Create Appointment
            $serviceType = $serviceCategory === 'clinic' 
                ? $validated['service_type'] 
                : ($validated['service_type'] ?: 'grooming');

            $appointment = Appointment::create([
                'appointment_code' => Appointment::generateAppointmentCode(),
                'service_category' => $serviceCategory,
                'service_type' => $serviceType,
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'booked_by' => auth()->id(),
                'veterinarian_id' => $validated['veterinarian_id'] ?? null,
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'purpose_examination_notes' => $validated['purpose_examination_notes'] ?? null,
                'status' => 'confirmed',
                'is_new_client' => $isNewClient,
                'is_new_pet' => $isNewPet,
            ]);

            DB::commit();

            $serviceName = $serviceCategory === 'clinic' ? 'Clinical (' . ucfirst(str_replace('_', ' ', $serviceType)) . ')' : 'Grooming';
            return redirect()->back()->with('success', "Appointment {$appointment->appointment_code} successfully booked for {$owner->full_name} and {$pet->name} ({$serviceName})!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to book appointment: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,completed,cancelled',
        ]);

        $appointment->status = $validated['status'];
        $appointment->save();

        // If checked in, automatically initialize patient record if needed
        if ($validated['status'] === 'checked_in') {
            if ($appointment->service_category === 'clinic') {
                // Create draft / ongoing medical record for Vet queue
                $existingRecord = MedicalRecord::where('pet_id', $appointment->pet_id)
                    ->whereDate('created_at', Carbon::today())
                    ->where('status', 'ongoing')
                    ->first();

                if (!$existingRecord) {
                    $vetId = $appointment->veterinarian_id;
                    if (!$vetId) {
                        $activeVet = User::where('role', 'veterinarian')->where('status', 'active')->first();
                        $vetId = $activeVet ? $activeVet->id : null;
                    }

                    MedicalRecord::create([
                        'record_code' => MedicalRecord::generateRecordCode(),
                        'owner_id' => $appointment->owner_id,
                        'pet_id' => $appointment->pet_id,
                        'veterinarian_id' => $vetId,
                        'service_type' => in_array($appointment->service_type, ['consultation', 'follow_up', 'wellness']) ? $appointment->service_type : 'consultation',
                        'visit_date' => Carbon::today(),
                        'history_taking' => $appointment->purpose_examination_notes ?: 'Checked in via Reception appointment: ' . $appointment->appointment_code,
                        'diagnosis' => 'Patient arrived for ' . ucfirst(str_replace('_', ' ', $appointment->service_type ?: 'consultation')),
                        'service_fee' => 0.00,
                        'status' => 'ongoing',
                    ]);
                }
            } elseif ($appointment->service_category === 'grooming') {
                // Create grooming queue record
                $existingGroom = GroomingRecord::where('pet_id', $appointment->pet_id)
                    ->whereDate('created_at', Carbon::today())
                    ->whereIn('status', ['queued', 'in_progress'])
                    ->first();

                if (!$existingGroom) {
                    GroomingRecord::create([
                        'grooming_code' => GroomingRecord::generateGroomingCode(),
                        'owner_id' => $appointment->owner_id,
                        'pet_id' => $appointment->pet_id,
                        'style' => 'Standard Grooming Package',
                        'groomer_observation_notes' => $appointment->purpose_examination_notes ?: 'Checked in via Reception appointment: ' . $appointment->appointment_code,
                        'status' => 'queued',
                        'price' => 0.00,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', "Appointment {$appointment->appointment_code} status updated to " . ucfirst(str_replace('_', ' ', $appointment->status)) . "!");
    }

    public function destroy(Appointment $appointment)
    {
        $code = $appointment->appointment_code;
        $appointment->delete();
        return redirect()->back()->with('success', "Appointment {$code} has been deleted.");
    }

    /**
     * API to fetch pets of an owner dynamically for Select2 dropdown
     */
    public function apiGetPetsByOwner(Owner $owner)
    {
        return response()->json([
            'success' => true,
            'owner' => [
                'id' => $owner->id,
                'full_name' => $owner->full_name,
                'contact_number' => $owner->contact_number,
                'address' => $owner->address,
            ],
            'pets' => $owner->pets()->get(['id', 'name', 'species', 'breed', 'pet_code', 'age', 'sex', 'color']),
        ]);
    }
}
