<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Events\BookingRejected;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::orderBy('created_at', 'desc')->get();
        return view('admin.room_booking', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Booking::with('room')->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json($booking);
    }

    public function approve($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['bookstatus' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Booking approved successfully',
            'booking' => $booking
        ]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $booking = Booking::findOrFail($id);
        $booking->update(['bookstatus' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Booking rejected successfully',
            'reason' => $request->reason,  
            'booking' => $booking
        ]);
    }

    public function updateStatus(Request $request, $bookId)
    {
        $booking = Booking::where('book_id', $bookId)->firstOrFail();
    
        $status = $request->input('status');
    
        if ($status === 'approved') {
            $booking->update(['bookstatus' => 'approved']);
            return response()->json(['message' => 'Booking approved successfully']);
        } elseif ($status === 'rejected') {
            $booking->update(['bookstatus' => 'rejected']);
            return response()->json([
                'message' => 'Booking rejected',
                'reason' => $request->input('reason')
            ]);
        }
    
        return response()->json(['message' => 'Invalid status'], 400);
    }

}
