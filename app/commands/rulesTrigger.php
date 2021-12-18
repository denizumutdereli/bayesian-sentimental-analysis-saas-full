<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Aws\Sqs\SqsClient;
use Aws\S3\S3Client;
use Aws\Common\Credentials\Credentials;
use Guzzle\Http\EntityBody;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use ZipArchive as Zip;

class rulesTrigger extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rules:queue';
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
     * 
     * Declaration of a how to a rule procudure should be
     * info
     * 
     * accountid:
     * userid:
     * bwtoken:
     * bwuser:
     * bwatchid:
     * bwruleid:
     * projectid:
     * queries:
     *      [data]
     * querygroups:
     *      [data]
     * categories:
     *      [data]
     * domainid:
     * action:
     *  [sentiment]
     *          [options]
     *  [delete]
     * 
     *  [tag]
     *      [tags]
     * 
     * dataPagging:
     * dataMark:
     * logs:
     * timestamps:
     */

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {

        $credentials = new Credentials(Config::get('settings.api.aws.sqs.key'), Config::get('settings.api.aws.sqs.secret'));

        // Instantiate the SQS client with your AWS credentials
        $client = SqsClient::factory(array(
                    'credentials' => $credentials,
                    'region' => Config::get('settings.api.aws.sqs.region')
        ));

        //Get All active rules
        $bwrules = array();
        $bwrules = Bwrule::where('is_active', '=', '1')->where('status', '=', '1')->get();

        $i = 0;

        foreach ($bwrules as $bwrule) :

            $i++;
            sleep(1);
            $bwatch = Bwatch::find($bwrule->bwatch_id);

            try {
                $conditions = [];
                //dataPrepration:
                $conditions['bwruleid'] = $bwrule->id;
                $conditions['rulename'] = $bwrule->name;
                $conditions['accountid'] = $bwrule->account_id;
                $conditions['access_token'] = Account::find($bwrule->account_id)->access_token;
                $conditions['userid'] = $bwrule->account_id;
                $conditions['bwtoken'] = $bwrule->bw_token;
                $conditions['bwatchuser'] = $bwatch->username;
                $conditions['bwatchid'] = $bwatch->id;
                $conditions['fromdate'] = ($bwatch->fromdate == 0) ? Config::get('settings.api.rates.bwquery.defaultStartDate') : date($bwatch->created_at, 'Y-m-d');
                $conditions['projectid'] = $bwrule->project_id;
                $conditions['queries'] = explode('&', self::normalizeData($bwrule->queries));
                $conditions['domainid'] = $bwrule->domain_id;
                $conditions['datamark'] = $bwrule->datamark;
                $conditions['pos'] = $bwrule->param1;
                $conditions['neg'] = $bwrule->param2;
                $conditions['ntr'] = $bwrule->param3;
                //$data['categories'] = $bwrule->categories;
                $conditions['action'] = $bwrule->action;
                $conditions['page'] = 0;
                $conditions['pageSize'] = Config::get('settings.api.rates.bwquery.pageSize'); //maxBWApiAllows
                $conditions['cache'] = 1; //bypass CacheOption

                $conditions['startDate'] = Config::get('settings.api.rates.bwquery.defaultStartDate');
                $conditions['endDate'] = date('Y-m-d');

                switch ($bwrule->action):

                    case "sentiment":
                        $conditions['sentiment'] = $bwrule->sentiment;
                        break;

                    case "delete":
                        $conditions['delete'] = $bwrule->delete;
                        break;

                    case "tag":
                        $conditions['tags'] = self::normalizeData($bwrule->tags);
                        break;

                endswitch;

                foreach ($conditions['queries'] as $query) {

                    echo $query . ' starting.. <br>';
                    $conditions['queryId'] = $query;

                    $carbon = new \Carbon\Carbon();
                    $carbon->timezone('Europe/Istanbul');

                    //check if the query exist
                    $q = BwQueries::where('query_id', '=', $query)->first();

                    //if query not exist record it
                    if (!$q) {
                        $q = new BwQueries();
                        $q->account_id = $conditions['accountid'];
                        $q->project_id = $conditions['projectid'];
                        $q->query_id = $conditions['queryId'];
                        $q->cycle = 0;
                        $q->save();
                        echo $conditions['queryId'] . ' not found / recorded.. <br>';
                    }
                }

                continue;

//                //Call mentions:
//                $message = ['Rule[' . $bwrule->id . ']', 'conditions' => $conditions, 'time' => date("Y-m-d h:i:s")];
//
//                $client->sendMessage(array(
//                    'QueueUrl' => Config::get('settings.api.aws.sqs.QueueUrl'),
//                    'MessageBody' => json_encode($message),
//                ));
            } catch (Exception $e) {
                print $e->getMessage();
            }

            $bwrule->last_queue_time = date('Y-m-d h:i:s');
            $bwrule->update();

        endforeach;

        return Response::json('Number of: ' . count($bwrules) . ' rules queued.');
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
