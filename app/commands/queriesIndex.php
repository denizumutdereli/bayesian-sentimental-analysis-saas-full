<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class queriesIndex extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queries:index';
    protected static $api_main = 'http://api.labelai.com/';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve mentions to the queue';

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

        //Get All active rules
        $bwrules = array();
        $bwrules = Bwrule::where('is_active', '=', '1')->where('status', '=', '1')->get();

        $i = 0;

        foreach ($bwrules as $bwrule) :

            $i++;
            sleep(1);
            
            try {

                $conditions = [];
                $conditions['account_id'] = $bwrule->account_id;
                $conditions['bwatchid'] = $bwrule->bwatch_id;
                $conditions['projectid'] = $bwrule->project_id;
                $conditions['queries'] = explode('&', self::normalizeData($bwrule->queries));

                foreach ($conditions['queries'] as $query) {

                    $conditions['queryId'] = $query;

                    $carbon = new \Carbon\Carbon();
                    $carbon->timezone('Europe/Istanbul');

                    //check if the query exist
                    $q = BwQueries::where('query_id', '=', $query)->first();

                    //if query not exist record it
                    if (!$q) {
                        $q = new BwQueries();
                        $q->account_id = $conditions['account_id'];
                        $q->bwatch_id = $conditions['bwatchid'];
                        $q->project_id = $conditions['projectid'];
                        $q->query_id = $conditions['queryId'];
                        $q->cycle = 0;
                        $q->save();
                    }
                }

                continue;
            } catch (Exception $e) {
                print $e->getMessage();
            }

            $bwrule->last_queue_time = date('Y-m-d h:i:s');
            $bwrule->update();

        endforeach;

        return Response::json('Number of: ' . count($bwrules) . ' rules queued.');
    }

    /**
     * 
     * Normalization for the data
     * 
     */
    public function normalizeData($data) {
        $data = unserialize($data);
        $response = "";
        foreach ($data as $val)
            $response .= $val . '&';

        $response = substr($response, 0, -1);

        return $response;
    }

    /*
     * Slug
     * 
     */

    public function Slug($string) {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), ''));
    }

    /**
     * Get the console command aycorguments.
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
