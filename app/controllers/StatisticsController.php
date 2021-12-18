<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class StatisticsController extends \BaseController {

    protected $statics;

    /**
     * Class StatisticsController
     */
    public function __construct(ModelRepositoryInterface $statics) {
        $this->statics = $statics;
        $this->user = Auth::user(); //Current User
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index() {
        $domains = Account::find($this->user->account_id)->domains()->get();

        if (Input::get('domain'))
            $defaultDomain = Domain::where('id', '=', Input::get('domain_id'))->where('account_id', '=', $this->user->account_id)->first();
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

        return View::make('statistics.index', compact('domainLists', 'defaultDomain'));
    }


    /**
     * @param $cacheKeyStr
     * @param $result
     * @param $cacheTime
     */
    protected function setCache($cacheKeyStr, $result, $cacheTime) {
        if (Config::get('settings.analysis.cache_forever', false)) {
            Cache::forever($cacheKeyStr, $result);
        } else {
            if (Config::get('settings.analysis.cache_time')) {
                Cache::put($cacheKeyStr, $result, $cacheTime);
            }
        }
    }

}
