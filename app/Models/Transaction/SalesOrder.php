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
        return $model;
    }
    public static function getPopulateSalesOrderHead()
    {
        $model = self::select('*');
        return $model;
    }

    public static function getPopulateSalesOrderDetail()
    {
        $model = DB::table('wina_v_salesorder_detail');
        return $model;
    }

    public static function addData($request)
    {
        $model = new self();
        $model->NO_BUKTI = $request['NO_BUKTI'];
        $model->TGL_BUKTI = $request['TGL_BUKTI'];
        $model->tgl_due = $request['tgl_due'];
        $model->DIVISI = $request['DIVISI'];
        $model->ID_CUST = $request['ID_CUST'];
        $model->NM_CUST = $request['NM_CUST'];
        $model->TEMPO = $request['TEMPO'];
        $model->ID_SALES = $request['ID_SALES'];
        $model->NM_SALES = $request['NM_SALES'];
        $model->KETERANGAN = $request['KETERANGAN'];
        $model->CREATOR = $request['CREATOR'];
        $model->EDITOR = $request['EDITOR'];
        $model->rate = $request['rate'];
        $model->curr = $request['curr'];
        $model->dept = $request['dept'];
        $model->PO_CUST = $request['PO_CUST'];
        $model->attn = $request['attn'];
        $model->pay_term = $request['pay_term'];
        // $model->discH = $request['discH'];
        $model->no_ref = $request['no_ref'];
        $model->alamatkirim = $request['alamatkirim'];
        $model->jenis = $request['jenis'];
        $model->totdetail = $request['totdetail'];
        // $model->rp_disch = $request['rp_disch'];
        $model->ppntotdetail = $request['ppntotdetail'];
        $model->uangmuka = $request['uangmuka'];
        $model->uangmuka_ppn = $request['uangmuka_ppn'];
        $model->use_branch = $request['use_branch'];
        $model->save();
        return $model;
    }
    public static function updateData($request, $where)
    {

        $model = self::find($where['id']);
        $model->NO_BUKTI = $request['NO_BUKTI'];
        $model->TGL_BUKTI = $request['TGL_BUKTI'];
        $model->DIVISI = $request['DIVISI'];
        $model->ID_CUST = $request['ID_CUST'];
        $model->NM_CUST = $request['NM_CUST'];
        $model->TEMPO = $request['TEMPO'];
        $model->ID_SALES = $request['ID_SALES'];
        $model->NM_SALES = $request['NM_SALES'];
        $model->KETERANGAN = $request['KETERANGAN'];
        $model->CREATOR = $request['CREATOR'];
        $model->EDITOR = $request['EDITOR'];
        $model->rate = $request['rate'];
        $model->curr = $request['curr'];
        $model->dept = $request['dept'];
        $model->PO_CUST = $request['PO_CUST'];
        $model->attn = $request['attn'];
        $model->pay_term = $request['pay_term'];
        // $model->discH = $request['discH'];
        $model->no_ref = $request['no_ref'];
        $model->alamatkirim = $request['alamatkirim'];
        $model->jenis = $request['jenis'];
        $model->totdetail = $request['totdetail'];
        // $model->rp_disch = $request['rp_disch'];
        $model->ppntotdetail = $request['ppntotdetail'];
        $model->uangmuka = $request['uangmuka'];
        $model->uangmuka_ppn = $request['uangmuka_ppn'];
        $model->save();
        return $model;
    }

    public static function deleteData($request)
    {
        $model = self::where('NO_BUKTI', $request)->delete();
        return $model;
    }

    public static function getById()
    {
        $model = self::select('*');
        return $model;
    }
}
