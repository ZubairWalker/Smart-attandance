<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrController extends Controller
{
    const VALID_FROM = '08:00';
    const VALID_TO = '09:30';

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
