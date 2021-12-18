<?php

/**
 * Class HomeController
 */
class HomeController extends BaseController {

    /**
     * @var \Illuminate\Auth\UserInterface|null
     */
    protected $user;

    /**
     *
     */
    public function __construct() {
        $this->user = Auth::user();
    
    }

    /*
      |--------------------------------------------------------------------------
      | Default Home Controller
      |--------------------------------------------------------------------------
      |
      | You may wish to use controllers instead of, or in addition to, Closure
      | based routes. That's great! Here is an example controller method to
      | get you started. To route to this controller, just add the route:
      |
      |	Route::get('/', 'HomeController@index');
      |
     */

    /**
     * @return \Illuminate\View\View
     */
    public function index() {
               
        $permissions = json_decode($this->user->permissions);

        if (count($permissions) == 0) {
            Notification::danger('<i class="fa fa-warning"></i> Sistem tarafından yetkileriniz sınırlandırıldı. Lütfen yöneticinize başvurunuz!');
        }

        if ($this->user->is_superAdmin()) {
            $domains = Domain::all()->lists('name', 'id');
        } else {
            //Select user domains.
            $domains = Domain::where('account_id', '=', $this->user->account_id)->lists('name', 'id');
        }

        if (!$domains) {
            Notification::danger('İşlem yapılabilmesi için en az bir adet domain bulunmalıdır!');
            return Redirect::to('domain');
        }

        return View::make('deck.index', compact('domains'));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addTraine() {
        $text = Input::get('text');
        $state = Input::get('state');
        $source = Input::get('source', 'manuel');
        $sourceId = Input::get('source_id', md5(uniqid(mt_rand(), true)));
        $domainId = Input::get('domain_id');

        if (!is_domain_taught($domainId)) {
            return Response::json(array('response' => 2, 'message' => 'Bu domain özel bir domaindir. Bu domaine öğretme yetkileri sahibi tarafından kapatılmıştır. Lütfen başka bir domain seçiniz.'));
        }

        $response = Bayes::learn($text, (int) $state, $source, $sourceId, (int) $domainId);

        // clear cache for new request
        Cache::flush();

        if ($response['status'] == 'old') {
            return Response::json(array('response' => -1, 'sentimental' => $response['result']->id));
        }

        $result = Bayes::check($text, $source, $domainId);

        if ($result) {
            $result['response'] = 1;
        } else {
            $result['response'] = 0;
        }

        //  add user logging data
        $user = $this->user;
        $states = [
            '-1' => 'olumsuz',
            '0' => 'nötr',
            '1' => 'olumlu'
        ];

        UserLog::create([
            'user_id' => $user->id,
            'source' => 'sentimantal',
            'log' => json_encode(array(
                'text' => $text,
                'action' => $states[$state] . ' olarak eklendi',
            ))
        ]);


        return Response::json($result);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function showAnalysis() {
        $bayes_result = Bayes::check(Input::get('text'), Input::get("source", null), Input::get("domain_id", null), 3);

        $result = $bayes_result;
        $result['dataLearned'] = calculate_learning_percent(Input::get("domain_id", 1), true);

        if ($result) {
            $result['response'] = 1;
        } else {
            $result['response'] = 0;
        }

        return Response::json($result);
    }

    public function getLearned() {
        $result = array(
            'response' => 1,
            'dataLearned' => calculate_learning_percent(Input::get('domain_id', 1), true),
            'param' => Input::all(),
        );
        return Response::json($result);
    }

    public function google() {
        
        return View::make('google.index'); 
    }

    public function googleUpdate() {
        // Create Bucket here 
        // https://cloud.google.com/storage/docs/getting-started-console#create_a_bucket
        $bucket = 'sentima';
        // Get Service account API hereL 
        // https://cloud.google.com/vision/docs/getting-started#setting_up_a_service_account
        $api_key = 'YOUR KEy';
        
        $cvurl = 'https://vision.googleapis.com/v1/images:annotate?key=' . $api_key;
        $type = 'LABEL_DETECTION'; //label, land, text, logo
//Did they upload a file...
        if ($_FILES['photo']['name']) {
            
            // if no errors...
            if (!$_FILES['photo']['error']) {
                $valid_file = true;
                if ($_FILES['photo']['size'] > (4024000)) {
                    // can't be larger than ~4 MB
                    $valid_file = false;
                    die('Your file\'s size is too large.');
                }
                // if the file has passed the test
                if ($valid_file) {
                    // convert it to base64
                    $data = file_get_contents($_FILES['photo']['tmp_name']);
                    $base64 = base64_encode($data);
                    // Create this JSON
                    $request_json = '
            {
                "requests": [
                    {
                        "image": {
                            "content":"' . $base64 . '"
                        },
                        "features": [
                            {
                                "type": "' . $type . '",
                                "maxResults": 200
                            }
                        ]
                    }
                ]
            }';
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $cvurl);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
                    $json_response = curl_exec($curl);
                    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);
                    if ($status != 200) {
                        die("Error: call to URL $cvurl failed with status $status, response $json_response, curl_error " . curl_error($curl) . ', curl_errno ' . curl_errno($curl));
                    }
                    echo '<pre>';
                    echo $json_response;
                    echo '</pre>';
                }
            } else {
                // if there is an error, set that to be the returned message
                echo 'Error';
                die('Ooops!  Your upload triggered the following error:  ' . $_FILES['photo']['error']);
            }
        }
    }

}
