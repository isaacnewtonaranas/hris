<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function sendResetLinkResponse(Request $request, $response)
    {
        appLog('Forgot_Password', findUser(request('email'))->id);

        return response()->json([
            'message' => 'Password reset email sent.'
        ]);
    }

    public function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json([
            'message' => trans($response),
            'errors' => ['email' => [trans($response)]]
        ], 422);
    }
}
