<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Employee;
use App\Models\Module;
use App\Models\ModuleFunction;
use App\Models\UserGroup;

class EmployeeController extends Controller
{
    public function getMenuList(Request $request)
    {
        $menu = array();
        $userid = $request->get("user_id");
        $module = DB::select("call wina_sp_get_module_user ('$userid')");
        $module = collect($module)->keyBy('module_id')->toArray();
        $moduleParent = DB::select("call wina_sp_get_module_user_parent ('$userid')");

        // $max = 9999;
        $level = 0;
        $list = [];
        $menu = [];
        foreach ($moduleParent as $node) {
            $tree = new ModuleNode($node->module_id, $module, $level);
            $tree->addChildren($module, $node->module_id, $list);
            array_push($menu, $tree);
        }


        return json_encode($menu);
    }

    public function getList(Request $request)
    {
        $model = new Employee();
        $fields = $model->getTableColumns();

        $employee = Employee::getPopulateEmployee($request->input('void'));
        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $employee->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('wina_m_user.employee_id', 'LIKE', "%$keyword%")
                        ->orWhere('wina_m_user.full_name', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $employee->get();
        $totalRows = $employee->count();

        if ($request->has('sort')) {
            if (!is_array($request->input('sort'))) {
                $message = "Invalid array for parameter sort";
                $data = [
                    'result' => FALSE,
                    'message' => $message
                ];
                Log::debug($request->path() . " | " . $message . " | " . print_r($_POST, TRUE));
                return response()->json($data);
            }

            foreach ($request->input('sort') as $key => $sort) {
                $column = $sort['column'];
                $direction = $sort['dir'];
                $employee->orderBy($column, $direction);
            }
        } else {
            $employee->orderBy('employee_id', 'asc');
        }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $employee->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($offset < 0) {
                $offset = 0;
            }

            $employee->skip($offset)->take($limit);
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'employee' => $employee->get()
        ];

        return response()->json($data);
    }

    public function EmployeeAddSave(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Employee::addData($request);
            DB::commit();
            $message = 'Succesfully save data.';
            $data = [
                "result" => true,
                'message' => $message,
                "data" => $model
            ];
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Terjadi Error Server.';
            $data = [
                "result" => false,
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            return $data;
        }
    }

    public function getEmployeeById(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $model = DB::select("call wina_sp_get_user ('$user_id')");
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

    public function employeeGetRawData(Request $request)
    {
        $model = employee::getAll();
        return response()->json($model);
    }

    public function employeeGroupMatrix(Request $request)
    {
        $menu = array();
        $UA = array();
        $module = Module::whereNull('parent_id')->get();
        $j = 0;
        foreach ($module as $data) {
            $i = 0;
            $childModule = Module::where('parent_id', $data->module_id)->get();
            foreach ($childModule as $row) {
                $function = ModuleFunction::where('module_id', $row->module_id)->get();
                $l = 0;
                if (count($function) < 1) {
                    $function = NULL;
                }
                $menu[$data->module_name][$i] = array(
                    "child" => $row,
                    "function" => $function
                );
                $i++;
            }
            $j++;
        }
        $userAccess = array();
        $userAccessModel = UserGroup::where('description', $request->input('userGroup'))
            ->pluck('module_function_id')->toArray();
        if (count($userAccessModel) > 0) {
            $userAccess = $userAccessModel;
        }
        Log::debug($userAccessModel);
        $data = [
            'menu' => $menu,
            // 'userAccess' => $userAccessModel,
            'userAccess' => $userAccess,
        ];
        return response()->json($data);
    }

    private function traverseModule($module, $path)
    {
        $modulSkrg = Module::where('parent_id', $module)->orderBy('module_sequence', 'asc')->where('is_visible', '<>', '0')->get();;
        if (count($modulSkrg) == 0) {
            $functionModule = ModuleFunction::join("wina_m_function", "wina_m_module_function.function_id", "wina_m_function.function_id")
                ->where('module_id', $module)
                ->get();
            $lastlevel = array();
            if (count($functionModule) > 0) {
                foreach ($functionModule as $functione) {
                    $tmpFunctionz = array(
                        "checked" => false,
                        "children" => array(),
                        "id" => $functione->module_function_id,
                        "text" => $functione->function_name,
                        "path" => $path . "." . $functione->function_name
                    );
                    array_push($lastlevel, $tmpFunctionz);
                }
            }
            return $lastlevel;
        }
        $tmpTree = array();
        foreach ($modulSkrg as $m) {
            $template = array(
                "checked" => false,
                "children" => self::traverseModule($m->module_id, $path . "." . $m->module_name),
                "id" => "-" . $m->module_id,
                "text" => $m->module_name,
                "path" => $path . "." . $m->module_name
            );
            array_push($tmpTree, $template);
        }
        return $tmpTree;
    }

    public function getEmployeeMatrixList()
    {
        $menu = array();
        $UA = array();
        $module = Module::whereNull('parent_id')->orderBy('module_sequence', 'asc')->where('is_visible', '1')->get();;
        foreach ($module as $parent) {
            $template = array(
                "checked" => false,
                "children" => self::traverseModule($parent->module_id, $parent->module_name),
                "id" => "-" . $parent->module_id,
                "text" => $parent->module_name,
                "path" => $parent->module_name
            );
            array_push($menu, $template);
        }
        return $menu;
    }
}
