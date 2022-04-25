<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;



class LoginController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'username' => 'required'
        ]);
        $password = $request->input('password');
        $username = $request->input('username');
        $user = User::where('username', $username)->first();
        if (!isset($user)) {
            $message = 'User tidak ditemukan';
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($_POST, TRUE));
            return response()->json([
                'result' => FALSE,
                'message' => $message
            ]);
        } else {
            if ($user->status == 1) {
                $message = 'Akun belum aktif';
                Log::debug($request->path() . " | "  . $message .  " | " . print_r($_POST, TRUE));
                return response()->json([
                    'result' => FALSE,
                    'message' => $message
                ]);
            } else {

                $checkPassword = Hash::check($password, $user->password);
                if ($checkPassword) {
                    if (!isset($user->api_token)) {
                        $user->api_token = User::randomString(24);
                        $user->save();
                    }

                    $message = "User '$username' successfully login";
                    Log::debug($request->path() . " | "  . $message .  " | " . print_r($_POST, TRUE));
                    return response()->json([
                        'result' => TRUE,
                        'message' => $message,
                        'data' => User::getLogin($username),
                    ]);
                } else {
                    $message = 'Password yang anda masukkan salah';
                    Log::debug($request->path() . " | "  . $message .  " | " . print_r($_POST, TRUE));
                    return response()->json([
                        'result' => FALSE,
                        'message' => $message
                    ]);
                }
            }
        }
    }
}
