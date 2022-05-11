<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Master\Province;

class ProvinceController extends Controller
{
    public function getProvinceById(Request $request)
    {
        try {
            $user_id = $request->user_id;
            if ($request[0] == 'all') {
                $model = Province::getAll();
            } else {
                $model = Province::where('province_id', $request[0])->get();
            }
            $data = [
                "result" => true,
                "data" => $model
            ];

            return json_encode($data);
        } catch (\Exception $e) {
            $message = 'Terjadi Error Server.';
            $data = [
                "result" => false,
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            return $data;
        }
    }
}
