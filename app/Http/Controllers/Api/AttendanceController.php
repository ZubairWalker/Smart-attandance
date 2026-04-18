<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\QrToken;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the QR token.
        $qr = QrToken::where('token', $request->token)
            ->where('date', today())
            ->where('is_active', true)
            ->first();

        if (!$qr) {
            return response()->json(['error' => 'invalid or expired QR'], 422);
        }

        // Check time window.
        $now = now()->format('H:i');
        if ($now < $qr->valid_from || $now > $qr->valid_to) {
            return response()->json(['error' => 'Outside check-in window'], 422);
        }

        // 3. Block duplicate check-in
        $exists = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Already checked in today'], 422);
        }

        // 4. Write to DB
        $attendance = Attendance::create([
            'user_id'  => $user->id,
            'date'     => today(),
            'check_in' => now(), // Store the full timestamp
            'status'   => 'present',
        ]);

        return response()->json(['message' => 'Check-in successful', 'data' => $attendance]);
    }

    public function checkOut(Request $request)
    {
        $user = auth('api')->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return response()->json(['error' => 'No active check-in found for today'], 422);
        }

        $attendance->update([
            'check_out' => now(),
        ]);

        return response()->json(['message' => 'Check-out successful', 'data' => $attendance]);
    }

    public function monthlyReport(Request $request)
    {
        $user = auth('api')->user();
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        return response()->json(['data' => $attendances]);
    }
}
