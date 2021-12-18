<?php

class RateLimitController extends \BaseController {

    /**
     * Default rate limit, maximum number of requests.
     */
    const DEFAULT_LIMIT = 80;

    /**
     * Default request cost per request.
     */
    const DEFAULT_COST = 1;

    /**
     * Default period which the requests are limited (minutes).
     */
    const DEFAULT_PERIOD = 10;

    //Default Headers
    public static $headers;
    public $account;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param int                       $limit   The overall request limit (defaults to 1000)
     * @param int                       $cost    The cost of the request (defaults to 1)
     *
     * @return mixed
     */
    public function limit($limit = self::DEFAULT_LIMIT, $cost = self::DEFAULT_COST) {
        $request = Request::instance();
        $param = str_replace(url() . '/' . Config::get('settings.api.version') . '/', '', $request->url());

        $rates = self::rate_settings($param); //get Rates from settings page
        //$request->setTrustedProxies(array('127.0.0.1')); // only trust proxy headers coming from the IP addresses on the array (change this to suit your needs)
        $ip = $request->getClientIp();
 
        // Rate limit by IP address and wthing option BW credentials
        if($param == 'bwauth') { $param = 'bwauth:'.Input::get('username');}
        if($param == 'bwquery') { $param = 'bwauth:'.Input::get('access_token');}
        
        
        $api_request = md5('apirate:' . $ip . ':' . $param);

        if (Cache::has($api_request)) {
            $data = Cache::get($api_request);

            $remaining = max($data['count'] - $rates['cost'], 0);
            $reset_time = $data['time'];

            self::$headers = [
                'X-RateLimit-Cost' => $rates['cost'],
                'X-RateLimit-Limit' => $rates['limit'],
                'X-RateLimit-Remaining' => max($remaining, 0),
                'X-RateLimit-Reset' => $reset_time,
                'X-RateLimit-Reset-Ttl' => max($reset_time - time(), 0)];

            $data = ['count' => $remaining, 'time' => $reset_time];

            Cache::put($api_request, $data, max(( $reset_time - time() ) / 60, 0));

            return self::$headers;
        }

        $remaining = max($rates['limit'] - $rates['cost'], 0);
        $reset_time = time() + ($rates['period'] * 60);
        $data = ['count' => $remaining, 'time' => $reset_time];
        Cache::add($api_request, $data, $rates['period']);

        // Set rate limit headers
        return self::$headers = [
            'X-RateLimit-Cost' => $rates['cost'],
            'X-RateLimit-Limit' => $rates['limit'],
            'X-RateLimit-Remaining' => max($remaining, 0),
            'X-RateLimit-Reset' => $reset_time,
            'X-RateLimit-Reset-Ttl' => max($reset_time - time(), 0)];
    }

    /**
     * rate settings
     * Internal
     *
     * @return Response
     */
    public function rate_settings($param) {

        $rates = Config::get('settings.api.rates');
        if (!isset($rates[$param]))
            $param = 'default';

        $result['limit'] = Config::get('settings.api.rates.' . $param . '.limit');
        $result['cost'] = Config::get('settings.api.rates.' . $param . '.cost');
        $result['period'] = Config::get('settings.api.rates.' . $param . '.period');
        
        
//        echo '<pre>';
//        print_r($result);exit;
        return $result;
    }

}
