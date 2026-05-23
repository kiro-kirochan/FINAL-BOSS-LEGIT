<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    /** GET /api/doctors — list all doctors with optional search & filter */
    public function index(Request $request)
    {
        $query = Doctor::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('name')->get(),
        ]);
    }

    /** POST /api/doctors — create a new doctor */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|max:100|unique:doctb,email',
            'password'       => 'required|string|min:6',
            'specialization' => 'required|string|max:100',
            'docFees'        => 'required|numeric|min:0',
        ]);

        $data['password'] = Hash::make($data['password']);
        $doctor = Doctor::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Doctor registered successfully!',
            'data'    => $doctor,
        ], 201);
    }

    /** GET /api/doctors/{id} — show one doctor */
    public function show(Doctor $doctor)
    {
        return response()->json(['success' => true, 'data' => $doctor]);
    }

    /** PUT /api/doctors/{id} — update a doctor */
    public function update(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => ['required','email','max:100', Rule::unique('doctb','email')->ignore($doctor->id)],
            'password'       => 'nullable|string|min:6',
            'specialization' => 'required|string|max:100',
            'docFees'        => 'required|numeric|min:0',
        ]);

        $doctor->name           = $data['name'];
        $doctor->email          = $data['email'];
        $doctor->specialization = $data['specialization'];
        $doctor->docFees        = $data['docFees'];

        if (!empty($data['password'])) {
            $doctor->password = Hash::make($data['password']);
        }

        $doctor->save();

        return response()->json([
            'success' => true,
            'message' => 'Doctor updated successfully!',
            'data'    => $doctor,
        ]);
    }

    /** DELETE /api/doctors/{id} — delete a doctor */
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return response()->json(['success' => true, 'message' => 'Doctor deleted successfully!']);
    }

    /** GET /api/specializations — distinct list for dropdowns */
    public function specializations()
    {
        $specs = Doctor::select('specialization')
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization');

        return response()->json(['success' => true, 'data' => $specs]);
    }
}
