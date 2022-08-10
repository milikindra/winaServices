<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TablePNLProjectList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:pnlprojectlist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate table wina_sv_pnlprojectlist';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer  $drip
     * @return mixed
     */
    public function handle()
    {
        $sdate = date('Y-m-d', strtotime("-365 days"));
        $edate = date('Y-m-d');
        DB::select("CALL TF_RL_SO_LIST('','$sdate','$edate', 'N', 'Y','N','R')");
    }
}
