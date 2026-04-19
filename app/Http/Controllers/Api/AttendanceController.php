<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\QrToken;
use Illuminate\Http\Request;

use OpenApi\Attributes as OA;

class AttendanceController extends Controller
{
    #[OA\Post(
        path: "/api/attendance/check-in",
        summary: "User Check-in",
        description: "Record a user's check-in using a valid QR token.",
        tags: ["Attendance"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["token"],
                properties: [
                    new OA\Property(property: "token", type: "string", example: "ABC123XYZ789")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Check-in successful"
            ),
            new OA\Response(
                response: 422,
                description: "Validation Error or Business Logic Violation"
            )
        ]
    )]
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

    #[OA\Post(
        path: "/api/attendance/check-out",
        summary: "User Check-out",
        description: "Record a user's check-out for the current day.",
        tags: ["Attendance"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Check-out successful"
            ),
            new OA\Response(
                response: 422,
                description: "No active check-in found"
            )
        ]
    )]
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

    #[OA\Get(
        path: "/api/attendance/month",
        summary: "Monthly Attendance Report",
        description: "Get the current user's attendance records for a specific month.",
        tags: ["Attendance"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "month",
                in: "query",
                description: "Month (1-12)",
                required: false,
                schema: new OA\Schema(type: "integer", example: 4)
            ),
            new OA\Parameter(
                name: "year",
                in: "query",
                description: "Year",
                required: false,
                schema: new OA\Schema(type: "integer", example: 2026)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of attendance records",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            )
        ]
    )]
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
