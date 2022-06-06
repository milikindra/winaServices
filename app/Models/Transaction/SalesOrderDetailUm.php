<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesOrderDetailUm extends Model
{

    protected $table = 'kontrak_det_um';
    protected $primaryKey = 'idxurut';
    public $incrementing = false;
    const CREATED_AT = 'urut';
    const UPDATED_AT = NULL;

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

    public static function getSoDetail()
    {
        $model = self::select('*');
        return $model;
    }

    public static function addData($request)
    {
        $model = new self();
        $model->NO_BUKTI = $request['NO_BUKTI'];
        $model->keterangan = $request['keterangan'];
        $model->nilai = $request['nilai'];
        $model->nourut = $request['nourut'];
        $model->tax = $request['tax'];
        $model->save();
        return $model;
    }
}
