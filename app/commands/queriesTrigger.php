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

class queriesTrigger extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queries:pull';
    protected static $api_main = 'http://api.codexai.com/';

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

        $this->carbon = new \Carbon\Carbon();
        $this->carbon->timezone('Europe/Istanbul');

        $credentials = new Credentials(Config::get('settings.api.aws.sqs.key'), Config::get('settings.api.aws.sqs.secret'));

        // Instantiate the SQS client with your AWS credentials
        $client = SqsClient::factory(array(
                    'credentials' => $credentials,
                    'region' => Config::get('settings.api.aws.sqs.region')
        ));

        //Get All active rules;
        $queries = BwQueries::all();

        $i = 0;

        foreach ($queries as $q) :

            $i++;
            sleep(1);

            try {
                $conditions = [];
                //$conditions['append'] = FALSE;
                //dataPrepration:
                $conditions['page'] = 0;
                $conditions['sinceId'] = 0;
                $conditions['startDate'] = Config::get('settings.api.rates.bwquery.defaultStartDate');
                $conditions['endDate'] = date('Y-m-d');

                if ($q->cycle == 1) { //not first time, previously triggered.
//                    $start = $this->carbon ->parse($q->updated_at);
//                    $totalHours =$this->carbon ->now()->diffInMinutes($start); //DUZELTILECEK
//
//                    if ($totalHours < Config::get('settings.api.rates.bwquery.queriesUpdateCycle')) { //default 4 hours
//                        return;
//                    }
                    if ($q->pulledData < $q->resultsTotal) {
                        echo 1;
                        $conditions['page'] = $q->resultsPage + 1;
                        $conditions['sinceId'] = 0;
                        $conditions['startDate'] = $this->carbon->parse($q->startDate)->format('Y-m-d');
                        $conditions['endDate'] = $this->carbon->parse($q->endDate)->format('Y-m-d');
                    } else {
                        $conditions['page'] = 0;
                        $conditions['sinceId'] = $q->maximumIdInResult;
                        $conditions['startDate'] = $this->carbon->parse($q->endDate)->format('Y-m-d');
                        $conditions['endDate'] = $this->carbon->now()->addDay()->format('Y-m-d');
                    }
                }

                $mentions = self::getMentions($q, $conditions);

                continue;
            } catch (Exception $e) {
                print $e->getMessage();
            }

            $bwrule->last_queue_time = date('Y-m-d h:i:s');
            $bwrule->update();

        endforeach;

        return Response::json('Number of: ' . count($queries) . ' rules queued.');
    }

    /*
     * Get mentions
     * response json
     * 
     */

    public function getMentions($q, $conditions) {

        if ($q) { //first run
            //Check previous errros to avoid repeating..
            if ($q->error == 1) {
                //error delays
                echo 'waiting..';

                $start = $this->carbon->parse($q->updated_at);
                $totalDuration = $this->carbon->now()->diffInMinutes($start);

                if ($totalDuration < Config::get('settings.api.badrequest.timetowait')) { //The request should wait until timeout
                    return;
                } else {

                    //Ok now its free
                    $q->error = 0;
                    $q->errorCode = '';
                    $q->errorMessage = '';
                    $q->request = serialize($conditions);
                    $q->update();
                }
            } //error exist and expecting admin to fix it before running again.
            else {

                $this->access_token = Account::find($q->account_id)->access_token;
                $this->bw_token = Bwatch::find($q->bwatch_id)->bw_token;

                if (!$this->access_token OR ! $this->bw_token) {
                    //Ping admin error logs
                    \AdminLog::create([
                        'account_id' => $q->account_id,
                        'user_id' => 0,
                        'source' => 'bwrule',
                        'created_by' => 1, //superAdmin
                        'updated_by' => 1, //SuperAdmin
                        'log' => json_encode(array(
                            'level' => 'critical',
                            'text' => 'while running query trigger, access_token or bw_token couldnt find.',
                            'request' => serialize($conditions),
                            'action' => 'BW api getMentions hatası',
                            'result' => 'Kural çalışmıyor.'
                        ))
                    ]);
                } else {

                    echo '<pre>';
                    print_r($conditions);

                    $api = new HttpController;

                    $api->url = 'http://api.codexai.com/' . Config::get('settings.api.version') . '/';
                    $api->method = 'GET';
                    $api->page = 'bwquery';
                    $api->params = [
                        'access_token' => $this->access_token,
                        'bw_token' => $this->bw_token,
                        'queryId' => $q->query_id,
                        'startDate' => $conditions['startDate'],
                        'endDate' => $conditions['endDate'],
                        'page' => $conditions['page'],
                        'pageSize' => Config::get('settings.api.rates.bwquery.pageSize'),
                        'sinceId' => $conditions['sinceId'],
                        'endpoint' => 'projects/' . $q->project_id . '/data/mentions/fulltext/'];

                    $response = $api->call();

                    echo 'Api response:';
//                    print_r($response);
//                    exit;

                    if (!isset($response['data'])) {
                        echo 'No data index on API response:<br>';
                        return;
                    } else {
                        echo 'There is data index on API response:<br>';
                    }

                    $response = json_decode($response['data']);

                    if ($response->status == FALSE) { //if we have an error reply
                        echo 'API False Error <br>';
                        $q->error = 1;
                        $q->errorCode = (@$response->data->error->errors[0]->code ? @$response->data->error->errors[0]->code : 401);
                        $q->errorMessage = (@$response->data->error->errors[0]->message ? @$response->data->error->errors[0]->message : 'Unkown error!');
                        $q->request = serialize($conditions);
                        $q->update();

                        //Ping admin error logs
                        \AdminLog::create([
                            'account_id' => $q->account_id,
                            'user_id' => 0,
                            'source' => 'bwrule',
                            'created_by' => 1, //superAdmin
                            'updated_by' => 1, //SuperAdmin
                            'log' => json_encode(array(
                                'level' => 'critical',
                                'text' => (@$response->data->error->errors[0]->message ? @$response->data->error->errors[0]->message : 'Unkown error!'),
                                'request' => serialize($conditions),
                                'action' => 'BW api getMentions hatası',
                                'result' => 'Kural çalışmıyor.'
                            ))
                        ]);

                        echo 'Its ended because of FALSE error<br>';
                        return;
//                } else {
//                    Cache::put($cache, $response, 0);
//                }
                    }

                    echo 'Pull $response->data object..<br>';
                    $response = $response->data;


                    //            echo '<pre>';
                    //            print_r($response);

                    if (count($response->results) > 0) { //there are new mentions
                        echo 'Put mentions to S3<br>';
                        $AwsS3 = $this->putMentions($response->results, $q, $conditions);
                        echo 'Im back after s3 opt..<br>';
                    } else {
                        echo 'No data to putting mentions to S3<br>';
                        $AwsS3 = TRUE; //bypassing
                        $q->cycle = 1;
                        $q->save(); //save for update date and exit
                    }

                    if ($AwsS3 === TRUE) {

                        $q->cycle = 1;
                        $q->resultsPage = $response->resultsPage;
                        $q->resultsPageSize = $response->resultsPageSize;
                        $q->resultsTotal = $response->resultsTotal;
                        $q->pulledData = count($response->results) + $q->pulledData;
                        $q->startDate = $response->startDate;
                        $q->endDate = $response->endDate;
                        $q->maximumIdInResult = $response->maximumIdInResult;
                        $q->maximumId = $response->maximumId;
                        $q->error = 0;
                        $q->errorCode = '';
                        $q->errorMessage = '';
                        //$q->request = serialize($conditions);
                        $q->save();
                        echo 'I have saved the query data and ending here..<br><hr>';
                    } else {
                        //Ping admin error logs
                        \AdminLog::create([
                            'account_id' => $q->account_id,
                            'user_id' => 0,
                            'source' => 'bwrule',
                            'created_by' => 1, //superAdmin
                            'updated_by' => 1, //SuperAdmin
                            'log' => json_encode(array(
                                'level' => 'critical',
                                'text' => 'S3 Recording Error',
                                'request' => serialize($conditions),
                                'action' => 'BW Mentions S3 Recording',
                                'result' => 'Kural çalışmıyor.'
                            ))
                        ]);
                    }
                }

                //we are all okay and put mentions to S3
                return;
            }
        }
    }

    public function putMentions($responses, $q, $conditions) {


        $dbc = @mysql_pconnect('bwmentions.cd8ylr6dtbvw.eu-west-1.rds.amazonaws.com', 'admin', 'A1980amor');
        $dbs = @mysql_select_db('bwmentions_db');

        foreach ($responses as $key):
 
            $response = $key;
            
            //$resource = new BwMentions();

            $response->queryName = mysql_real_escape_string($response->queryName);
            $response->fullText = mysql_real_escape_string($response->fullText);
            $json = mysql_real_escape_string(json_encode($response));


            $q = "insert into mentions ("
                    . "resourceId,"
                    . "queryId,"
                    . "queryName,"
                    . "mention) "
                    . "values("
                    . "'" . $response->resourceId . "',"
                    . "'" . $response->queryId . "',"
                    . "'" . $response->queryName . "',"
                    . "'" . $response->fullText . "')";



//            $data = [
//                'resourceId' => $response->resourceId,
//                'queryId' => $response->queryId,
//                'queryName' => $response->queryName,
//                'mention' => $response->fullText,
//                'added' => $response->added,
//                'pageType' => $response->pageType,
//                'domain' => $response->domain,
//                'author' => $response->author,
//                //'mediaUrls' => ($response->mediaUrls[0] ? $response->mediaUrls[0] : '' ),
//                'url' => $response->url,
//                'json' => json_encode($response),
//            ];

            if (@mysql_query($q, $dbc)) {
                $response =[];
                continue;
            } else {
                return FALSE;
            }



        endforeach;
        return TRUE;
    }

    public function reportError($data) {
        echo 'error:<br>';
        $data['time'] = (new \DateTime())->format('Y-m-d H:i:s'); //timestamp
        $path = storage_path() . '/bwatch/errors/' . $data['queryId'] . '.log';

        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true);
        }
        File::put($path . date('dmyhi') . '.json', $data);
        return $result = ['status' => FALSE];
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
