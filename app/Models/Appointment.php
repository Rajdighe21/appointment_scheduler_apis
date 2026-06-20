<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['doctor_id', 'patient_id', 'appointment_date', 'slot_time', 'status'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
