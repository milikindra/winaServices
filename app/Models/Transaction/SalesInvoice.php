<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesInvoice extends Model
{

    protected $table = 'jual_head';
    protected $primaryKey = 'NO_BUKTI';
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

    public static function getsiEfaktur()
    {
        $model = self::select("jual_head.*", DB::raw(" GROUP_CONCAT( DISTINCT jual_det.no_sj ORDER BY jual_det.no_sj SEPARATOR ';' ) as DO_id"));
        return $model;
    }

    public static function getPopulateSalesInvoice()
    {
        $model = self::select(
            'jual_head.no_bukti2',
            'jual_head.tag',
            'jual_head.TGL_BUKTI',
            'jual_head.ID_CUST',
            'jual_head.NM_CUST',
            'jual_head.NM_SALES',
            'jual_head.TEMPO',
            'jual_head.no_so',
            'jual_head.curr',
            'jual_head.rate',
            'jual_head.isWapu',
            'jual_head.no_pajak',
            'jual_head.totdpp_rp',
            'jual_head.totppn_rp',
            'jual_head.total_rp',
            'jual_head.no_tt',
            'jual_head.tgl_tt',
            'jual_head.penerima_tt',
            DB::RAW('DATE_ADD(jual_head.tgl_tt, INTERVAL jual_head.TEMPO DAY) as due_date'),
            DB::RAW('DATEDIFF(CURDATE(), DATE_ADD(jual_head.tgl_tt, INTERVAL jual_head.TEMPO DAY) ) as age'),
            'bayar.income'
        );
        $model->leftJoin(DB::RAW('( SELECT sum( ifnull( nilai, 0 )+ ifnull( potongan, 0 )) AS income, no_nota FROM bayar_det GROUP BY no_nota ) AS bayar'), 'bayar.no_nota', 'jual_head.NO_BUKTI');
        return $model;
    }

    public static function getPopulateSalesInvoiceDetail()
    {
        $model = self::select(
            'jual_head.no_bukti2',
            'jual_head.tag',
            'jual_head.TGL_BUKTI',
            'jual_head.ID_CUST',
            'jual_head.NM_CUST',
            'jual_head.NM_SALES',
            'jual_head.TEMPO',
            'jual_head.no_so',
            'jual_head.curr',
            'jual_head.rate',
            'jual_head.isWapu',
            'jual_head.no_pajak',
            'jual_head.totdpp_rp',
            'jual_head.totppn_rp',
            'jual_head.total_rp',
            'jual_head.no_tt',
            'jual_head.tgl_tt',
            'jual_head.penerima_tt',
            DB::RAW('DATE_ADD(jual_head.tgl_tt, INTERVAL jual_head.TEMPO DAY) as due_date'),
            DB::RAW('DATEDIFF(CURDATE(), DATE_ADD(jual_head.tgl_tt, INTERVAL jual_head.TEMPO DAY) ) as age'),
            'jual_det.no_sj',
            'jual_det.NO_STOCK',
            'jual_det.NM_STOCK',
            'jual_det.QTY',
            'jual_det.SAT',
            'jual_det.HARGA',
            'jual_det.JUMLAH',
            'bayar.income'
        );
        $model->leftJoin(DB::RAW('( SELECT sum( ifnull( nilai, 0 )+ ifnull( potongan, 0 )) AS income, no_nota FROM bayar_det GROUP BY no_nota ) AS bayar'), 'bayar.no_nota', 'jual_head.NO_BUKTI');
        $model->leftJoin('jual_det', 'jual_head.NO_BUKTI', 'jual_det.NO_BUKTI');
        return $model;
    }

    public static function geDataDo()
    {
        $model = self::select(
            'sj_head.*',
            DB::RAW('IFNULL(jual_head.total_rp,0) as bill')
        );
        $model->rightJoin('kontrak_head', 'jual_head.no_so', 'kontrak_head.NO_BUKTI');
        $model->leftJoin('sj_head', 'kontrak_head.NO_BUKTI', 'sj_head.no_So');
        $model->whereRaw('kontrak_head.total_rp <> IFNULL(jual_head.total_rp,0)');
        return $model;
    }
}
