<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // Validate and process the booking data
        $validated = $request->validate([
            'room_id'    => 'required|string',
            'booktitle'  => 'required|string|max:255',
            'username'   => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'booktel'    => 'required|string|max:20',
            'bookedate'  => 'required|date',
            'booktime'   => 'required|string',
            'bookdetail' => 'nullable|string',
        ]);

        $booking = Booking::create($validated);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $booking,
        ], 201);
    }
}
