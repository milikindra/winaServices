<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;
use App\Models\Master\Customer;
use App\Models\Master\CustomerShippingAddress;

class CustomerController extends Controller
{
    public function customerGetRawData(Request $request)
    {
        $model = Customer::getAll($request->field, $request->sort);
        return response()->json($model);
    }

    public function customerGetById(Request $request)
    {
        $model = Customer::select('mascustomer.*', 'rate_tmp.rate', 'wina_m_other_address.address_alias', 'wina_m_other_address.tax_number', 'wina_m_other_address.other_address');
        $model->leftJoin(DB::RAW('(SELECT DISTINCT curr, rate
		FROM masrate ORDER BY tanggal DESC) as rate_tmp'), 'mascustomer.curr', 'rate_tmp.curr');
        $model->leftJoin('wina_m_other_address', 'mascustomer.ID_CUST', 'wina_m_other_address.customer_id');
        $model->where('mascustomer.ID_CUST', $request->id_cust);
        $model->count(); //for reset the query
        $data = $model->get();
        return $data;
    }
    public function customerGetForSi(Request $request)
    {
        $model = Customer::getPopulate();
        $model->leftJoin('jual_head', 'jual_head.ID_CUST', 'mascustomer.ID_CUST');
        $model->where('mascustomer.ID_CUST', $request->id_cust);
        return $model->get();
    }

    public function getList(Request $request)
    {
        $model = new Customer();
        $fields = $model->getTableColumns();
        $void = $request->input('void');

        $customer = Customer::getPopulate();

        if ($request->has('search')) {
            $keyword = $request->input('search');
            if (!empty($keyword)) {
                $customer->where(function ($query) use ($keyword, $fields) {
                    $query->orWhere('ID_CUST', 'LIKE', "%$keyword%")
                        ->orWhere('NM_CUST', 'LIKE', "%$keyword%")
                        ->orWhere('ALAMAT1', 'LIKE', "%$keyword%");
                });
            }
        }

        $filteredData = $customer->get();
        $totalRows = $customer->count();

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
                $customer->orderBy($column, $direction);
            }
        } else {
            $customer->orderBy('NM_CUST', 'asc');
        }

        if ($request->has('current_page')) {
            $page = $request->input('current_page');
            $limit = $customer->count();
            if ($request->has('per_page')) {
                $limit = $request->input('per_page');
            }
            $offset = ($page - 1) * $limit;
            if ($limit > 0) {
                $customer->skip($offset)->take($limit);
            }
        }

        $data = [
            'result' => true,
            'total' => $totalRows,
            'per_page' => $request->has('per_page') ? $request->input('current_page') : 0,
            'recordsFiltered' => count($filteredData),
            'current_page' => $request->has('current_page') ? $request->input('current_page') : 0,
            'customer' => $customer->get()
        ];

        return response()->json($data);
    }

    public function customerAddSave(Request $request)
    {
        DB::beginTransaction();
        try {
            // insert customer
            $model = Customer::addData($request->customer);
            // insert branch
            for ($i = 0; $i < count($request->branch); $i++) {
                $model = CustomerShippingAddress::addData($request->branch[$i]);
            }
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

    public function customerEdit(request $request)
    {
        try {
            $model = Customer::find($request->ID_CUST);
            $model->get();
            $child = CustomerShippingAddress::where('customer_id', $request->ID_CUST)->get();
            $data = [
                "result" => true,
                'customer' => $model,
                'branch' => $child
            ];
            return $data;
        } catch (\Exception $e) {
            $message = 'Terjadi Error Server.';
            $data = [
                "result" => false,
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            return $data;
        }
    }

    public function customerUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Customer::updateData($request->customer);
            $model = CustomerShippingAddress::deleteData($request->input('customer')['ID_CUST_OLD']);
            // insert branch
            for ($i = 0; $i < count($request->branch); $i++) {
                $model = CustomerShippingAddress::addData($request->branch[$i]);
            }

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

    public function customerBranchGetById(request $request)
    {
        $model = CustomerShippingAddress::select('*');
        $model->where('customer_id', $request->id_cust);
        return $model->get();
    }

    public function customerAddBranch(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = CustomerShippingAddress::addData($request->branch);
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

    public function customerDelete(Request $request)
    {
        try {
            Customer::where('ID_CUST', $request->ID_CUST)->delete();
            CustomerShippingAddress::where('customer_id', $request->ID_CUST)->delete();
            DB::commit();
            $message = 'Succesfully delete data.';
            $data = [
                "result" => true,
                'message' => $message,
            ];
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Something wrong! Cannot delete data.';
            $data = [
                "result" => false,
                'message' => $message
            ];
            Log::debug($request->path() . " | "  . $message .  " | " . print_r($request->input(), TRUE));
            Log::debug($e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
            return $data;
        }
    }
}
