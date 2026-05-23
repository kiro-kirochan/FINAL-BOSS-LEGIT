<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /** GET /api/patients — list all patients with optional search */
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('fname', 'like', "%{$s}%")
                  ->orWhere('lname', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('contact', 'like', "%{$s}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('fname')->get(),
        ]);
    }

    /** POST /api/patients — register a new patient */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fname'   => 'required|string|max:100',
            'lname'   => 'required|string|max:100',
            'email'   => 'required|email|max:100|unique:patreg,email',
            'password'=> 'required|string|min:6',
            'contact' => 'required|string|max:20',
            'gender'  => 'required|in:Male,Female,Other',
        ]);

        $data['password'] = Hash::make($data['password']);
        $patient = Patient::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient registered successfully!',
            'data'    => $patient,
        ], 201);
    }

    /** GET /api/patients/{id} */
    public function show(Patient $patient)
    {
        return response()->json(['success' => true, 'data' => $patient]);
    }

    /** PUT /api/patients/{id} */
    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'fname'   => 'required|string|max:100',
            'lname'   => 'required|string|max:100',
            'email'   => ['required','email','max:100', Rule::unique('patreg','email')->ignore($patient->id)],
            'password'=> 'nullable|string|min:6',
            'contact' => 'required|string|max:20',
            'gender'  => 'required|in:Male,Female,Other',
        ]);

        $patient->fname   = $data['fname'];
        $patient->lname   = $data['lname'];
        $patient->email   = $data['email'];
        $patient->contact = $data['contact'];
        $patient->gender  = $data['gender'];

        if (!empty($data['password'])) {
            $patient->password = Hash::make($data['password']);
        }

        $patient->save();

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully!',
            'data'    => $patient,
        ]);
    }

    /** DELETE /api/patients/{id} */
    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(['success' => true, 'message' => 'Patient deleted successfully!']);
    }
}
