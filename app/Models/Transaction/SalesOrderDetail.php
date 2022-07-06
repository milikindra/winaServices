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
    const CREATED_AT = 'URUT';
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
        $model->NO_STOCK = $request['NO_STOCK'];
        $model->NM_STOCK = $request['NM_STOCK'];
        $model->QTY = $request['QTY'];
        $model->SAT = $request['SAT'];
        $model->HARGA = $request['HARGA'];
        $model->DISC1 = $request['DISC1'];
        $model->DISC2 = $request['DISC2'];
        $model->DISC3 = $request['DISC3'];
        $model->DISCRP = $request['DISCRP'];
        $model->discrp2 = $request['discrp2'];
        $model->state = $request['state'];
        $model->alasan = $request['alasan'];
        $model->nourut = $request['nourut'];
        $model->tax = $request['tax'];
        $model->kode_group = $request['kode_group'];
        $model->qty_grup = $request['qty_grup'];
        $model->VINTRASID = $request['VINTRASID'];
        $model->tahun = $request['tahun'];
        $model->save();
        return $model;
    }

    public static function updateData($request, $where)
    {
        $model = self->where('NO_BUKTI');
        $model->NO_BUKTI = $request['NO_BUKTI'];
        $model->NO_STOCK = $request['NO_STOCK'];
        $model->NM_STOCK = $request['NM_STOCK'];
        $model->QTY = $request['QTY'];
        $model->SAT = $request['SAT'];
        $model->HARGA = $request['HARGA'];
        $model->DISC1 = $request['DISC1'];
        $model->DISC2 = $request['DISC2'];
        $model->DISC3 = $request['DISC3'];
        $model->DISCRP = $request['DISCRP'];
        $model->discrp2 = $request['discrp2'];
        $model->state = $request['state'];
        $model->alasan = $request['alasan'];
        $model->nourut = $request['nourut'];
        $model->tax = $request['tax'];
        $model->kode_group = $request['kode_group'];
        $model->qty_grup = $request['qty_grup'];
        $model->VINTRASID = $request['VINTRASID'];
        $model->tahun = $request['tahun'];
        $model->save();
        return $model;
    }
}
