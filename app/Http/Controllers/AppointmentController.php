<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    /** GET /api/appointments — list all appointments (with related doctor + patient names) */
    public function index(Request $request)
    {
        $query = Appointment::with(['doctor:id,name,specialization', 'patient:id,fname,lname,email,contact']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('patient', function ($q) use ($s) {
                $q->where('fname', 'like', "%{$s}%")
                  ->orWhere('lname', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            })->orWhereHas('doctor', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('appdate')->orderBy('apptime')->get(),
        ]);
    }

    /** POST /api/appointments — book a new appointment */
    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patreg,id',
            'doctor_id'  => 'required|exists:doctb,id',
            'appdate'    => 'required|date|after_or_equal:today',
            'apptime'    => 'required',
            'status'     => 'sometimes|in:Pending,Confirmed,Cancelled,Completed',
        ]);

        // Check for time-slot conflict (same doctor, same date/time)
        $conflict = Appointment::where('doctor_id', $data['doctor_id'])
            ->where('appdate', $data['appdate'])
            ->where('apptime', $data['apptime'])
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'That time slot is already booked for this doctor. Please choose a different time.',
            ], 422);
        }

        $data['status'] = $data['status'] ?? 'Pending';
        $appointment = Appointment::create($data);
        $appointment->load(['doctor:id,name,specialization', 'patient:id,fname,lname,email,contact']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully!',
            'data'    => $appointment,
        ], 201);
    }

    /** GET /api/appointments/{id} */
    public function show(Appointment $appointment)
    {
        $appointment->load(['doctor:id,name,specialization,docFees', 'patient:id,fname,lname,email,contact,gender']);
        return response()->json(['success' => true, 'data' => $appointment]);
    }

    /** PUT /api/appointments/{id} */
    public function update(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patreg,id',
            'doctor_id'  => 'required|exists:doctb,id',
            'appdate'    => 'required|date',
            'apptime'    => 'required',
            'status'     => 'required|in:Pending,Confirmed,Cancelled,Completed',
        ]);

        // Check conflict — ignore this appointment's own slot
        $conflict = Appointment::where('doctor_id', $data['doctor_id'])
            ->where('appdate', $data['appdate'])
            ->where('apptime', $data['apptime'])
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->where('id', '!=', $appointment->id)
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'That time slot is already taken for this doctor.',
            ], 422);
        }

        $appointment->update($data);
        $appointment->load(['doctor:id,name,specialization', 'patient:id,fname,lname,email,contact']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated successfully!',
            'data'    => $appointment,
        ]);
    }

    /** DELETE /api/appointments/{id} */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(['success' => true, 'message' => 'Appointment deleted successfully!']);
    }

    /** GET /api/stats — dashboard summary counts */
    public function stats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_doctors'       => \App\Models\Doctor::count(),
                'total_patients'      => \App\Models\Patient::count(),
                'total_appointments'  => Appointment::count(),
                'pending'             => Appointment::where('status', 'Pending')->count(),
                'confirmed'           => Appointment::where('status', 'Confirmed')->count(),
                'completed'           => Appointment::where('status', 'Completed')->count(),
                'cancelled'           => Appointment::where('status', 'Cancelled')->count(),
            ],
        ]);
    }
}
