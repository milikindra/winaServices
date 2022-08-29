<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesInvoice extends Model
{

    protected $table = 'jual_head';
    // protected $primaryKey = 'NO_BUKTI';
    protected $primaryKey = null;
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
            'jual_head.NO_BUKTI',
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
            'jual_head.NO_BUKTI',
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
        $model = self::select('sj_head.*');
        $model->rightJoin('kontrak_head', 'jual_head.no_so', 'kontrak_head.NO_BUKTI');
        $model->leftJoin('jual_det', 'jual_head.NO_BUKTI', 'jual_det.NO_BUKTI');
        $model->leftJoin('sj_head', 'kontrak_head.NO_BUKTI', 'sj_head.no_So');
        $model->whereRaw('sj_head.NO_BUKTI <> jual_det.no_sj');

        return $model;
    }

    public static function addData($request)
    {
        $model = new self();
        $model->NO_BUKTI = $request['NO_BUKTI'];
        $model->TGL_BUKTI = $request['TGL_BUKTI'];
        $model->ID_CUST = $request['ID_CUST'];
        $model->NM_CUST = $request['NM_CUST'];
        $model->TEMPO = $request['TEMPO'];
        $model->ID_SALES = $request['ID_SALES'];
        $model->NM_SALES = $request['NM_SALES'];
        $model->PPN = $request['PPN'];
        $model->KETERANGAN = $request['KETERANGAN'];
        $model->CREATOR = $request['CREATOR'];
        // $model->EDITOR = $request['EDITOR'];
        $model->rate = $request['rate'];
        $model->curr = $request['curr'];
        $model->no_so = $request['no_so'];
        $model->alamatkirim = $request['alamatkirim'];
        $model->pay_term = $request['pay_term'];
        $model->isUM = $request['isUM'];
        $model->no_so_um = $request['no_so_um'];
        $model->uangmuka = $request['uangmuka'];
        $model->uangmuka_ppn = $request['uangmuka_ppn'];
        $model->totdetail = $request['totdetail'];
        $model->ppntotdetail = $request['ppntotdetail'];
        $model->no_pajak = $request['no_pajak'];
        $model->no_rek = $request['no_rek'];
        $model->isWapu = $request['isWapu'];
        $model->no_tt = $request['no_tt'];
        $model->tgl_tt = $request['tgl_tt'];
        $model->penerima_tt = $request['penerima_tt'];
        $model->isSI_UM_FINAL = $request['isSI_UM_FINAL'];
        $model->save();
        return $model;
    }

    public static function updateData($request, $where)
    {
        $model = self::find($where);
        $model->NO_BUKT = $request['NO_BUKTI'];
        $model->TGL_BUKTI = $request['TGL_BUKTI'];
        $model->ID_CUST = $request['ID_CUST'];
        $model->NM_CUST = $request['NM_CUST'];
        $model->TEMPO = $request['TEMPO'];
        $model->ID_SALES = $request['ID_SALES'];
        $model->NM_SALES = $request['NM_SALES'];
        $model->PPN = $request['PPN'];
        $model->KETERANGAN = $request['KETERANGAN'];
        $model->EDITOR = $request['EDITOR'];
        $model->rate = $request['rate'];
        $model->curr = $request['curr'];
        $model->no_so = $request['no_so'];
        $model->alamatkirim = $request['alamatkirim'];
        $model->pay_term = $request['pay_term'];
        $model->isUM = $request['isUM'];
        $model->no_so_um = $request['no_so_um'];
        $model->uangmuka = $request['uangmuka'];
        $model->uangmuka_ppn = $request['uangmuka_ppn'];
        $model->totdetail = $request['totdetail'];
        $model->ppntotdetail = $request['ppntotdetail'];
        $model->no_pajak = $request['no_pajak'];
        $model->no_rek = $request['no_rek'];
        $model->isWapu = $request['isWapu'];
        $model->no_tt = $request['no_tt'];
        $model->tgl_tt = $request['tgl_tt'];
        $model->penerima_tt = $request['penerima_tt'];
        $model->isSI_UM_FINAL = $request['isSI_UM_FINAL'];
        $model->save();
        return $model;
    }

    public static function deleteData($request)
    {
        $model = self::where('NO_BUKTI', $request)->delete();
        return $model;
    }
}
