<?php

namespace App\Http\Controllers\Veterinarian;

use App\Http\Controllers\Controller;
use App\Models\VeterinaryHealthCertificate;
use App\Models\Owner;
use App\Models\Pet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HealthCertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = VeterinaryHealthCertificate::with(['owner', 'pet'])->latest('certificate_date')->latest('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('control_number', 'like', "%{$s}%")
                    ->orWhere('owner_name', 'like', "%{$s}%")
                    ->orWhere('pet_name', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('certificate_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('certificate_date', '<=', $request->date_to);
        }

        $certificates = $query->paginate(15)->withQueryString();
        $nextControlNumber = VeterinaryHealthCertificate::generateControlNumber();
        $owners = Owner::with('pets')->orderBy('full_name')->get();
        $today = Carbon::today()->format('Y-m-d');

        return view('veterinarian.certificates.index', compact('certificates', 'nextControlNumber', 'owners', 'today'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'certificate_date' => 'required|date',
            'owner_id' => 'nullable|exists:owners,id',
            'pet_id' => 'nullable|exists:pets,id',
            'owner_name' => 'required|string|max:150',
            'residing_at' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'destination' => 'required|string|max:255',
            'pet_name' => 'required|string|max:100',
            'species' => 'required|string|max:50',
            'breed' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'sex' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|string|max:100',
            'weight' => 'nullable|string|max:50',
            'microchip' => 'nullable|string|max:100',
            'rabies_vaccination_date' => 'nullable|date',
            'rabies_vaccine_name' => 'nullable|string|max:100',
            'rabies_lot_number' => 'nullable|string|max:100',
            'veterinarian_name' => 'required|string|max:150',
            'tin_no' => 'nullable|string|max:50',
            'ptr_no' => 'nullable|string|max:50',
            'prc_no' => 'nullable|string|max:50',
            'license_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $certDate = Carbon::parse($validated['certificate_date']);
        $validated['control_number'] = VeterinaryHealthCertificate::generateControlNumber($certDate);
        $validated['veterinarian_id'] = auth()->id();
        $validated['microchip'] = $validated['microchip'] ?: 'None';
        $validated['rabies_vaccine_name'] = $validated['rabies_vaccine_name'] ?: 'Rabisin';

        $cert = VeterinaryHealthCertificate::create($validated);

        if ($request->input('action_type') === 'save_and_print') {
            return redirect()->route('vet.certificates.print', $cert)->with('auto_print', true);
        }

        return redirect()->route('vet.certificates.index')->with('success', "Veterinary Health Certificate {$cert->control_number} generated successfully!");
    }

    public function show(VeterinaryHealthCertificate $certificate)
    {
        return view('veterinarian.certificates.show', compact('certificate'));
    }

    public function update(Request $request, VeterinaryHealthCertificate $certificate)
    {
        $validated = $request->validate([
            'certificate_date' => 'required|date',
            'owner_id' => 'nullable|exists:owners,id',
            'pet_id' => 'nullable|exists:pets,id',
            'owner_name' => 'required|string|max:150',
            'residing_at' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'destination' => 'required|string|max:255',
            'pet_name' => 'required|string|max:100',
            'species' => 'required|string|max:50',
            'breed' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'sex' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|string|max:100',
            'weight' => 'nullable|string|max:50',
            'microchip' => 'nullable|string|max:100',
            'rabies_vaccination_date' => 'nullable|date',
            'rabies_vaccine_name' => 'nullable|string|max:100',
            'rabies_lot_number' => 'nullable|string|max:100',
            'veterinarian_name' => 'required|string|max:150',
            'tin_no' => 'nullable|string|max:50',
            'ptr_no' => 'nullable|string|max:50',
            'prc_no' => 'nullable|string|max:50',
            'license_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['microchip'] = $validated['microchip'] ?: 'None';
        $validated['rabies_vaccine_name'] = $validated['rabies_vaccine_name'] ?: 'Rabisin';

        $certificate->update($validated);

        if ($request->input('action_type') === 'save_and_print') {
            return redirect()->route('vet.certificates.print', $certificate)->with('auto_print', true);
        }

        return redirect()->route('vet.certificates.index')->with('success', "Veterinary Health Certificate {$certificate->control_number} updated successfully!");
    }

    public function print(VeterinaryHealthCertificate $certificate)
    {
        return view('veterinarian.certificates.print', compact('certificate'));
    }

    public function destroy(VeterinaryHealthCertificate $certificate)
    {
        $code = $certificate->control_number;
        $certificate->delete();

        return redirect()->route('vet.certificates.index')->with('success', "Certificate {$code} deleted successfully.");
    }
}
