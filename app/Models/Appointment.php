<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointmenttb';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appdate',
        'apptime',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'appdate' => 'date:Y-m-d',
        ];
    }

    // An appointment BELONGS TO one patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    // An appointment BELONGS TO one doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
