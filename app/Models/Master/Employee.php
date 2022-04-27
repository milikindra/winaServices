<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{

    protected $table = 'wina_m_user';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    const CREATED_AT = 'dt_record';
    const UPDATED_AT = 'dt_modified';

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'user_record');
    }

    public static function getAll()
    {
        $model = self::get();
        return $model;
    }

    public static function getPopulateEmployee($status)
    {
        $model = self::select('wina_m_user.*', 'wina_m_global_param.name as religion')
            ->leftJoin('wina_m_global_param', 'wina_m_global_param.code', 'wina_m_user.religion_id')
            ->where('wina_m_user.status', $status);
        return $model;
    }

    public static function addData($request)
    {
        $model = new self();
        $model->username = $request->input('username');
        $model->email = $request->input('email');
        $model->password = Hash::make($request->input('password'));
        $model->employee_id = $request->input('employee_id');
        $model->full_name = $request->input('full_name');
        $model->pob = $request->input('pob');
        $model->dob = $request->input('dob');
        $model->nationality = $request->input('nationality');
        $model->national_id = $request->input('national_id');
        $model->province = $request->input('province');
        $model->city = $request->input('city');
        $model->district = $request->input('district');
        $model->village = $request->input('village');
        $model->address = $request->input('address');
        $model->postal_code = $request->input('postal_code');
        $model->phone = $request->input('phone');
        $model->marital_status = $request->input('marital_status');
        $model->ptkp_type = $request->input('ptkp_type');
        $model->tax_id = $request->input('tax_id');
        $model->join_date = $request->input('join_date');
        $model->user_modified = $request->input('user_modified');
        $model->user_image = ($request->input('user_image') != null) ? $request->input('user_image') . $request->input('employee_id') . ".png" : null;
        $model->save();
        return $model;
    }
}
