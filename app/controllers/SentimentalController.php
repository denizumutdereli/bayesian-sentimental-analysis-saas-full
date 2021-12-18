<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class SentimentalController extends \BaseController {

    protected $sentimental;

    public function __construct(ModelRepositoryInterface $sentimental) {
        $this->sentimental = $sentimental;
        $this->user = Auth::user(); //Current User
    }

    /**
     * Display a listing of the resource.
     * GET /sentimental
     *
     * @return Response
     */
    public function index() {
        //Select user domains.

        $domains = Domain::where('account_id', '=', $this->user->account_id)->get();

        if (Input::get('domain'))
            $defaultDomain = Domain::where('id', '=', Input::get('domain'))->where('account_id', '=', $this->user->account_id)->first();
        else
            $defaultDomain = Domain::where('is_default', '=', 1)->where('account_id', '=', $this->user->account_id)->first();

        if (!$domains OR ! $defaultDomain) {
            Notification::danger('İşlem yapılabilmesi için en az bir adet domain bulunmalıdır!');
            return Redirect::to('domain');
        }

        $domainLists = array();

        foreach ($domains as $val => $domain) {
            $domainLists[$domain->id] = sprintf('%s (%s)', $domain->name, calculate_learning_percent($domain->id));
        }

        // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

        // get items for search query
        $query = array('text', 'LIKE', '%' . trim(Input::get('q')) . '%', 'domain_id', '=', $defaultDomain->id);

        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $sentimentals = $this->sentimental->getPaginatedItems($limit, $order, $query);

        return View::make('sentimental.index', compact('sentimentals', 'domainLists', 'defaultDomain'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /sentimental/create
     *
     * @return Response
     */
    public function create() {
        if ($this->user->is_superAdmin()) {
            $domainList = Domain::all()->lists('name', 'id');
        } else {
            //Select user domains.
            $domainList = Domain::where('account_id', '=', $this->user->account_id)->lists('name', 'id');
        }

        if (!$domainList) {
            Notification::warning('Geçerli Domain bulunamadı!');
            return Redirect::to('domain');
        }

        return View::make('sentimental.create', compact('domainList'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /sentimental
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            'text' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $state = Input::get('state');
        $text = Input::get('text');
        $sourceId = Input::get('sourceId', md5(uniqid(mt_rand(), true)));
        $domainId = Input::get('domain_id');
        $accountId = Input::get('account_id');
        $userId = Input::get('user_id');
        $createdBy = Input::get('user_id');
        $updatedBy = Input::get('user_id');
        $source = 'manual';

        Bayes::learn($text, (int) $state, $source, $sourceId, $domainId); //learn($text, $state = 0, $source = '', $sourceId = null, $domainId)

        $states = Config::get('settings.states');

        $user = Auth::user();
        UserLog::create([
            'user_id' => $user->id,
            'source' => 'sentimantal',
            'log' => json_encode(array(
                'text' => $text,
                'action' => $states[$state] . ' olarak eklendi',
            ))
        ]);
    
        Notification::success('Metin kaydedildi!');

        return Redirect::to('sentimental');
    }

    /**
     * Display the specified resource.
     * GET /sentimental/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        return false;
    }

    /**
     * Show the form for editing the specified resource.
     * GET /sentimental/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        if ($this->user->is_superAdmin()) {
            $domainList = Domain::all()->lists('name', 'id');
        } else {
            //Select user domains.
            $domainList = Domain::where('account_id', '=', $this->user->account_id)->lists('name', 'id');
        }

        if (!$domainList) {
            Notification::warning('Geçerli Domain bulunamadı!');
            return Redirect::to('domain');
        }


        $sentimental = Sentimental::find($id);

        if (!$sentimental) {
            Notification::warning('Sadece size ait kayıtları görebilir ve güncelleyebilirsiniz!');
            return Redirect::to('sentimental');
        } else if (!$this->user->is_superAdmin() AND $sentimental->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait kayıtları görebilir ve güncelleyebilirsiniz!');
            return Redirect::to('sentimental');
        }

        return View::make('sentimental.edit', compact(array('domainList', 'sentimental')));
    }

    /**
     * Update the specified resource in storage.
     * PUT /sentimental/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        if (Input::has('text')) {
            $rules = array('text' => 'required');

            $validator = Validator::make(Input::all(), $rules);
            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator);
            }
        }

        $sentimental = Sentimental::find($id);

        if (!$sentimental) {
            Notification::warning('Sadece size ait kayıtları görebilir ve güncelleyebilirsiniz!');
            return Redirect::to('sentimental');
        } else if (!$this->user->is_superAdmin() AND $sentimental->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait kayıtları görebilir ve güncelleyebilirsiniz!');
            return Redirect::to('sentimental');
        }

        $text = Input::has('text') ? Bayes::cleanWord(Input::get('text')) : $sentimental->text;
        $state = Input::get('state');

        $sentimental->text = $text;
        $sentimental->state = $state;
        $sentimental->domain_id = Input::get('domain_id', 1);
        ;
        $sentimental->save();

        $user = Auth::user();

        $states = Config::get('settings.states');

        UserLog::create([
            'user_id' => $user->id,
            'source' => 'sentimental',
            'log' => json_encode(array(
                'text' => $text,
                'action' => Domain::find($sentimental->domain_id)->name . ' Domain\'ine kaydedilerek, ' . $states[$state] . ' olarak düzenlendi',
            ))
        ]);

        Notification::success('Metin düzenlendi!');

        return Redirect::to('sentimental');
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /sentimental/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        $sentimental = Sentimental::find($id);
        if (!$this->user->is_superAdmin() AND $sentimental->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait kayıtları görebilir ve güncelleyebilirsiniz!');
            return Redirect::to('sentimental');
        }

        $user = Auth::user();
        UserLog::create([
            'user_id' => $user->id,
            'source' => 'sentimental',
            'log' => json_encode(array(
                'text' => $sentimental->text,
                'action' => 'silindi',
            ))
        ]);

        $sentimental->forceDelete();

        Notification::success('Kayıt silindi.');
        return Redirect::back();
    }

}
