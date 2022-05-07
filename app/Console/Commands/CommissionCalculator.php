<?php

namespace App\Console\Commands;

use App\Services\CommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CommissionCalculator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:commission {file=input.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command calculates the commission fee.';

    public $all_data = array();
    public $currencies;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->currencies = json_decode(Http::get('https://developers.paysera.com/tasks/api/currency-exchange-rates'));
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CommissionService $commissionService)
    {
        $filename = $this->argument('file');
        $getfile = public_path($filename);
        $file = fopen($getfile, "r");
        while ( ($data = fgetcsv($file, 200, ",")) !==FALSE ) {
            $this->all_data[] = $data;
        }
        foreach ($this->all_data as $key=>$data) {
            $calculated_data = $commissionService->calculateCommissionFee($data, $this->currencies, $this->all_data, $key);
            $this->info($calculated_data);
        }
        return 0;
    }
}
