<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\GlobalParam;

class GlobalParamController extends Controller
{
    public function getGlobalParam(request $request)
    {
        $model = GlobalParam::getPopulate();
        if ($request->category == 'top_management') {
            $model->leftJoin('wina_m_user', 'wina_m_global_param.value', 'wina_m_user.user_id');
            $model->where('wina_m_global_param.category', $request->category);
        } else if ($request->category != 'all') {
            $model->where('wina_m_global_param.category', $request->category);
        }

        if ($request->id != 'all') {
            $model->where('wina_m_global_param.code', $request->id);
        }
        $data = $model->get();
        return response()->json($data);
    }
}
