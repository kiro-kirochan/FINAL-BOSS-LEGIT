<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    // Tell Laravel this model uses the "doctb" table (matches the original project)
    protected $table = 'doctb';

    protected $fillable = [
        'name',
        'email',
        'password',
        'specialization',
        'docFees',
    ];

    // Never expose password in JSON API responses
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'docFees'  => 'decimal:2',
            'password' => 'hashed',
        ];
    }

    // A doctor HAS MANY appointments
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
}
