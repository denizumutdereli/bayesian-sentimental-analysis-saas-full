<?php

use Carbon\Carbon;

/**
 * Class CommentController
 */
class CommentController extends \BaseController {

    protected $domain;

    public function __construct() {
        $this->sandbox = 1; //Sandbox Live
        $this->user = Auth::user(); //Current User
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index() {
                
        //Select user domains.

        $domains = Account::find($this->user->account_id)->domains()->get();

        if (Input::get('domain'))
            $defaultDomain = Domain::where('id', '=', Input::get('domain'))->where('account_id', '=', $this->user->account_id)->first();
        else
            $defaultDomain = Domain::where('is_default', '=', 1)->where('account_id', '=', $this->user->account_id)->first();

        if (!$domains OR !$defaultDomain) {
            Notification::danger('İşlem yapılabilmesi için en az bir adet domain bulunmalıdır!');
            return Redirect::to('domain');
        }

        $domainLists = array();

        foreach ($domains as $val => $domain) {
            $domainLists[$domain->id] = sprintf('%s (%s)', $domain->name, calculate_learning_percent($domain->id));
        }

        //DomainTags
        $tags = $defaultDomain->getTags();
        if ($tags)
            $tags = \Tag::whereIn('id', $tags)->lists('name', 'id');
        else
            $tags = array();

        return View::make('comments.index', compact('domainLists', 'defaultDomain', 'tags'));
    }

    /**
     * Display a listing of the resource.
     * GET /comment
     *
     * @param array $result
     * @return Response
     */
    public function search($result = array()) {
        
        $cacheKey = array();
        $cacheTime = Config::get('settings.comments.cache_time', 0); // minutes
//        $expiresAt = Carbon::now()->addMinutes($cacheTime);

        $q = DB::table('comments');

        $domainId = Input::has('domain_id') ? Input::get('domain_id') : Domain::getDefault()->id;

        $domain = Domain::find($domainId);

        if ($domain->account_id != $this->user->account_id) {
            $data['error'] = 'Domain bulunamadı!';
            return Response::json($data);
        }

        //get domain settings 
        $settings = json_decode($domain->settings, true);

        if (!$domain) {
            $data['error'] = 'Domain bulunamadı!';
            return Response::json($data);
        }

        if(empty($settings["sources"])) {
            Notification::danger('Bu domaine daha önce eklenmiş kaynak dosyaları bulunamadı!');
            Redirect::to('domain/'.$domainId.'/edit');
        }
        
        $q->whereIn('source_id', $settings["sources"]);

        $cacheKey['domain'] = $domainId;

        $count = ($this->adjustedFilters()) ? 200 : Input::get('count', 20);
        $q->take($count);
        $cacheKey['count'] = $count;

        if (Input::has('is_published')) {
            switch (Input::get('is_published')) {
                case '1': // yayınlanmış
                    $q->where('is_published', 1);
                    $cacheKey['is_published'] = 'published';
                    break;
                case '2': // yayınlanmamış
                    $q->where('is_published', 0);
                    $cacheKey['is_published'] = 'unpublished';
                    break;
                default:
                    $cacheKey['is_published'] = 'all';
                    break;
            }
        }

        if (Input::has('is_processed')) {
            switch (Input::get('is_processed')) {
                case '1': // işlenmiş tüm
                    $q->whereIn('id', function ($query) use ($domainId) {
                        $query->select('source_id')->from(with(new Sentimental())->getTable())->where('domain_id', $domainId);
                    });
                    $cacheKey['is_processed'] = 'processed';
                    break;
                case '2': // işlenmiş olumlu
                    $q->whereIn('id', function ($query) use ($domainId) {
                        $query->select('source_id')->from(with(new Sentimental())->getTable())->where('domain_id', $domainId)->where('state', 1);
                    });
                    $cacheKey['is_processed'] = 'processed:positive';
                    break;
                case '3': // işlenmiş olumsuz
                    $q->whereIn('id', function ($query) use ($domainId) {
                        $query->select('source_id')->from(with(new Sentimental())->getTable())->where('domain_id', $domainId)->where('state', -1);
                    });
                    $cacheKey['is_processed'] = 'processed:negative';
                    break;
                case '4': // işlenmiş nötr
                    $q->whereIn('id', function ($query) use ($domainId) {
                        $query->select('source_id')->from(with(new Sentimental())->getTable())->where('domain_id', $domainId)->where('state', 0);
                    });
                    $cacheKey['is_processed'] = 'processed:neutral';
                    break;
                case '5': // işlenmemiş
                    $q->whereNotIn('id', function ($query) use ($domainId) {
                        $query->select('source_id')->from(with(new Sentimental())->getTable())->where('domain_id', $domainId);
                    });
                    $cacheKey['is_processed'] = 'unprocessed';
                    break;
                default:
                    $cacheKey['is_processed'] = 'all';
                    break;
            }
        }

        if (Input::has('order')) {
            switch (Input::get('order')) {
                case '0': // baştan sona
                    $q->orderBy('id', 'asc');
                    $cacheKey['order'] = 'first';
                    $operator = '>';
                    break;
                case '1': // sondan başa
                    $q->orderBy('id', 'desc');
                    $cacheKey['order'] = 'last';
                    $operator = '<';
                    break;
                case '2': // karışık
                    $q->orderBy(DB::raw('RAND()'));
                    $cacheKey['order'] = 'mix';
                    $operator = false;
                    break;
            }
        }

        // get items for search query
        if (Input::get('search')) {
            $q->whereRaw('MATCH (text) AGAINST (? IN BOOLEAN MODE)', array(trim(Input::get('search'))));
            $cacheKey['search'] = trim(Input::get('search'));
//            Cache::flush(); // clear cache for search
        }

        //fix for whitespaces. Deniz 03/01/2015
        $q->whereRaw(' length(trim(text)) > 10 ');

        if ($this->adjustedFilters()) {
            $str = sprintf('%s-%s-%s', implode(':', Input::get('slider_positive')), implode(':', Input::get('slider_negative')), implode(':', Input::get('slider_neutral')));
            $cacheKey['filters'] = $str;
        }

        if ($this->includeTags()) {
            $cacheKey['tags'] = 'tags';
        }

        $startTime = microtime(true);
        $loopCount = Config::get('settings.loop.comment');
        while ($loopCount > 0) {
            $loopCount--;

            if (Input::has('max_id') && $operator) {
                $q->where('id', $operator, Input::get('max_id', 0));
                $cacheKey['max_id'] = Input::get('max_id', 0);
            }

            // set cache key
            $cacheKeyStr = implode('-', $cacheKey);

            if (Cache::has($cacheKeyStr)) {
                // get data from cache
                $comments = Cache::get($cacheKeyStr);
            } else {
                // get data from db
                $comments = $q->get();

                //return Response::json($comments);
                // put data cache 10 minutes
                if (Config::get('settings.comments.cache_time')) {
                    Cache::put($cacheKeyStr, $comments, $cacheTime);
                }
            }

            if (!$comments) {
                $endTime = microtime(true);
                $elapsedTime = $endTime - $startTime;
                return $this->searchResults($result, $domainId, $loopCount, $elapsedTime, $cacheKeyStr);
            }

            foreach ($comments as $comment) {
                $comment->created_at = date("D M d H:m:s +0000 Y", strtotime($comment->created_at));
            }

            // get the bayes results.
            $result = array_merge($result, $this->bayesProcess($comments, $domainId));

            if (count($result) < (int) Input::get('count', 20)) {
                Input::merge(array('max_id' => end($comments)->id));
            } else {
                $endTime = microtime(true);
                $elapsedTime = $endTime - $startTime;
                return $this->searchResults($result, $domainId, $loopCount, $elapsedTime, $cacheKeyStr);
            }
        }
    }

    /**
     * @param array $comments
     * @param int $domainId
     * @return array
     */
    protected function bayesProcess(array $comments, $domainId = 1) {
        $result = array();

        foreach ($comments as $comment) {
            $comment_on_db = DB::table('sentimental')
                    ->select('*')
                    ->where('source', '=', 'comment')
                    ->where('source_id', '=', $comment->id)
                    ->where('domain_id', '=', $domainId)
                    ->first();

            $comment->state = $comment_on_db ? (int) $comment_on_db->state : null;

            $comment->stripped_text = explode(' ', strip_tags($comment->text));

            $comment->check = Bayes::check($comment->text, 'comment', $domainId, 3);

//            if ($this->includeTags())
//                $tags_query = Input::get('include_tags');
//            else
//                $tags_query = null;
//
//            if ($this->includeTags())//if any tag category asked for  
//                $comment->word_list = Bayes::findTags($comment->text, $domainId, $tags_query);
//            else {
                $comment->word_list['badwords'] = $comment->check['badwords'];
            //}

            $comment = $this->checkFilterValues($comment);

            if ($this->adjustedFilters()) {
                $slider = array(
                    'positive' => Input::get('slider_positive'),
                    'negative' => Input::get('slider_negative'),
                    'neutral' => Input::get('slider_neutral'),
                );

                if (is_array($slider['positive']) && is_array($slider['negative']) && is_array($slider['neutral'])) {
                    if (($comment->check['positive'] >= $slider['positive'][0] && $comment->check['positive'] <= $slider['positive'][1]) &&
                            ($comment->check['negative'] >= $slider['negative'][0] && $comment->check['negative'] <= $slider['negative'][1]) &&
                            ($comment->check['neutral'] >= $slider['neutral'][0] && $comment->check['neutral'] <= $slider['neutral'][1])
                    ) {
                        $result[] = $comment;
                    }
                }
            } else {
                $result[] = $comment;
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    protected function adjustedFilters() {
        if (Input::has('check_slider')) {
            return (int) Input::get('check_slider') ? true : false;
        }

        $sliderValues = array(
            'positive' => Input::get('slider_positive'),
            'negative' => Input::get('slider_negative'),
            'neutral' => Input::get('slider_neutral'),
        );

        foreach ($sliderValues as $slider) {
            if ($slider[0] > 0 || $slider[1] < 100) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function includeTags() {
        return (Input::has('include_tags')) ? true : false;
    }

    /**
     * @param $comment
     * @return mixed
     */
    // protected function checkFilterValues($comment)
    // {
    // $comment->check_sentimental = $comment->check;
    // if ($comment->state === -1 || count($comment->word_list['badwords'])) {
    // $comment->check["negative"] = 100;
    // $comment->check["positive"] = 0;
    // $comment->check["neutral"] = 0;
    // } elseif ($comment->state === 1) {
    // $comment->check["negative"] = 0;
    // $comment->check["positive"] = 100;
    // $comment->check["neutral"] = 0;
    // } elseif ($comment->state === 0) {
    // $comment->check["negative"] = 0;
    // $comment->check["positive"] = 0;
    // $comment->check["neutral"] = 100;
    // }
    // return $comment;
    // }

    protected function checkFilterValues($comment) {
        //$domainId = Input::has('domain_id') ? Input::get('domain_id') : Domain::getDefault()->id;

        $balance = 50;
        $trainLimit = 80;

        $comment->check_sentimental = $comment->check;

        if ($comment->state === -1) {
            $comment->check['negative'] = 100;
            $comment->check['positive'] = 0;
            $comment->check['neutral'] = 0;
            Bayes::changeState($comment->id, -1);
        } elseif (count($comment->word_list['badwords']) AND true === $this->includeTags()) {
            $comment->check['negative'] = 100;
            $comment->check['positive'] = 0;
            $comment->check['neutral'] = 0;
            Bayes::changeState($comment->id, -1);
        } elseif ($comment->state === 1) {
            $comment->check['negative'] = 0;
            $comment->check['positive'] = 100;
            $comment->check['neutral'] = 0;
        } elseif ($comment->state === 0) {
            $comment->check['negative'] = 0;
            $comment->check['positive'] = 0;
            $comment->check['neutral'] = 100;
        } elseif ($comment->check['neutral'] >= 75) {
            if ($comment->check['negative'] > $comment->check['positive']) {
                $comment->check['negative'] = $comment->check['negative'] + $balance;
            } elseif ($comment->check['negative'] < $comment->check['positive']) {
                $comment->check['positive'] = $comment->check['positive'] + $balance;
            }
            $comment->check['neutral'] = $comment->check['neutral'] - $balance;
        }
//         elseif ($comment->check['negative'] >= $trainLimit) {
//            Bayes::learn($comment->text, -1, 'comment', $comment->id, $domainId);
//        } elseif ($comment->check['positive'] >= $trainLimit) {
//            Bayes::learn($comment->text, 1, 'comment', $comment->id, $domainId);
//        }

        return $comment;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function publish() {
        $id = Input::get('comment_id');
        $comment = Comments::find($id);
        $URL = $comment->is_published ? Config::get('settings.comments.delete_url') : Config::get('settings.comments.publish_url');

        /*
          $ch = curl_init();

          //set the url, number of POST vars, POST data
          curl_setopt($ch,CURLOPT_URL, $URL);
          curl_setopt($ch,CURLOPT_POST, 1);
          curl_setopt($ch,CURLOPT_POSTFIELDS, "comment_id=".$id);
          //execute post
          $result = curl_exec($ch);
          //close connection
          $responseInfo = curl_getinfo($ch);
          curl_close($ch);
         */

        $responseInfo['http_code'] = 200;


        // for feyk return
        if (true || $responseInfo['http_code'] == 200) {
            $comment->is_published = $comment->is_published ? false : true;
            $comment->save();

            $user = Auth::user();
            UserLog::create([
                'user_id' => $user->id,
                'source' => 'comment',
                'log' => json_encode(array(
                    'text' => $comment->text,
                    'action' => $comment->is_published ? 'yayınlandı' : 'Yayından kaldırıldı',
                ))
            ]);

            return Response::json(array('status' => 'ok', 'published' => $comment->is_published));
        }

        return Response::json(array('status' => 'failed'));
    }

    /**
     * @param $result
     * @param $domainId
     * @param $loopCount
     * @param $elapsedTime
     * @param $cacheKey
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function searchResults($result, $domainId, $loopCount, $elapsedTime, $cacheKey) {
        $result = array_slice($result, 0, Input::get('count', 20));

        $dataLearned = calculate_learning_percent($domainId, true); // (int) params $domain_id, (bool) $object return parametter
        $domainData = Domain::find($domainId);

        if ($domainData->account_id != $this->user->account_id) {
            $data['error'] = 'Domain bulunamadı!';
            return Response::json($data);
        }

        $result = array(
            'result' => $result,
            'dataLearned' => $dataLearned,
            'lists' => $domainData->getNames(),
            'loopCount' => $loopCount,
            'elapsedTime' => $elapsedTime,
        );

        if (Config::get('settings.comments.cache_time')) {
            $result['cacheKey'] = $cacheKey;
        }

        return Response::json($result);
    }

}
