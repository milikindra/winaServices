<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{

    protected $table = 'mascustomer';
    protected $primaryKey = 'ID_CUST';
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

    public static function addData($request)
    {
        $model = new self();
        $model->ID_CUST = $request['ID_CUST'];
        $model->NM_CUST = $request['NM_CUST'];
        $model->ALAMAT1 = $request['ALAMAT1'];
        $model->TELP = $request['TELP'];
        $model->FAX = $request['FAX'];
        $model->TEMPO = $request['TEMPO'];
        $model->PLAFON = $request['PLAFON'];
        $model->AKTIF = $request['AKTIF'];
        $model->NO_NPWP = $request['NO_NPWP'];
        $model->AREA = $request['AREA'];
        $model->ID_SALES = $request['ID_SALES'];
        $model->tipeCustomer = $request['tipeCustomer'];
        $model->CREATOR = $request['CREATOR'];
        $model->EDITOR = $request['EDITOR'];
        $model->al_fac = $request['al_fac'];
        $model->kecamatan_fac = $request['kecamatan_fac'];
        $model->kabupaten_fac = $request['kabupaten_fac'];
        $model->propinsi_fac = $request['propinsi_fac'];
        $model->telp_fac = $request['telp_fac'];
        $model->fax_fac = $request['fax_fac'];
        $model->nama_npwp = $request['nama_npwp'];
        $model->al_npwp = $request['al_npwp'];
        $model->usaha = $request['usaha'];
        $model->keterangan = $request['keterangan'];
        $model->curr = $request['curr'];
        $model->isWapu = $request['isWapu'];
        $model->KodePajak = $request['KodePajak'];
        $model->no_ktp = $request['no_ktp'];
        $model->isBerikat = $request['isBerikat'];
        $model->alias = $request['alias'];
        $model->save();
        return $model;
    }

    public static function updateData($request)
    {
        $model = self::find($request['ID_CUST_OLD']);
        $model->ID_CUST = $request['ID_CUST'];
        $model->NM_CUST = $request['NM_CUST'];
        $model->ALAMAT1 = $request['ALAMAT1'];
        $model->TELP = $request['TELP'];
        $model->FAX = $request['FAX'];
        $model->TEMPO = $request['TEMPO'];
        $model->PLAFON = $request['PLAFON'];
        $model->AKTIF = $request['AKTIF'];
        $model->NO_NPWP = $request['NO_NPWP'];
        $model->AREA = $request['AREA'];
        $model->ID_SALES = $request['ID_SALES'];
        $model->tipeCustomer = $request['tipeCustomer'];
        $model->CREATOR = $request['CREATOR'];
        $model->EDITOR = $request['EDITOR'];
        $model->al_fac = $request['al_fac'];
        $model->kecamatan_fac = $request['kecamatan_fac'];
        $model->kabupaten_fac = $request['kabupaten_fac'];
        $model->propinsi_fac = $request['propinsi_fac'];
        $model->telp_fac = $request['telp_fac'];
        $model->fax_fac = $request['fax_fac'];
        $model->nama_npwp = $request['nama_npwp'];
        $model->al_npwp = $request['al_npwp'];
        $model->usaha = $request['usaha'];
        $model->keterangan = $request['keterangan'];
        $model->curr = $request['curr'];
        $model->isWapu = $request['isWapu'];
        $model->KodePajak = $request['KodePajak'];
        $model->no_ktp = $request['no_ktp'];
        $model->isBerikat = $request['isBerikat'];
        $model->alias = $request['alias'];
        $model->save();
        return $model;
    }
}
