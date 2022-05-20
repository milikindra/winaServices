<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesOrder extends Model
{

    protected $table = 'kontrak_head';
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

    public static function getPopulateSalesOrder()
    {

        $model = DB::table('wina_v_salesorder');

        // $model = DB::select(DB::RAW('select * from (select 
        // kontrak_head.*,
        //         sum(
        //         IF
        //         ( kontrak_det.state != "B" OR kontrak_det.state IS NULL, kontrak_det.QTY, 0 )) AS Qty_SO,
        //         IFNULL( SUM( sj_det.QTY ), 0 ) AS Qty_DO,
        //         IFNULL( SUM( rj_det.QTY ), 0 ) AS Qty_RJ 
        //     FROM
        //         `kontrak_head`
        //         LEFT JOIN `kontrak_det` ON `kontrak_det`.`NO_BUKTI` = `kontrak_head`.`NO_BUKTI`
        //         LEFT JOIN `sj_head` ON `sj_head`.`no_So` = `kontrak_det`.`NO_BUKTI`
        //         LEFT JOIN `sj_det` ON `sj_det`.`NO_BUKTI` = `sj_head`.`NO_BUKTI`
        //         LEFT JOIN `rj_head` ON `rj_head`.`no_so` = `kontrak_det`.`NO_BUKTI`
        //         LEFT JOIN `rj_det` ON `rj_det`.`NO_BUKTI` = `rj_head`.`NO_BUKTI` 
        //     GROUP BY
        //         `kontrak_head`.`NO_BUKTI`) as A
        // '));
        // $model = self::select(
        //     DB::RAW('kontrak_head.NO_BUKTI as NO_BUKTI'),
        //     DB::RAW('kontrak_head.TGL_BUKTI as TGL_BUKTI'),
        //     DB::RAW('kontrak_head.ID_CUST as ID_CUST'),
        //     DB::RAW('kontrak_head.NM_CUST as NM_CUST'),
        //     DB::RAW('kontrak_head.Dept as Dept'),
        //     DB::RAW('kontrak_head.PO_CUST as PO_CUST'),
        //     DB::RAW('kontrak_head.totdpp as totdpp'),
        //     DB::RAW('kontrak_head.totppn as totppn'),
        //     DB::RAW('kontrak_head.total as total'),
        //     DB::RAW('sum(if(kontrak_det.state != "B" OR kontrak_det.state is null, kontrak_det.QTY, 0))as Qty_SO'),
        //     DB::RAW('IFNULL(SUM(sj_det.QTY),0) AS Qty_DO'),
        //     DB::RAW('IFNULL(SUM(rj_det.QTY),0) AS Qty_RJ'),
        // )
        //     ->leftJoin('kontrak_det', 'kontrak_det.NO_BUKTI', 'kontrak_head.NO_BUKTI')
        //     ->leftJoin('sj_head', 'sj_head.no_So', 'kontrak_det.NO_BUKTI')
        //     ->leftJoin('sj_det', 'sj_det.NO_BUKTI', 'sj_head.NO_BUKTI')
        //     ->leftJoin('rj_head', 'rj_head.no_so', 'kontrak_det.NO_BUKTI')
        //     ->leftJoin('rj_det', 'rj_det.NO_BUKTI', 'rj_head.NO_BUKTI');
        return $model;

        // $model = self::select('*');
        // return $model;
    }

    public static function addData($request)
    {
        $model = new self();
        $model->creator = $request->input('creator');
        $model->editor = $request->input('creator');
        $model->no_stock = $request->input('no_stock');
        $model->nm_stock = $request->input('nm_stock');
        $model->sat = $request->input('sat');
        $model->minstock = $request->input('minstock');
        $model->kategori = $request->input('kategori');
        $model->kategori2 = $request->input('kategori2');
        $model->merk = $request->input('merk');
        $model->hrg_jual = $request->input('hrg_jual');
        $model->keterangan = $request->input('keterangan');
        $model->aktif = $request->input('aktif');
        $model->isKonsi = $request->input('isKonsi');
        $model->isMinus = $request->input('isMinus');
        $model->NO_REK1 = $request->input('NO_REK1');
        $model->NO_REK2 = $request->input('NO_REK2');
        $model->PphPs23 = $request->input('PphPs23');
        $model->PPhPs21 = $request->input('PPhPs21');
        $model->PPhPs4Ayat2 = $request->input('PPhPs4Ayat2');
        $model->PPhPs21OP = $request->input('PPhPs21OP');
        $model->save();
        return $model;
    }
    public static function updateData($request)
    {
        $model = self::find($request->input('no_stock'));
        $model->editor = $request->input('creator');
        $model->no_stock = $request->input('no_stock');
        $model->nm_stock = $request->input('nm_stock');
        $model->sat = $request->input('sat');
        $model->minstock = $request->input('minstock');
        $model->kategori = $request->input('kategori');
        $model->kategori2 = $request->input('kategori2');
        $model->merk = $request->input('merk');
        $model->hrg_jual = $request->input('hrg_jual');
        $model->keterangan = $request->input('keterangan');
        $model->aktif = $request->input('aktif');
        $model->isKonsi = $request->input('isKonsi');
        $model->isMinus = $request->input('isMinus');
        $model->NO_REK1 = $request->input('NO_REK1');
        $model->NO_REK2 = $request->input('NO_REK2');
        $model->PphPs23 = $request->input('PphPs23');
        $model->PPhPs21 = $request->input('PPhPs21');
        $model->PPhPs4Ayat2 = $request->input('PPhPs4Ayat2');
        $model->PPhPs21OP = $request->input('PPhPs21OP');
        $model->save();
        return $model;
    }

    public static function deleteData($request)
    {
        $model = self::where('no_stock', $request)->delete();
        return $model;
    }
}
