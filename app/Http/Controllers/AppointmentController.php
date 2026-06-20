<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function getSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
        ]);

        $doctorId = $request->doctor_id;
        $date = $request->date;

        $startTime = Carbon::createFromTime(10, 0, 0);
        $endTime = Carbon::createFromTime(19, 0, 0); 
        $slotDuration = 60;

        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('status', 'booked')
            ->pluck('slot_time')
            ->map(function ($time) {
                return Carbon::parse($time)->format('H:i:s');
            })
            ->toArray();

        $slots = [];
        $current = $startTime->copy();

        while ($current < $endTime) {
            $slotTime = $current->format('H:i:s');
            $slots[] = [
                'time' => $current->format('h:i A'),
                'slot_time' => $slotTime,
                'is_available' => !in_array($slotTime, $bookedSlots),
            ];
            $current->addMinutes($slotDuration);
        }

        return response()->json([
            'doctor_id' => $doctorId,
            'date' => $date,
            'slots' => $slots,
        ]);
    }

    public function bookSlot(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|integer',
            'appointment_date' => 'required|date|after_or_equal:today',
            'slot_time' => 'required|date_format:H:i:s',
        ]);

        $time = Carbon::createFromFormat('H:i:s', $validated['slot_time']);
        $start = Carbon::createFromTime(10, 0, 0);
        $end = Carbon::createFromTime(19, 0, 0);

        if ($time->lt($start) || $time->gte($end)) {
            return response()->json(['message' => 'Slot time must be between 10:00 AM and 7:00 PM'], 422);
        }

        $existing = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('slot_time', $validated['slot_time'])
            ->where('status', 'booked')
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return response()->json(['message' => 'This slot is already booked'], 409);
        }

        try {
            $appointment = Appointment::create([
                'doctor_id' => $validated['doctor_id'],
                'patient_id' => $validated['patient_id'],
                'appointment_date' => $validated['appointment_date'],
                'slot_time' => $validated['slot_time'],
                'status' => 'booked',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'This slot is already booked'], 409);
        }

        return response()->json(['message' => 'Slot booked successfully', 'data' => $appointment], 201);
    }

   
    public function cancelSlot($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = 'cancelled';
        $appointment->save();

        return response()->json(['message' => 'Appointment cancelled, slot freed']);
    }
}
