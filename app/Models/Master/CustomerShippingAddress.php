<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CustomerShippingAddress extends Model
{

    protected $table = 'wina_m_other_address';
    protected $primaryKey = 'internal_id';
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

    public static function getAll($field, $sort)
    {
        $model = self::orderby($field, $sort)->get();
        return $model;
    }

    public static function addData($request)
    {
        $model = new self();
        $model->customer_id = $request['customer_id'];
        $model->address_alias = $request['address_alias'];
        $model->other_address = $request['other_address'];
        $model->tax_number = $request['tax_number'];
        $model->user_modified = $request['user_modified'];
        $model->save();
        return $model;
    }

    public static function deleteData($request)
    {
        $model = self::where('customer_id', $request)->delete();
        return $model;
    }
}
