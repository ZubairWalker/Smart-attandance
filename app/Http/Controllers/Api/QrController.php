<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use OpenApi\Attributes as OA;

class QrController extends Controller
{
    const VALID_FROM = '08:00';
    const VALID_TO = '09:30';

    #[OA\Post(
        path: "/api/qr/generate",
        summary: "Generate QR Token",
        description: "Generate a new QR token for a specific office. Required Admin role.",
        tags: ["QR Code"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["office_id"],
                properties: [
                    new OA\Property(property: "office_id", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "QR token generated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "qr_token", type: "string", example: "ABC123XYZ789")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Forbidden - Admin only"
            )
        ]
    )]
    public function generate(Request $request) 
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id',
        ]);

        $token = Str::random(12);

        // Deactivate existing tokens for this specific office today
        QrToken::where('office_id', $request->office_id)
            ->where('date', today())
            ->update(['is_active' => false]);

        $qr = QrToken::create([
            'token' => $token,
            'office_id' => $request->office_id,
            'date' => today(),
            'valid_from' => self::VALID_FROM,
            'valid_to' => self::VALID_TO,
            'is_active' => true,
        ]);

        return response()->json(['qr_token' => $qr->token]);
    }
}
