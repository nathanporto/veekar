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
            'name'           => ['required', 'string', 'max:255'],
            'company_name'   => ['required', 'string', 'max:255'],
            'document'       => ['required', 'string', 'max:18', 'unique:users,document'],
            'email'          => ['required', 'email', 'unique:users,email'],
            'password'       => ['required', 'confirmed', Password::min(8)],
            'accepted_terms' => ['required', 'accepted'],
        ]);

        $verificationToken = Str::random(64);

        $user = User::create([
            'name'                     => $validated['name'],
            'company_name'             => $validated['company_name'],
            'document'                 => $validated['document'],
            'email'                    => $validated['email'],
            'password'                 => Hash::make($validated['password']),
            'accepted_terms_at'        => now(),
            'terms_version'            => config('terms.version'),
            'email_verification_token' => hash('sha256', $verificationToken),
        ]);

        $user->subscription()->create(['status' => 'trial']);

        rescue(fn () => $this->email->sendEmailVerification($user->email, $user->name, $verificationToken));

        return response()->json(['message' => 'Verifique seu e-mail para ativar a conta.'], 201);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! $user->email_verification_token || $user->email_verification_token !== hash('sha256', $validated['token'])) {
            return response()->json(['message' => 'Link inválido ou expirado.'], 422);
        }

        if (! $user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);

            rescue(fn () => $this->email->sendWelcome($user->email, $user->name));
        }

        $token = auth()->login($user);

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        // Sempre retorna sucesso para não expor se o e-mail existe
        if (! $user || $user->email_verified_at) {
            return response()->json(['message' => 'Se este e-mail estiver pendente de confirmação, reenviamos o link.']);
        }

        $verificationToken = Str::random(64);

        $user->update(['email_verification_token' => hash('sha256', $verificationToken)]);

        rescue(fn () => $this->email->sendEmailVerification($user->email, $user->name, $verificationToken));

        return response()->json(['message' => 'Se este e-mail estiver pendente de confirmação, reenviamos o link.']);
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

        if (! auth()->user()->email_verified_at) {
            auth()->logout();

            return response()->json([
                'message' => 'Confirme seu e-mail antes de entrar. Verifique sua caixa de entrada.',
                'code'    => 'email_verification_required',
            ], 403);
        }

        return response()->json(['token' => $token, 'user' => auth()->user()]);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            ...$user->toArray(),
            'terms_reacceptance_required' => $user->terms_version !== config('terms.version'),
        ]);
    }

    public function acceptTerms(): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'accepted_terms_at' => now(),
            'terms_version'     => config('terms.version'),
        ]);

        return response()->json([
            ...$user->toArray(),
            'terms_reacceptance_required' => false,
        ]);
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
