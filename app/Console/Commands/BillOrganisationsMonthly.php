<?php

namespace App\Console\Commands;

use App\Models\Organisation;
use Illuminate\Console\Command;

class BillOrganisationsMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bill:organisations:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge organisations their monthly subscription fee';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orgs = Organisation::with('employees')->get();


        foreach ($orgs as $org) {
            \App\Jobs\BillOrganizationJob::dispatch($org);
        }
    }
}
