<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Handle the tenant SSO login request from the Cloud Platform.
     * Validates HMAC signature using CLIENT_API_KEY and logs in a single admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $payload = $request->query('payload');
        $signature = $request->query('signature');

        if (!$payload || !$signature) {
            return response()->view('login', [
                'title' => 'Invalid SSO Request',
                'message' => 'The SSO payload or signature is missing. Please start the login process again from Barnomala Platform Dashboard or School Settings.',
            ], 400);
        }

        // 1. Verify Signature
        $secret = env('CLIENT_API_KEY');

        if (!$secret) {
            Log::warning('SSO Error: CLIENT_API_KEY not configured. Running optimize:clear and retrying once.');
            Artisan::call('optimize:clear');

            // Reload env after clearing cached config so newly rotated secrets can be picked up.
            $secret = env('CLIENT_API_KEY');
        }

        if (!$secret) {
            Log::error('SSO Error: CLIENT_API_KEY is not configured after retry.');
            return response()->view('login', [
                'title' => 'SSO Configuration Missing',
                'message' => 'The CLIENT_API_KEY is not configured. Please contact support.',
            ], 500);
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('SSO Error: Invalid signature detected.');
            abort(403, 'Invalid signature');
        }

        // 2. Decode Payload
        $data = json_decode(base64_decode($payload), true);

        if (!$data || !isset($data['expires_at'])) {
            abort(400, 'Malformed payload');
        }

        // 3. Validate Expiration
        if ($data['expires_at'] < now()->timestamp) {
            abort(403, 'SSO Token Expired');
        }

        // 4. Always login the single admin account
        $name = $data['name'] ?? 'Admin';
        $email = $data['email'] ?? env('ADMIN_EMAIL', 'admin@barnomala.com');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(str()->random(16)), // Random password since login is via SSO
                'is_admin' => true,
            ]);
        }

        // 5. Establish Session
        Auth::login($user);
        $request->session()->regenerate();

        return view('sso-success');
    }
}
