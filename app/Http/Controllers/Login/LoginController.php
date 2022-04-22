<?php

namespace App\Http\Controllers\Login;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Functions;
use App\User;
use App\Models\UserAccess;
use App\Models\Master\Member;
use App\Models\Master\Source;
use App\Models\DefaultAccount;
use App\Models\ResetPassword;
use Validator;
use Auth;
use DB;
use Log;
use Illuminate\Support\Facades\Hash;



class LoginController extends BaseController
{
    public function login(Request $request)
    {
        dd($request->input());
        $this->validate($request, [
            'password' => 'required',
            'email' => 'required'
        ]);
        $password = $request->input('password');
        $email = $request->input('email');
        $user = User::where('username', $email)->first();
        if (!isset($user)) {
            $message = 'User tidak ditemukan';
            $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
            return response()->json([
                'result' => FALSE,
                'message' => $message
            ]);
        }
        if ($user->is_locked == 1) {
            $message = 'Akun belum aktif';
            $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
            return response()->json([
                'result' => FALSE,
                'message' => $message
            ]);
        }
        if ($user->status == 0) {
            $message = 'Akun belum aktif';
            $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
            return response()->json([
                'result' => FALSE,
                'message' => $message
            ]);
        }
        $member = Member::find($user->user_id);
        if ($member->status == 3) {
            $message = 'Akun telah tereliminasi';
            $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
            return response()->json([
                'result' => FALSE,
                'message' => $message
            ]);
        }
        $checkPassword = Hash::check($password, $user->password);
        if ($checkPassword) {
            if (!isset($user->api_token)) {
                $user->api_token = User::randomString(24);
                $user->save();
            }

            $message = "User '$email' successfully login";
            $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
            return response()->json([
                'result' => TRUE,
                'message' => $message,
                // 'data' => User::where('email', $email)->first()
                'data' => User::getLogin($email),
                'source' => Source::getByUser($user->user_id),
                'default_settings' => DefaultAccount::select('default_id', 'default_name', 'value')->get()
                // 'default_settings' => DefaultAccount::get()
            ]);
        } else {
            $message = 'Password yang anda masukkan salah';
            $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
            return response()->json([
                'result' => FALSE,
                'message' => $message
            ]);
        }
    }

    public function forgetPassword(Request $request)
    {
        try {
            DB::beginTransaction();

            $username = $request->input('username');

            $user = User::where('username', $username)->first();
            if (!isset($user)) {
                $message = 'User tidak ditemukan';
                $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
                return response()->json([
                    'result' => FALSE,
                    'message' => $message
                ]);
            }
            if ($user->is_locked == 1) {
                $message = 'Akun telah dikunci, silahkan meghubungi customer service';
                $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
                return response()->json([
                    'result' => FALSE,
                    'message' => $message
                ]);
            }
            if ($user->status == 0) {
                $message = 'Akun belum aktif';
                $this->logActivity($request->path(), $message, print_r($_POST, TRUE));
                return response()->json([
                    'result' => FALSE,
                    'message' => $message
                ]);
            }

            $member = Member::getByID($user->user_id);
            $email = $member->email;
            // $pepipost_api_key = env('PEPIPOST_API_KEY');
            $pepipost_api_key = '';

            $check = ResetPassword::checkRequest($member->member_id);
            if (count($check) > 0) {
                return response()->json([
                    'result' => false,
                    'message' => 'Sudah ada pengajuan.'
                ]);
            }
            $reset = ResetPassword::addData($username, $member->member_id, $email);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.pepipost.com/v5.1/template?template_id=29105",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "api_key: $pepipost_api_key"
                ),
            ));

            $responseTemplate = curl_exec($curl);
            $errTemplate = curl_error($curl);

            curl_close($curl);

            if ($errTemplate) {
                $data = [
                    'result' => true,
                    'message' => 'Error loading mail template'
                ];

                return response()->json($data);
            } else {
                $pBody = json_decode($responseTemplate);

                $from = [
                    "email" => "info-noreply@infocjdw.com",
                    "name" => "CJDW"
                ];
                $content = [
                    'type' => "html",
                    "value" => $pBody->data->content[0]->value
                ];
                $attributes = [
                    "USERNAME" => $username,
                    "TOKEN" => $reset->token
                ];
                $to = [
                    "email" => $email
                ];
                $personalizations = [
                    'attributes' => $attributes,
                    'to' => array($to)
                ];
                $pepiJson = array(
                    "from" => $from,
                    'subject' => "Form Reset Password",
                    'content' => array($content),
                    "personalizations" => array($personalizations)
                );
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.pepipost.com/v5.1/mail/send",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => json_encode($pepiJson),
                    CURLOPT_HTTPHEADER => array(
                        "api_key: $pepipost_api_key",
                        "content-type: application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);
            }

            DB::commit();

            return response()->json([
                'result' => true,
                'message' => 'Berhasil kirim link reset password.'
            ]);
        } catch (\Exception $e) {
            \Log::debug($e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine());

            $data = [
                'result' => false,
                'message' => 'Gagal.'
            ];

            return response()->json($data);
        }
    }

    public function newPassword(Request $request)
    {
        $token = $request->input('token');

        $reset = ResetPassword::checkByToken($token);
        if (isset($reset)) {
            return response()->json([
                'result' => false,
                'message' => 'Link tidak valid.'
            ]);
        }

        $model = ResetPassword::getByToken($token);

        return response()->json([
            'result' => true,
            'user_id' => $model->member_id
        ]);
    }

    public function newPasswordSave(Request $request)
    {
        $user_id = $request->input('user_id');
        $password = $request->input('password');
        $token = $request->input('token');

        $new = User::updatePassword($user_id, $password);
        $reset = ResetPassword::updateData($token, $user_id);

        if (!$new) {
            return resopnse()->json([
                'result' => false,
                'message' => 'Gagal reset password.'
            ]);
        }

        return response()->json([
            'result' => true,
            'message' => 'Sukses reset password.'
        ]);
    }
}
