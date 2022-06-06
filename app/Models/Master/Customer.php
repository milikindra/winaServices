<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{

    protected $table = 'mascustomer';
    protected $primaryKey = 'ID_CUST';
    public $incrementing = false;
    const CREATED_AT = 'TGLCREATE';
    const UPDATED_AT = 'TGLEDIT';

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

    public static function getById()
    {
        $model = self::select('*');
        return $model;
    }
}
