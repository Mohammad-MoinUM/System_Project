<?php

namespace App\Http\Controllers;

use App\Events\ProviderLocationUpdated;
use App\Models\Booking;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function updateLocation(Request $request, $bookingId)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $booking = Booking::findOrFail($bookingId);

        $booking->update([
            'provider_latitude'  => $request->latitude,
            'provider_longitude' => $request->longitude,
        ]);

        broadcast(new ProviderLocationUpdated(
            bookingId:  $bookingId,
            providerId: $booking->provider_id,
            latitude:   $request->latitude,
            longitude:  $request->longitude,
        ));

        return response()->json(['status' => 'ok']);
    }

    public function getLocation($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);

        return response()->json([
            'latitude'  => $booking->provider_latitude,
            'longitude' => $booking->provider_longitude,
        ]);
    }
}