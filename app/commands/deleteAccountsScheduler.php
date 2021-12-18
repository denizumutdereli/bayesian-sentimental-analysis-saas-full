<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class deleteAccountsScheduler extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'force:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently remove the softdeleted accounts';

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
         * Delete Accounts after 14 days
         * return boolen
         */
        $accountQueue = array();
        $accounts = Account::onlyTrashed()->get();
        $now = \Carbon\Carbon::now();

        foreach ($accounts as $account) {

            $deleted = new \Carbon\Carbon($account->deleted_at);
            $difference = $deleted->diffInDays($now);

            if ($difference >= Config::get('settings.account.trashed.delete_time', 14)):

                $accountQueue[] = $account->id;
                $account->pings()->withTrashed()->forceDelete();
                $account->users()->withTrashed()->forceDelete();
                $account->sources()->withTrashed()->forceDelete();
                $account->uploads()->withTrashed()->forceDelete();
                $account->tags()->withTrashed()->forceDelete();
                $account->taguploads()->withTrashed()->forceDelete();
                $account->domains()->withTrashed()->forceDelete();
                $account->comments()->withTrashed()->forceDelete();
                $account->sentimentals()->withTrashed()->forceDelete();
                $account->tickets()->withTrashed()->forceDelete();
                $account->userlogs()->withTrashed()->forceDelete();
                $account->invoices()->withTrashed()->forceDelete();
            endif;
        }

        //Delete Account masters       
        if (count($accountQueue) AND Account::withTrashed()->whereIn('id', $accountQueue)->forceDelete()) {
            return Response::json('Number of: ' . count($account) . ' account(s) deleted.');
        }
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
