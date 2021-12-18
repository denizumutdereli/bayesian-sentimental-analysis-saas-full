<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class DomainController extends \BaseController {

    protected $domain;

    public function __construct(ModelRepositoryInterface $domain) {
        $this->domain = $domain;
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     * GET /domain
     *
     * @return Response
     */
    public function index() {

        // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

        // get items for search query
        $query = array('name', 'LIKE', '%' . Input::get('q') . '%');

        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $domains = $this->domain->getPaginatedItems($limit, $order, $query);
        //dd($domains->all());

        return View::make('domains.index', compact('domains'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /domain/create
     *
     * @return Response
     */
    public function create() {

        $accounts = $this->user->account()->lists('name', 'id');

        ##Sources & Uploads Section
        //checking if any source cat exists?
        $sources = Account::find($this->user->account_id)->sources()->lists('name', 'id');

        $account = $this->user->account()->lists('name', 'id');

        if (!$sources) { //Source cat does not exists.
            Notification::warning('Domain ekleyebilmeniz için öncelikle, Kaynak Kategorisi eklemelisiniz!');
            return Redirect::to('source/create');
        }

        $tags = Account::find($this->user->account_id)->tags()->lists('name', 'id');
        if (!$tags)
            $tags = array();

        $models = Config::get('settings.bayes.models');

        return View::make('domains.create', compact('account', 'sources', 'tags', 'models'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /domain
     *
     * @return Response
     */
    public function store() {

        $rules = array(
            'name' => 'required',
            'rules' => 'required',
            'sources' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            Notification::warning('Domain ekleyebilmeniz için İsim, Kaynaklar ve Kuralları eklemelisiniz!');
            return Redirect::back()->withInput()->withErrors($validator)->with(Input::get('account_id'));
        }

        $domain = new Domain;

        $domain->account_id = $this->user->account_id;
        $domain->user_id = $this->user->id;

        $domain->name = uniqueName($domain, Input::get('name'));
        $adjustment = Input::get("adjustment", array(
                    "0" => "0",
                    "-1" => "0",
                    "1" => "0"
                        )
        );

        if (($adjustment[1] + $adjustment[-1]) < 100) {
            $adjustment[0] = 100 - ($adjustment[1] + $adjustment[-1]);
        }

        $domain->settings = json_encode(array(//Turkish Default Logic
            "rules" => Input::get('rules', array(
                "unigram", "bigram"
                    ), "bigram"
            ),
            "names" => Input::get("names", array(
                "0" => "Nötr",
                "-1" => "Olumsuz",
                "1" => "Olumlu"
                    )
            ),
            "model" => Input::get('model', Config::get('settings.bayes.models')[0]), //default acoustic
            "adjustment" => $adjustment,
            "balance" => Input::get('balance', 0),
            "sources" => Input::get('sources'),
            "tags" => Input::get('tags')
        ));

        $userDomains = Account::find($domain->account_id)->domains()->lists('id');

        if (count($userDomains) == 0)
            $domain->is_default = true;
        elseif (Input::has('is_default')) {
            if ($userDomains->update(array('is_default' => '0'))) {
                $domain->is_default = true;
            }
        }

        $domain->sense = (Input::get('sense')) ? Input::get('sense') : 0;
        $domain->created_by = $this->user->id;
        $domain->updated_by = $this->user->id;
        $domain->is_private = Input::has('is_private');
        $domain->domain_secret = Str::random(32);
        $domain->save();

        Notification::success('Domain kaydedildi!');

        return Redirect::to('domain');
    }

    /**
     * Display the specified resource.
     * GET /domain/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $domain = Domain::find($id);

        if ($domain->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait Dommain bilgilerini görebilirsiniz!');
            return Redirect::to('domain');
        }


        return View::make('domains.show', $domain);
    }

    /**
     * Ajax Request - has permission exception
     * Display the related tags.
     * POST /domain/tags
     *
     * @param  int  $id
     * @return Response
     */
    public function tags() {
        if (!Request::ajax()) {
            return Response::make('Unauthorized', 401);
        }
        $result['response'] = 0;

        if (!Input::get('domain_id'))
            Response::json($result);

        $domain = Domain::find(Input::get('domain_id'));

        if (!$domain)
            Response::json($result);
        else {
            $tags = $domain->getTags();

            if ($tags) {
                $tags = \Tag::whereIn('id', $tags)->lists('name', 'id');
            }

            $result['response'] = 1;
            $result['tags'] = $tags;
            return Response::json($result);
        }
    }

    /**
     * Show the form for editing the specified resource.
     * GET /domain/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {

        if (!$id) {
            Notification::warning('Sadece size ait Domain bilgilerini görebilirsiniz!');
            return Redirect::to('domain');
        } else {
            $domain = Domain::find($id);
        }

        if ($domain->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait Domain bilgilerini görebilirsiniz!');
            return Redirect::to('domain');
        }

        if (!is_domain_taught($id) and $domain->user_id != $this->user->account_id) {
            Notification::warning('Bu domain özel bir domain olduğu için üzerinde değişiklik yapma yetkiniz yok!');
            return Redirect::back();
        }

        //accounts
        $accounts = $this->user->account()->lists('name', 'id');

        //Update existing source & tags
        //$domain = $this->updateDomainSettingsLive($domain, ["type", "source"]);
        $settings = json_decode($domain->settings, true);

        ##Sources & Uploads Section
        //checking if any source cat exists?
        $sources = Account::find($this->user->account_id)->sources()->lists('name', 'id');

        if (!$sources) { //Source cat does not exists.
            Notification::warning('Bu domaine eklenmiş kaynak kategorilerinin tamamı silinmiş. Bu nedenle kullanılamıyor. Lütfen yeni bir kaynak kategorisi ekleyin!');
            return Redirect::to('source/create');
        }

        ##Source & Uploads Section End
        ##Tags Section
        //checking if any source cat exists?
        $tags = Account::find($this->user->account_id)->tags()->lists('name', 'id');
        if (!$tags)
            $tags = array();

        $models = Config::get('settings.bayes.models');
        $model = $domain->getSettings()["model"];

        return View::make('domains.edit', compact('domain', 'settings', 'accounts', 'sources', 'tags', 'models', 'model'));
    }

    /**
     * update domain tags & sources resource in storage.
     *
     * @return Response
     */
    public function updateDomainSettingsLive($domain, $type) {
        //getSettings
        $settings = json_decode($domain->settings, true);

        if (in_array('source', $type)) {
            #Sources
            $sources_old = $domain->getSources();
            if (count($sources_old) > 0) {
                $sources_old_check = Source::whereIn('id', $sources_old)->lists('id');

                if (count($sources_old) != count($sources_old_check)) { //not equal it should be fixed before display.
                    $sources_exists = array_diff($sources_old, array_diff($sources_old, $sources_old_check));

                    if (empty($sources_exists))
                        $sources_exists = array();

                    foreach ($settings as $key => $value) {

                        if ($key == 'sources') {
                            $settings[$key] = $sources_exists; //update with existed ones.
                            break;
                        }
                    }
                }
            }
        }
        #Sources End

        if (in_array('tag', $type)) {
            #Tags
            $tags_old = $domain->getTags();
            if (count($tags_old) > 0) {
                $tags_old_check = Tag::whereIn('id', $tags_old)->lists('id');

                if (count($tags_old) != count($tags_old_check)) { //not equal it should be fixed before display.
                    $tag_exists = array_diff($tags_old, array_diff($tags_old, $tags_old_check));

                    foreach ($settings as $key => $value) {

                        if (empty($sources_exists))
                            $sources_exists = array();

                        if ($key == 'tags') {
                            $settings[$key] = $tag_exists; //update with existed ones.
                            break;
                        }
                    }
                }
            }
            #Tags End
        }

        $domain->settings = json_encode($settings, TRUE);
        $domain->save();

        return $domain;
    }

    /**
     * Update the specified resource in storage.
     * PUT /domain/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {

        $rules = array(
            'name' => 'required',
            'rules' => 'required',
            'sources' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            Notification::warning('Domain ekleyebilmeniz için İsim, Kaynaklar ve Kuralları eklemelisiniz!');
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $domain = Domain::find($id);
        $domain->name = ($domain->name != Input::get('name')) ? uniqueName($domain, Input::get('name')) : $domain->name;
        $adjustment = Input::get("adjustment", array(
                    "0" => "0",
                    "-1" => "0",
                    "1" => "0"
                        )
        );

        if (($adjustment[1] + $adjustment[-1]) < 100) {
            $adjustment[0] = 100 - ($adjustment[1] + $adjustment[-1]);
        }
        $domain->settings = json_encode(array(
            "rules" => Input::get('rules', array(
                "unigram", "bigram"
                    ), "bigram"
            ),
            "names" => Input::get("names", array(
                "0" => "Nötr",
                "-1" => "Olumsuz",
                "1" => "Olumlu"
                    )
            ),
            "adjustment" => $adjustment,
            "model" => Input::get('model', Config::get('settings.bayes.models')[0]), //default acoustic
            "balance" => Input::get('balance', 0),
            "sources" => Input::get('sources'),
            "tags" => Input::get('tags')
        ));

        if (Input::has('is_default')) {

            $userDomains = Account::find($domain->account_id)->domains();

            if ($userDomains->update(array('is_default' => '0'))) {
                $domain->is_default = true;
            }
        }

        $domain->sense = (Input::get('sense')) ? Input::get('sense') : 0;
        $domain->account_id = \Auth::User()->account_id;
        $domain->is_private = Input::has('is_private');
        $domain->save();

        Notification::success('Domain düzenlendi!');

        return Redirect::to('domain');
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /domain/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        $domain = Domain::find($id);

        $defaultDomain = Account::find($this->user->account_id)->domains()->where('is_default', '=', '1')->first();

        if (!is_domain_taught($id)) {
            Notification::warning('Bu domain özel olduğu için bunu silemezsiniz.');
            return Redirect::back();
        } elseif ($domain->isDefault()) {
            $defaultDomain = Domain::where('id', '!=', $domain->id)->where('account_id', '=', $domain->account_id)->first();
            if (is_object($defaultDomain)) {
                $defaultDomain->is_default = true;
                $defaultDomain->save();
            }
        }

        $user = Auth::user();
        UserLog::create([
            'user_id' => $user->id,
            'source' => 'domain',
            'log' => json_encode(array(
                'text' => $domain->name,
                'action' => 'silindi',
            ))
        ]);

        //Remove Api Access Tokens
        $access_token = md5($domain->domain_secret);
        Cache::forget($access_token);

        //Delete related sections.
        $domain->sentimentals()->withTrashed()->forceDelete();

        if ($domain->forceDelete()) {
            Notification::success('Kayıt silindi.');
            return Redirect::back();
        } else {
            Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
            return Redirect::back();
        }
    }

}
