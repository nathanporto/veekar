<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $user = User::create([
            'name'         => $validated['name'],
            'company_name' => $validated['company_name'],
            'document'     => $validated['document'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
        ]);

        $user->subscription()->create(['status' => 'trial']);

        rescue(fn () => $this->email->sendWelcome($user->email, $user->name));

        $token = auth()->login($user);

        return response()->json(['token' => $token, 'user' => $user], 201);
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

        return response()->json(['token' => $token, 'user' => auth()->user()]);
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
