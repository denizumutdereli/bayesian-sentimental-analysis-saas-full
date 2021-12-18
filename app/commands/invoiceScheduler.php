<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class invoiceScheduler extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create invoices';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {

        /*
         * Create first invoices for each Accounts
         * return boolen
         */

        $accounts = Account::all();

        foreach ($accounts as $account):

            //check invoices
            $start = new \Carbon\Carbon('first day of this month');
            $end = new \Carbon\Carbon('last day of this month');
            $invoice = Invoice::where('account_id', $account->id)->whereBetween('created_at', array($start, $end))->first();

            if (!$invoice) {
                $invoice = Invoice::create([
                            'account_id' => $account->id,
                            'user_id' => $account->created_by,
                            'amount' => '',
                            'status' => 'open',
                            'details' => '',
                            'payment_type' => 'Contract',
                            'payment_date' => $end,
                            'created_by' => $account->created_by,
                            'updated_by' => $account->created_by
                ]);
            }

            //Invoices are created now count the details
            //SELECT section,count(amount) FROM `pings` GROUP BY section
            $pings = $account->pings()
                    ->selectRaw('section, sum(amount) as sum')
                    ->groupBy('section')
                    ->whereBetween('created_at', array($start, $end))
                    ->get();

            $details = [];
            $gross_total = 0;

            $rates = Config::get('settings.api.rates'); //get rates from settings page

            foreach ($pings as $ping):

                if (!isset($rates[$ping->section]))
                    $param = 'default';
                else $param = $ping->section;

                $price = Config::get('settings.api.rates.' . $param . '.price');

                if ($account->accountType == 'pitching'):
                    $price = 0.00;
                endif;

                $details[ucfirst($ping->section)] = $ping->sum . ':' . number_format($price, 2, '.', '');

                $gross_total = $gross_total + ($ping->sum * number_format($price, 2, '.', ''));
                
                $gross_total = number_format($gross_total, 2, '.', '');

            endforeach;

            switch ($account->package):
                case "0":
                    $subscription = '50';
                    break;
                case "250":
                    $subscription = '500';
                    break;
                case "500":
                    $subscription = '1000';
                    break;
                case "-1":
                    $subscription = '1500';
                    break;
                default:
                    $subscription = '500';
                    break;
            endswitch;

            $details['Contract'] = '1:' . $subscription;

            //Now update the invoice;
            Invoice::find($invoice->id)->update([
                'details' => json_encode($details),
                'amount' => number_format($gross_total + $subscription, 2, '.', ''),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

        endforeach;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return [
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
        ];
    }

}
