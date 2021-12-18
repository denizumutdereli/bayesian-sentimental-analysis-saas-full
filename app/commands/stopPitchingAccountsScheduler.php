<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class stopPitchingAccountsScheduler extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'stop:pitching';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stops pitching accounts after 14 days';

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
         * Stop Pitching Accounts after 14 days
         * return boolen
         */

        $account = array();
        $accounts = Account::where('accountType', '=', 'pitching')->get();

        if ($accounts):
            $now = \Carbon\Carbon::now();

            foreach ($accounts as $account) {

                $created = new \Carbon\Carbon($account->created_at);
                $difference = $created->diffInDays($now);
                $i = 0;
                if ($difference >= Config::get('settings.account.pitching.period', 14)):
                    $i++;
                    $account->delete();
                endif;
            }
        endif;
        return Response::json('Number of: ' . count($account) . ' account(s) stoped.');
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
