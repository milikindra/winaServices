<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{

    protected $table = 'massupplier';
    protected $primaryKey = 'id_supplier';
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

    public static function getPopulate()
    {
        $model = self::select('*');
        return $model;
    }
}
