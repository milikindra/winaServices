<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'email' => 'required'
        ]);
        $password = md5(md5($request->input('password')));
        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        if (!isset($user)) {
            $message = 'User with email :' . $email . ' Not Found';
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($_POST, TRUE));
            return response()->json([
                'result' => FALSE,
                'message' => $message
            ]);
        } else {
            if ($user->status == 0) {
                $message = 'Account with username : ' . $user->username . ' is Not Active';
                Log::debug($request->path() . " | "  . $message .  " | " . print_r($_POST, TRUE));
                return response()->json([
                    'result' => FALSE,
                    'message' => $message
                ]);
            } else {
                // $checkPassword = Hash::check($password, $user->password);
                // if ($checkPassword) {
                if ($password == $user->password) {
                    if (!isset($user->api_token)) {
                        $user->api_token = User::randomString(24);
                        $user->save();
                    }

                    $message = "User with username : '$user->username' and email : '$email' successfully login";
                    Log::debug($request->path() . " | "  . $message);
                    return response()->json([
                        'result' => TRUE,
                        'message' => $message,
                        'data' => User::getLogin($email),
                    ]);
                } else {
                    $message = 'Password Wrong';
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
