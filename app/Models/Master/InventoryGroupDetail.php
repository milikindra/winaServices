<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InventoryGroupDetail extends Model
{

    protected $table = 'grouping_det';
    protected $primaryKey = 'IDXURUT';
    public $incrementing = false;
    const CREATED_AT = 'URUT';
    const UPDATED_AT = false;

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

    public static function addData($request)
    {
        $model = DB::insert('insert into grouping_det (NO_BUKTI, NO_STOCK,NM_STOCK,QTY,SAT  ) values (?, ?,?,?,?)', [$request['no_stock'], $request['no_stockGroup'], $request['nm_stockGroup'], $request['qtyGroup'], $request['satGroup']]);
        return $model;
    }

    public static function deleteData($request)
    {
        $model = self::where('NO_BUKTI', $request)->delete();
        return $model;
    }
}
