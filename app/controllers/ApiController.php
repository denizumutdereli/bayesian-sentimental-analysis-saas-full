<?php

use Yandex\Translate\Translator;
use Yandex\Translate\Exception;

class ApiController extends \BaseController {

    protected $statusCode = 401;
    protected $user;
    protected static $bw_main = 'https://newapi.brandwatch.com/';

    public function __construct() {

        $this->throttler = new RateLimitController();
        $this->throttler->limit();

        $this->request = Request::instance();

        $this->param = str_replace(url() . '/' . Config::get('settings.api.version') . '/', '', $this->request->url());
        $this->rates = Config::get('settings.api.rates');
        if (!isset($this->rates[$this->param]))
            $this->param = 'default';
    }

    /**
     * Determine if the current user is a guest.
     *
     * @static
     * @return bool
     */
    public function auth() {

        if (RateLimitController::$headers['X-RateLimit-Remaining'] <= 0) {
            return self::setStatusCode(429)->respondWithError(['error' => 'Rate Limit Exceeded.',
                        'X-RateLimit-Reset' => RateLimitController::$headers['X-RateLimit-Reset'],
                        'X-RateLimit-Reset-Ttl' => RateLimitController::$headers['X-RateLimit-Reset-Ttl']]);
        }

        $rules = array(
            'api_key' => 'required',
            'api_secret' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return self::setStatusCode(403)->respondWithError(['error' => 'Missing parameters!', 'parameters' => $rules]);
        }

        $this->account = Account::where('api_key', '=', Input::get('api_key'))
                ->where('api_secret', '=', Input::get('api_secret'))
                ->where('is_active', '=', '1')
                ->first();

        if (!$this->account) {
            return self::setStatusCode(401)->respondWithError(['error' => 'Bad credentials!']);
        } else {
            if ($this->account->api == 0) {
                return self::setStatusCode(401)->respondWithError(['error' => 'Api access is not permited to this account. Please contact with your account executive!']);
            } else {

                //Record the request
                \Ping::create(
                        [
                            'account_id' => $this->account->id,
                            'section' => $this->param,
                            'amount' => 1
                        ]
                );

                $access_token = md5('api:' . $this->param . ':' . $this->account->id);

                $this->account->access_token = $access_token;
                $this->account->save();

                return self::setStatusCode(200)->respondWithSuccess(['access_token' => $access_token]);
            }
        }
    }

    /**
     * check sentimental
     * ANY /
     *
     * @return Response
     */
    public function check() {

        if (RateLimitController::$headers['X-RateLimit-Remaining'] <= 0) {
            return self::setStatusCode(429)->respondWithError(['error' => 'Rate Limit Exceeded.',
                        'X-RateLimit-Reset' => RateLimitController::$headers['X-RateLimit-Reset'],
                        'X-RateLimit-Reset-Ttl' => RateLimitController::$headers['X-RateLimit-Reset-Ttl']]);
        }

        $rules = array(
            'access_token' => 'required',
            'domain_secret' => 'required',
            'text' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return self::setStatusCode(403)->respondWithError(['error' => 'Missing parameters!', 'parameters' => $rules]);
        } else {

            if (Input::get('access_token')) {
                $this->account = Account::where('access_token', '=', Input::get('access_token'))->first();
                if (!$this->account) {
                    return self::setStatusCode(401)->respondWithError(['error' => 'Account info not found! Please try to re-authanticate.']);
                }
            } else {
                return self::setStatusCode(401)->respondWithError(['error' => 'Access Token not found or expired!']);
            }

            $domain = Domain::where('domain_secret', '=', Input::get('domain_secret'))->where('account_id', '=', $this->account->id)->first();
            if (!$domain) {
                return self::setStatusCode(401)->respondWithError(['error' => 'Domain not found!']);
            } else {

                //everything is ok. now create access_token and cache for 10 minutes.
                $domain_token = md5($domain->access_secret . ':' . $this->account->id);

                if (Cache::has($domain_token)) {
                    Cache::get($domain_token);
                } else {
                    Cache::put($domain_token, $domain->id, Config::get('settings.api.rates.' . $this->param . '.period'));
                }
            }

            $domain = Cache::get($domain_token);

            if (!$domain) {
                return self::setStatusCode(401)->respondWithError(['error' => 'Domain Token not found or expired!']);
            } else {
                $domain = Domain::find($domain);

                $result = Bayes::check(Input::get('text'), 'upload', $domain->id, 0);

                if (sizeof($result['badwords']) > 0) {
                    $result['positive'] = 0;
                    $result['negative'] = 100;
                    $result['neutral'] = 0;
                }

                if ($result) {

                    //Record the request
                    \Ping::create(
                            [
                                'account_id' => $this->account->id,
                                'section' => $this->param,
                                'amount' => 1
                            ]
                    );

                    //Only available in API
                    //YandexTranslate
                    #$translator = new Translator(Config::get('settings.api.yandex.api_key')); ->dont forget to replace api keys and its going to use SSL

                    #$text = Input::get('text');
                    #$result['language'] = $translator->detect($text);

                    return self::setStatusCode(200)->respondWithSuccess($result);
                } else {
                    return self::setStatusCode(500)->respondWithError(['error' => 'System error!']);
                }
            }
        }
        return self::setStatusCode(500)->respondWithError(['error' => 'System error!']);
    }

    /**
     * check pos-tagging
     * ANY /
     *
     * @return Response
     */
    public function postag() {

        if (RateLimitController::$headers['X-RateLimit-Remaining'] <= 0) {
            return self::setStatusCode(429)->respondWithError(['error' => 'Rate Limit Exceeded.',
                        'X-RateLimit-Reset' => RateLimitController::$headers['X-RateLimit-Reset'],
                        'X-RateLimit-Reset-Ttl' => RateLimitController::$headers['X-RateLimit-Reset-Ttl']]);
        }

        $rules = array(
            'access_token' => 'required',
            'text' => 'required',
            'type' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return self::setStatusCode(403)->respondWithError(['error' => 'Missing parameters!', 'parameters' => $rules]);
        } else {

            if (Input::get('access_token')) {
                $this->account = Account::where('access_token', '=', Input::get('access_token'))->first();
                if (!$this->account) {
                    return self::setStatusCode(401)->respondWithError(['error' => 'Account info not found! Please try to re-authanticate.']);
                }
            } else {
                return self::setStatusCode(401)->respondWithError(['error' => 'Access Token not found or expired!']);
            }

            //Record the request
            \Ping::create(
                    [
                        'account_id' => $this->account->id,
                        'section' => $this->param,
                        'amount' => 1
                    ]
            );


            $google = new \GoogleController();
            $result = (array) $google->postag();

            if (isset($result['error'])) {
                return self::setStatusCode(401)->respondWithError($result['error']);
            } else {
                return self::setStatusCode(200)->respondWithSuccess($result);
            }
        }
        return self::setStatusCode(500)->respondWithError(['error' => 'System error!']);
    }

    /**
     * check images
     * ANY /
     *
     * @return Response
     */
    public function images() {

        if (RateLimitController::$headers['X-RateLimit-Remaining'] <= 0) {
            return self::setStatusCode(429)->respondWithError(['error' => 'Rate Limit Exceeded.',
                        'X-RateLimit-Reset' => RateLimitController::$headers['X-RateLimit-Reset'],
                        'X-RateLimit-Reset-Ttl' => RateLimitController::$headers['X-RateLimit-Reset-Ttl']]);
        }

        $rules = array(
            'access_token' => 'required',
            'type' => 'required',
            'image' => 'required|max:20000|mimes:jpg,jpeg,bmp,png'
        );

        $validator = Validator::make(Input::all(), $rules);

        //$validator->fails() = false;

        if ($validator->fails()) {
            $messages = $validator->messages();
            return self::setStatusCode(403)->respondWithError(['error' => 'Missing parameters!', 'parameters' => $rules]);
        } else {

            if (Input::get('access_token')) {
                $this->account = Account::where('access_token', '=', Input::get('access_token'))->first();
                if (!$this->account) {
                    return self::setStatusCode(401)->respondWithError(['error' => 'Account info not found! Please try to re-authanticate.']);
                }
            } else {
                return self::setStatusCode(401)->respondWithError(['error' => 'Access Token not found or expired!']);
            }

            //Record the request
            \Ping::create(
                    [
                        'account_id' => $this->account->id,
                        'section' => $this->param,
                        'amount' => 1
                    ]
            );

            $google = new \GoogleController();
            $result = (array) $google->imageCheck();

            if (isset($result['error'])) {
                return self::setStatusCode(401)->respondWithError($result['error']);
            } else {
                return self::setStatusCode(200)->respondWithSuccess($result);
            }
        }
        return self::setStatusCode(500)->respondWithError(['error' => 'System error!']);
    }

    /**
     * check context
     * ANY /
     *
     * @return Response
     */
    public function context() {

        if (RateLimitController::$headers['X-RateLimit-Remaining'] <= 0) {
            return self::setStatusCode(429)->respondWithError(['error' => 'Rate Limit Exceeded.',
                        'X-RateLimit-Reset' => RateLimitController::$headers['X-RateLimit-Reset'],
                        'X-RateLimit-Reset-Ttl' => RateLimitController::$headers['X-RateLimit-Reset-Ttl']]);
        }

        $rules = array(
            'access_token' => 'required',
            'type' => 'required',
            'image' => 'required|max:20000|mimes:jpeg,bmp,png'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return self::setStatusCode(403)->respondWithError(['error' => 'Missing parameters!', 'parameters' => $rules]);
        } else {

            if (Input::get('access_token')) {
                $this->account = Account::where('access_token', '=', Input::get('access_token'))->first();
                if (!$this->account) {
                    return self::setStatusCode(401)->respondWithError(['error' => 'Account info not found! Please try to re-authanticate.']);
                }
            } else {
                return self::setStatusCode(401)->respondWithError(['error' => 'Access Token not found or expired!']);
            }

            //Record the request
            \Ping::create(
                    [
                        'account_id' => $this->account->id,
                        'section' => $this->param,
                        'amount' => 1
                    ]
            );

            $clarifia = new \ClarifiaController();
            $result = (array) $clarifia->check();

            if (isset($result['error'])) {
                return self::setStatusCode(401)->respondWithError($result['error']);
            } else {
                return self::setStatusCode(200)->respondWithSuccess($result);
            }
        }
        return self::setStatusCode(500)->respondWithError(['error' => 'System error!']);
    }

    /**
     * Determine if the current user is a guest.
     *
     * @static
     * @return bool
     */
    public function bwauth() { //BWController içine Alınmalı!!
        if (RateLimitController::$headers['X-RateLimit-Remaining'] <= 0) {
            return self::setStatusCode(429)->respondWithError(['error' => 'Rate Limit Exceeded.',
                        'X-RateLimit-Reset' => RateLimitController::$headers['X-RateLimit-Reset'],
                        'X-RateLimit-Reset-Ttl' => RateLimitController::$headers['X-RateLimit-Reset-Ttl']]);
        }

        $rules = array(
            'access_token' => 'required',
            'username' => 'required',
            'password' => 'required'
        );


        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return self::setStatusCode(403)->respondWithError(['error' => 'Missing parameters!', 'parameters' => $rules]);
        }

        if (Input::get('access_token')) {
            $this->account = Account::where('access_token', '=', Input::get('access_token'))->first();
            if (!$this->account) {
                return self::setStatusCode(401)->respondWithError(['error' => 'Account info not found! Please try to re-authanticate.']);
            }
        } else {
            return self::setStatusCode(401)->respondWithError(['error' => 'Access Token not found or expired!']);
        }

        if (!Auth::user())
            $this->user = User::where('account_id', '=', $this->account->id)->first();
        else
            $this->user = Auth::user();


        //Record the request
        \Ping::create(
                [
                    'account_id' => $this->account->id,
                    'section' => $this->param,
                    'amount' => 1
                ]
        );

        //everything is ok. now create access_token and cache for 10 minutes.

        $continue = TRUE;

        $bwatch = Bwatch::where('account_id', '=', $this->account->id)
                ->where('username', '=', Input::get('username'))
                ->first();

        if ($bwatch) {
            $carbon = new \Carbon\Carbon();
            $carbon->timezone('Europe/Istanbul');
            $start = $carbon->parse($bwatch->updated_at);
            $totalMinnutes = $carbon->now()->diffInMinutes($start);
            $access_time = $bwatch->expires_in / 60;
            if ($bwatch && (floor($access_time) > $totalMinnutes)) {
                return self::setStatusCode(200)->respondWithSuccess($bwatch->json);
            } else {
                $continue = FALSE;
            }
        } else {
            $continue = FALSE;
        }

        if ($continue == FALSE) { {

                //BW Connection
                $api = new HttpController();
                $api->url = self::$bw_main;
                $api->method = 'GET';
                $api->page = 'oauth/token';
                $api->params = [
                    'username' => Input::get('username'),
                    'password' => Input::get('password'),
                    'grant_type' => 'api-password',
                    'client_id' => 'brandwatch-api-client'];

                $response = $api->call();

                if ($response['statusCode'] != 200) {

                    return self::setStatusCode(401)->respondWithError($response);
                }

                if ($response['status'] === TRUE) { //Connected
                    $response = json_decode($response['data']);

                    $bw = Bwatch::firstOrNew(['username' => Input::get('username')]);
                    $bw->account_id = $this->user->account_id;
                    $bw->user_id = $this->user->id;
                    $bw->username = Input::get('username');
                    $bw->bw_token = $response->access_token;
                    $bw->expires_in = $response->expires_in;
                    $bw->status = 1;
                    $bw->is_active = 1;
                    $bw->token_type = $response->token_type;
                    $bw->scope = $response->scope;
                    $bw->created_by = $this->user->id;
                    $bw->updated_by = $this->user->id;


                    //So far we have scuccessfully got the bw access token now checking for the client info;
                    $api = new HttpController();
                    $api->url = self::$bw_main;
                    $api->method = 'GET';
                    $api->page = 'user';
                    $api->params = [
                        'access_token' => $bw->bw_token];

                    $response = $api->call();

                    if ($response['statusCode'] != 200) {
                        return self::setStatusCode(401)->respondWithError($response);
                    }

                    if ($response['status'] === TRUE) { //Connected
                        $response = json_decode($response['data']);
                        $bw->json = json_encode($response);

                        if ($response->uiRole != 'regular' && $response->uiRole != 'admin') {
                            return self::setStatusCode(401)->respondWithError($response);
                        } else if ($response->enabled != 1) {
                            return self::setStatusCode(401)->respondWithError($response);
                        } else {
                            $api = new HttpController();
                            $api->url = self::$bw_main;
                            $api->method = 'GET';
                            $api->page = 'client';
                            $api->params = [
                                'access_token' => $bw->bw_token];

                            $response = $api->call();

                            if ($response['statusCode'] != 200) {
                                return self::setStatusCode(401)->respondWithError($response);
                            } else if ($response['status'] === TRUE) {

                                $response = json_decode($response['data']);
                                $bw->client_id = $response->id;
                                $bw->client_name = $response->name;
                                $bw->client_json = serialize($response);
                                if ($bw->save()) {
                                    return self::setStatusCode(200)->respondWithSuccess($bw->bw_token);
                                }
                            } else {
                                return self::setStatusCode(401)->respondWithError($response);
                            }
                        }
                    }

                    return self::setStatusCode(200)->respondWithSuccess($response);
                } else {
                    $response = json_decode($response['error']);
                    return self::setStatusCode(401)->respondWithError($response);
                }
            }
        }
    }

    /**
     * BW Query
     *
     * @static
     * @return bool
     */
    public function bwquery() {

        if (RateLimitController::$headers['X-RateLimit-Remaining'] <= 0) {
            return self::setStatusCode(429)->respondWithError(['error' => 'Rate Limit Exceeded.',
                        'X-RateLimit-Reset' => RateLimitController::$headers['X-RateLimit-Reset'],
                        'X-RateLimit-Reset-Ttl' => RateLimitController::$headers['X-RateLimit-Reset-Ttl']]);
        }

        $rules = array(
            'access_token' => 'required',
            'bw_token' => 'required',
            'endpoint' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();
            return self::setStatusCode(403)->respondWithError(['error' => 'Missing parameters!', 'parameters' => $rules]);
        }

        if (Input::get('access_token')) {
            $this->account = Account::where('access_token', '=', Input::get('access_token'))->first();
            if (!$this->account) {
                return self::setStatusCode(401)->respondWithError(['error' => 'Account info not found! Please try to re-authanticate.']);
            }
        } else {
            return self::setStatusCode(401)->respondWithError(['error' => 'Access Token not found or expired!']);
        }

        if (!Auth::user())
            $this->user = User::where('account_id', '=', $this->account->id)->first();
        else
            $this->user = Auth::user();


        //Record the request
        \Ping::create(
                [
                    'account_id' => $this->account->id,
                    'section' => $this->param,
                    'amount' => 1
                ]
        );

        $query_cache = md5(serialize(Input::all()));

        if (Cache::has($query_cache)) {
            return self::setStatusCode(200)->respondWithSuccess(['data' => Cache::get($query_cache)]);
        } else {

            //Record the request
            \Ping::create(
                    [
                        'account_id' => $this->account->id,
                        'section' => $this->param,
                        'amount' => 1
                    ]
            );

            $continue = TRUE;

            $bwatch = Bwatch::where('account_id', '=', $this->account->id)
                    ->where('bw_token', '=', Input::get('bw_token'))
                    ->first();

            if ($bwatch) {
                $carbon = new \Carbon\Carbon();
                $carbon->timezone('Europe/Istanbul');
                $start = $carbon->parse($bwatch->updated_at);
                $totalMinnutes = $carbon->now()->diffInMinutes($start);
                $access_time = $bwatch->expires_in / 60;
                if ($bwatch && (floor($access_time) > $totalMinnutes)) {


                    //BW Connection
                    $api = new HttpController();
                    $api->url = self::$bw_main;
                    $api->method = Request::method();
                    $api->page = Input::get('endpoint') ? Input::get('endpoint') : '';
                    $api->params = Input::all();
                    $api->params['access_token'] = $bwatch->bw_token;

                    unset($api->params['bw_token']);
                    unset($api->params['endpoint']);
                    unset($api->params['query']);
                    // unset($api->params['page']);

                    $response = $api->call();

                    if ($response['statusCode'] != 200) {
                        return self::setStatusCode(401)->respondWithError($response);
                    }

                    if ($response['status'] === TRUE) { //Connected
                        $response = json_decode($response['data']);
                        //Cache::put($query_cache, $response, 180); //5sec.cache - loop restriction
                        return self::setStatusCode(200)->respondWithSuccess($response);
                    } else {
                        $errorCode = $response['statusCode'];
                        $response = $response['error'];
                        return self::setStatusCode(401)->respondWithError($response);
                    }
                } else {
                    return self::setStatusCode(401)->respondWithError('BW user permission has expired.');
                }
            } else {
                return self::setStatusCode(401)->respondWithError('BW user not found.');
            }
        }
    }

    /**
     * Gets the value of statusCode.
     *
     * @return mixed
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Sets the value of statusCode.
     *
     * @param mixed $statusCode the status code
     *
     * @return self
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param  string $message
     *
     * @return mixed
     */
    public function respondNotFound($message = 'Not Found') {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * @param        $data
     * @param  array $headers
     *
     * @return mixed
     */
    public function respond($data) {
        return Response::json($data, 200, RateLimitController::$headers);
    }

    /**
     * @param  $data
     *
     * @return mixed
     */
    public function respondWithError($data) {

        self::checkBadRequestFrequency();

        $return = [
            'status' => false,
            'statusCode' => $this->getStatusCode()
        ];

        $return['data'] = $data;

        return $this->respond($return);
    }

    /**
     * @param  $data
     *
     * @return mixed
     */
    public function respondWithSuccess($data) {
        $return = [
            'status' => true,
            'statusCode' => $this->getStatusCode()
        ];

        $return['data'] = $data;

        return $this->respond($return);
    }

    /**
     * @Cache loop
     * 
     * @return mixed
     */
    public function checkBadRequestFrequency() {

        $ip = Request::getClientIp(true);
        if (Cache::has($ip . 'badrequest')) {
            $try = Cache::get($ip . 'badrequest');

            if ($try >= Config::get('settings.api.badrequest.timetowait')) {
                $return = [
                    'status' => false,
                    'statusCode' => $this->getStatusCode()
                ];

                $return['data']['error'] = ' You are blocked from accessing the application due to too many failed login attempts! Please try again later.';
                print(json_encode($return));
                exit;
            } else {
                Cache::put($ip . 'badrequest', $try + 1, Config::get('settings.api.badrequest.timetowait'));
            }
        } else {
            Cache::put($ip . 'badrequest', 1, Config::get('settings.api.badrequest.timetowait'));
        }
    }

    /**
     * @param Instance
     * 
     * @return mixed
     */
    public function instance() {
        $request = Request::instance();
        $param = str_replace(url() . '/' . Config::get('settings.api.version') . '/', '', $request->url());
        return $param;
    }

}
