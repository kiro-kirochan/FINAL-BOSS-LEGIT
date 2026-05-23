<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    // Uses "patreg" table — matching the original reference project naming
    protected $table = 'patreg';

    protected $fillable = [
        'fname',
        'lname',
        'email',
        'password',
        'contact',
        'gender',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // A patient HAS MANY appointments
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }
}
