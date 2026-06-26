<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(private EmailService $email) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'document'     => ['required', 'string', 'max:18', 'unique:users,document'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        $verificationToken = Str::random(64);

        $user = User::create([
            'name'                      => $validated['name'],
            'company_name'              => $validated['company_name'],
            'document'                  => $validated['document'],
            'email'                     => $validated['email'],
            'password'                  => Hash::make($validated['password']),
            'email_verification_token'  => $verificationToken,
        ]);

        $user->subscription()->create(['status' => 'trial']);

        rescue(fn () => $this->email->sendEmailVerification($user->email, $user->name, $verificationToken));

        return response()->json(['message' => 'Conta criada! Verifique seu e-mail para ativar o acesso.'], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $user = auth()->user();

        if (! $user->email_verified_at) {
            auth()->logout();
            return response()->json([
                'message' => 'E-mail não verificado. Verifique sua caixa de entrada e clique no link de confirmação.',
                'code'    => 'email_not_verified',
            ], 403);
        }

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)
            ->where('email_verification_token', $request->token)
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Link inválido ou expirado.'], 422);
        }

        if ($user->email_verified_at) {
            $token = auth()->login($user);
            return response()->json(['token' => $token, 'user' => $user]);
        }

        $user->update([
            'email_verified_at'        => now(),
            'email_verification_token' => null,
        ]);

        rescue(fn () => $this->email->sendWelcome($user->email, $user->name));

        $token = auth()->login($user);

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
