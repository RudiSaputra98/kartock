<?php

namespace App\Http\Controllers;

use App\Models\Log;

use App\Models\User;
use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function loginView()
    {
        if (Auth::check()) {
            return back();
        }

        // Membuat dua angka acak untuk captcha
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);

        // Simpan jawaban captcha dalam session
        $captchaAnswer = $num1 + $num2;
        session(['captcha_answer' => $captchaAnswer]);

        // Kirimkan angka captcha ke view
        return view('pages.auth.login', compact('num1', 'num2'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user(); // Ambil data user yang sedang login

        // Menambahkan log aktivitas logout
        Log::create([
            'user_id' => $user->id,
            'activity_type' => 'logout',
            'description' => 'User berhasil logout',
            'data' => json_encode(['email' => $user->email])
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return back();
        }

        // Validasi input email, password, dan captcha
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'captcha' => ['required', 'numeric'], // Validasi captcha
        ]);

        // Periksa apakah jawaban captcha sesuai
        $captchaAnswer = $request->input('captcha');
        $correctAnswer = $request->session()->get('captcha_answer');

        if ($captchaAnswer != $correctAnswer) {
            return back()->withErrors([
                'captcha' => 'Hasil itungan dak bener., macem mane k neh',
            ]);
        }

        // Cari pengguna berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // Verifikasi apakah pengguna ditemukan dan password cocok
        if (!$user || $user->password !== $credentials['password']) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        // Jika password cocok, login pengguna
        Auth::login($user);
        $request->session()->regenerate(); // Regenerasi session untuk keamanan

        Log::create([
            'user_id' => $user->id,
            'activity_type' => 'login',
            'description' => 'User berhasil login',
            'data' => json_encode(['email' => $user->email])
        ]);

        // Redirect ke halaman yang diinginkan setelah login
        return redirect()->intended('/');
    }
}
