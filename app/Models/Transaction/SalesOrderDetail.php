<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesOrderDetail extends Model
{

    protected $table = 'kontrak_det';
    protected $primaryKey = 'IDXURUT';
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

    public static function getAll()
    {
        $model = self::get();
        return $model;
    }

    public static function getPopulateSalesOrder()
    {
        $model = self::select('kontrak_det.*', DB::RAW('sum(if(kontrak_det.state != "B" OR kontrak_det.state is null, kontrak_det.QTY, 0))as Qty_SO'), DB::RAW('SUM(sj_det.QTY) AS Qty_DO'), DB::RAW('SUM(rj_det.QTY) AS Qty_RJ'))
            ->leftJoin('sj_head', 'sj_head.no_So', 'kontrak_det.NO_BUKTI')
            ->leftJoin('sj_det', 'sj_det.NO_BUKTI', 'sj_head.NO_BUKTI')
            ->leftJoin('rj_head', 'rj_head.no_so', 'kontrak_det.NO_BUKTI')
            ->leftJoin('rj_det', 'rj_det.NO_BUKTI', 'rj_head.NO_BUKTI');
        return $model;
    }
}
