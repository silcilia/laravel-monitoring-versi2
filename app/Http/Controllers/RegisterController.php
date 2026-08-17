<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Menampilkan halaman registrasi
     * 
     * @return \Illuminate\View\View
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Memproses registrasi user baru
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);

            Log::info("✅ Registrasi berhasil: {$user->username} - Email: {$user->email}");

            return redirect()->route('login')
                ->with('success', 'Registrasi berhasil! Silahkan login dengan akun Anda.');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("❌ Registrasi error (Database): " . $e->getMessage());

            $errorMessage = 'Terjadi kesalahan pada database. ';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $errorMessage = 'Username atau email sudah terdaftar. Silakan gunakan yang lain.';
            }

            return back()
                ->withErrors(['error' => $errorMessage])
                ->withInput($request->except('password', 'password_confirmation'));

        } catch (\Exception $e) {
            Log::error("❌ Registrasi error: " . $e->getMessage());

            return back()
                ->withErrors(['error' => 'Terjadi kesalahan saat registrasi: ' . $e->getMessage()])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }
}