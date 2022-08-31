<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Tree\ModuleNode;

class EfakturGenerator extends Controller
{
    public function generateCsv(Request $request)
    {
        $noSI = trim($request->si);
        $kode_ba = trim($request->ba);
        $kode_bc = trim($request->bc);

        $noSO = "";
        $noSO_asli = "";
        $termin = false;

        $FK = "";
        $KD_JENIS_TRANSAKSI = "";
        $FG_PENGGANTI = "";
        $NOMOR_FAKTUR = "";
        $MASA_PAJAK = "";
        $TAHUN_PAJAK = "";
        $TANGGAL_FAKTUR = "";
        $NPWP = "";
        $NAMA = "";
        $ALAMAT_LENGKAP = "";
        $JUMLAH_DPP = "";
        $JUMLAH_PPN = "";
        $JUMLAH_PPNBM = "";
        $ID_KETERANGAN_TAMBAHAN = "";
        $FG_UANG_MUKA = "";
        $UANG_MUKA_DPP = "";
        $UANG_MUKA_PPN = "";
        $UANG_MUKA_PPNBM = "";
        $REFERENSI = "";
        $KODE_DOKUMEN_PENDUKUNG = "";
        $detail = "";


        // Khusus SI yang UM get No SO from no SI
        $rowSO = DB::select(DB::RAW("SELECT no_so_um,no_so FROM jual_head WHERE no_bukti='$noSI'"));
        foreach ($rowSO as $row) {
            $noSO = $row->no_so_um;
            $noSO_asli = $row->no_so;
            $nilaiSO = 0;
        }

        $shipto = ""; // use for overiding alamat npwp if use_branch

        if ($noSO != "") {
            // get total UM , use_branch and alamatkirim from SO
            $row = DB::select(DB::RAW("SELECT floor(uangmuka) as uangmuka, floor(totdpp) as totdpp, floor(totppn) as totppn,use_branch,alamatkirim FROM kontrak_head WHERE no_bukti='$noSO';"));
            $nilaiSO = $row[0]->totdpp;
            $nilaiSO_PPN = $row[0]->totppn;
            // compare with total value
            if (count($row) > 0) {
                if ($row[0]->uangmuka < $row[0]->totdpp) {
                    $termin = true;
                }

                if ($row[0]->use_branch == 1) {
                    $shipto = $row[0]->alamatkirim;
                }
            }
        } else // bukan SI UM
        {
            $row = DB::select(DB::RAW("SELECT use_branch,alamatkirim FROM kontrak_head WHERE no_bukti='$noSO_asli'"));
            if (count($row) > 0) {
                if ($row[0]->use_branch == 1) {
                    $shipto = $row["alamatkirim"];
                }
            }
        }

        $query = DB::select(DB::RAW("SELECT isWapu,isBerikat,FG_PENGGANTI,no_pajak,tgl_bukti,no_NPWP,nama_npwp,ALAMAT_NPWP,floor(tot_dpp) as tot_dpp,floor(tot_pajak) as tot_pajak,FG_UANG_MUKA,floor(UANG_MUKA_DPP) as UANG_MUKA_DPP,floor(UANG_MUKA_PPN) as UANG_MUKA_PPN,no_bukti2 FROM v_jual_head_pajak WHERE no_bukti='" . $noSI . "'"));
        foreach ($query as $row) {

            if ($noSO == "") {
                $noSO = substr($row->no_bukti2, 3, 11);
            }

            $FK = "FK";
            $KD_JENIS_TRANSAKSI = "01";
            // if WAPU - BUMN
            if ($row->isWapu == "Y") $KD_JENIS_TRANSAKSI = "03";
            // if BERIKAT
            if ($row->isBerikat == "Y") $KD_JENIS_TRANSAKSI = "07";
            $FG_PENGGANTI = $row->FG_PENGGANTI;
            $NOMOR_FAKTUR = $row->no_pajak;
            $tgl_bukti = strtotime($row->tgl_bukti);
            $MASA_PAJAK = date("n", $tgl_bukti);
            $TAHUN_PAJAK = date("Y", $tgl_bukti);
            $TANGGAL_FAKTUR = date("d/m/Y", $tgl_bukti);
            $NPWP = $row->no_NPWP;
            $NAMA = trim($row->nama_npwp);
            if ($shipto == "") { // for overiding - based on new regulation
                $ALAMAT_LENGKAP = trim($row->ALAMAT_NPWP);
            } else {
                $ALAMAT_LENGKAP = trim($shipto);
            }
            $JUMLAH_DPP = $row->tot_dpp;
            $JUMLAH_PPN = $row->tot_pajak;
            $JUMLAH_PPNBM = "0";
            $ID_KETERANGAN_TAMBAHAN = "0";
            // if BERIKAT
            if ($row->isBerikat == "Y") $ID_KETERANGAN_TAMBAHAN = "1";
            $FG_UANG_MUKA = $row->FG_UANG_MUKA; // dari view hanya handle 0 dan 2
            // if UM <100% invoice jadi termin kode 1
            if ($FG_UANG_MUKA == "0" && $termin == true) $FG_UANG_MUKA = "1";
            $UANG_MUKA_DPP = $row->UANG_MUKA_DPP;
            $UANG_MUKA_PPN = $row->UANG_MUKA_PPN;
            // Apabila TERMIN
            if ($FG_UANG_MUKA == "1") {
                $JUMLAH_DPP = $nilaiSO;
                $JUMLAH_PPN = $nilaiSO_PPN;
                $UANG_MUKA_DPP = $row->tot_dpp;
                $UANG_MUKA_PPN = $row->tot_pajak;
            }
            $UANG_MUKA_PPNBM = "0";
            $REFERENSI = $row->no_bukti2;
            if ($kode_ba != "") $REFERENSI = $kode_ba . ";" . $REFERENSI;

            $KODE_DOKUMEN_PENDUKUNG = $kode_bc;

            // utk menghandle masalah pembulatan
            $jmldppdetail = 0;
            $jmlppndetail = 0;

            if ($FG_UANG_MUKA == 1) {
                $detail = "";
                $query2 =  DB::select(DB::RAW("SELECT kd.no_stock,kd.nm_stock,floor(kd.HARGA) as HARGA,floor(kd.QTY) as QTY,floor(kd.JMLBRUTO) AS JMLBRUTO,floor(kd.JMLDISKON) as JMLDISKON,floor(kd.JUMLAHNETTO) as JUMLAHNETTO,floor(mp.prosen) AS prosen FROM kontrak_det kd LEFT JOIN maskodepajak mp ON kd.tax=mp.kode WHERE no_bukti='$noSO' and (ifnull(`kd`.`kode_group`,'') = '')  ORDER BY kd.IDXURUT;"));
                //echo $querySelect2;
                foreach ($query2 as $row2) {
                    $OF2 = "OF";
                    $KODE_OBJEK2 = trim($row2->no_stock);
                    $NAMA2 = trim($row2->nm_stock);
                    $HARGA_SATUAN2 = $row2->HARGA;
                    $JUMLAH_BARANG2 = $row2->QTY;
                    $HARGA_TOTAL2 = $row2->JMLBRUTO;
                    $DISKON2 = $row2->JMLDISKON;
                    $DPP2 = $row2->JUMLAHNETTO;
                    $PPN2 = floor($DPP2 * $row2->prosen / 100);
                    $TARIF_PPNBM2 = "0";
                    $PPNBM2 = "0";
                    $detail .= "\r\n$OF2,$KODE_OBJEK2,\"$NAMA2\",$HARGA_SATUAN2,$JUMLAH_BARANG2,$HARGA_TOTAL2,$DISKON2,$DPP2,$PPN2,$TARIF_PPNBM2,$PPNBM2,,,,,,,,,";
                    $jmldppdetail += $DPP2;
                    $jmlppndetail += $PPN2;
                }
            } else {
                $query2 =  DB::select(DB::RAW("SELECT no_stock,nm_stock,floor(harga) as harga,floor(qty) as qty, floor(jmlbruto) as jmlbruto,floor(jmldiskon) as jmldiskon,floor(jumlah) as jumlah,floor(pajak) as pajak FROM v_jual_det_pajak WHERE no_bukti='$noSI' ORDER BY idxurut;"));
                //echo $querySelect2;
                $detail = "";
                foreach ($query2 as $row2) {
                    $OF2 = "OF";
                    $KODE_OBJEK2 = trim($row2->no_stock);
                    $NAMA2 = trim($row2->nm_stock);
                    $HARGA_SATUAN2 = $row2->harga;
                    $JUMLAH_BARANG2 = $row2->qty;
                    $HARGA_TOTAL2 = $row2->jmlbruto;
                    $DISKON2 = $row2->jmldiskon;
                    $DPP2 = $row2->jumlah;
                    $PPN2 = $row2->pajak;
                    $TARIF_PPNBM2 = "0";
                    $PPNBM2 = "0";
                    $detail .= "\r\n$OF2,$KODE_OBJEK2,\"$NAMA2\",$HARGA_SATUAN2,$JUMLAH_BARANG2,$HARGA_TOTAL2,$DISKON2,$DPP2,$PPN2,$TARIF_PPNBM2,$PPNBM2,,,,,,,,,";
                    $jmldppdetail += $DPP2;
                    $jmlppndetail += $PPN2;
                }
            }
        }

        $str = "";
        $str .= "FK,KD_JENIS_TRANSAKSI,FG_PENGGANTI,NOMOR_FAKTUR,MASA_PAJAK,TAHUN_PAJAK,TANGGAL_FAKTUR,NPWP,NAMA,ALAMAT_LENGKAP,JUMLAH_DPP,JUMLAH_PPN,JUMLAH_PPNBM,ID_KETERANGAN_TAMBAHAN,FG_UANG_MUKA,UANG_MUKA_DPP,UANG_MUKA_PPN,UANG_MUKA_PPNBM,REFERENSI,KODE_DOKUMEN_PENDUKUNG";
        $str .= "\r\n";
        $str .= "LT,NPWP,NAMA,JALAN,BLOK,NOMOR,RT,RW,KECAMATAN,KELURAHAN,KABUPATEN,PROPINSI,KODE_POS,NOMOR_TELEPON,,,,,,";
        $str .= "\r\n";
        $str .= "OF,KODE_OBJEK,NAMA,HARGA_SATUAN,JUMLAH_BARANG,HARGA_TOTAL,DISKON,DPP,PPN,TARIF_PPNBM,PPNBM,,,,,,,,,";
        $str .= "\r\n";
        $str .= "$FK,$KD_JENIS_TRANSAKSI,$FG_PENGGANTI,$NOMOR_FAKTUR,$MASA_PAJAK,$TAHUN_PAJAK,$TANGGAL_FAKTUR,$NPWP,\"$NAMA\",\"$ALAMAT_LENGKAP\",$JUMLAH_DPP,$JUMLAH_PPN,$JUMLAH_PPNBM,$ID_KETERANGAN_TAMBAHAN,$FG_UANG_MUKA,$UANG_MUKA_DPP,$UANG_MUKA_PPN,$UANG_MUKA_PPNBM,$REFERENSI,$KODE_DOKUMEN_PENDUKUNG";
        $str .= $detail;

        $data = [
            'result' => true,
            'so' => $noSO,
            'si' => $noSI,
            'str' => $str
        ];

        return $data;
    }
}
