<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PnlProject extends Model
{

    protected $table = 'trl_project';
    protected $primaryKey = null;
    public $timestamps = false;
    const CREATED_AT = false;
    const UPDATED_AT = false;
    protected $guarded = [];

    protected $fillable = [
        'ket',
        'rate',
        'idx',
        'nilai',
        'no_so'
    ];

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


    public static function getPopulate()
    {
        $model = self::select('*');
        return $model;
    }

    public static function addData($request)
    {
        $model = DB::insert("insert into trl_project (ket, no_so,rate,nilai, idx) values ('" . $request['ket'] . "', '" . $request['no_so'] . "','" . $request['rate'] . "','" . $request['nilai'] . "', '" . $request['idx'] . "')");
        return $model;
    }
}
