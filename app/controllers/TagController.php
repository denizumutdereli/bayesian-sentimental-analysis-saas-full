<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class TagController extends \BaseController {

    protected $tag;

    public function __construct(ModelRepositoryInterface $tag) {
        $this->tag = $tag;
        $this->user = Auth::user(); //Current User
    }

    /**
     * Display a listing of the resource.
     * GET /tags
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
        $tags = $this->tag->getPaginatedItems($limit, $order, $query);

        //TEMP
        $user = $this->user;

        return View::make('tag.index', compact('tags', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /tag/create
     *
     * @return Response
     */
    public function create() {
        //accounts
        $accounts = $this->user->account()->lists('name', 'id');
        return View::make('tag.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /tag
     *
     * @return Response
     */
    public function store() {
        $rules = array(
            'name' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        if (Tag::where('account_id', '=', $this->user->account_id)->count() >= Config::get('settings.tags.limit')) {
            Notification::danger('En fazla ' . Config::get('settings.tags.limit') . ' adet kategori eklenebilir!');
            return Redirect::to('tag');
        }

        $tag = new Tag;

        $tag->account_id = \Auth::User()->account_id;
        $tag->user_id = \Auth::User()->id;
        $tag->name = uniqueName($tag, Input::get('name'));
        $tag->about = Input::get('about');
        $tag->created_by = $this->user->id;
        $tag->updated_by = $this->user->id;

        if ($tag->save()) {
            Notification::success('Kategori kaydedildi!');
            return Redirect::to('tag');
        } else {
            Notification::warning('Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.');
            return Redirect::to('tag');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * GET /tag/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        $tag = Tag::find($id);

        if (!$tag) {
            Notification::warning('Kategori bulunamadı!');
            return Redirect::to('tag');
        } elseif ($tag->account_id != $this->user->account_id) {

            Notification::warning('Sadece size ait kategorileri görebilirsiniz!');
            return Redirect::to('tag');
        }

        //accounts
        $accounts = $this->user->account()->lists('name', 'id');

        return View::make('tag.edit', compact('tag', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     * PUT /tag/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {

        $rules = array('name' => 'required');

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $tag = Tag::find($id);
        if (!$tag) {
            Notification::warning('Kategori bulunamadı!');
            return Redirect::to('tag');
        } elseif ($tag->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait kategorileri görebilirsiniz!');
            return Redirect::to('source');
        }

        $tag->name = ($tag->name != Input::get('name')) ? uniqueName($tag, Input::get('name')) : $tag->name;

        if ($tag->save()) {
            Notification::success('Kategori düzenlendi!');
            return Redirect::to('tag');
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /tag/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        $tag = Tag::find($id);

        if (!$tag) {
            Notification::warning('Kategori bulunamadı!');
            return Redirect::to('tag');
        } elseif ($tag->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait kategorileri görebilirsiniz!');
            return Redirect::to('tag');
        }
        else {

            //Delete related sections.
            $tag->uploads()->withTrashed()->forceDelete();

            //exclude from related domains
            $domains = Domain::all();
            foreach ($domains as $domain) {
                $settings = json_decode($domain->settings, true);

                if (($key = array_search($tag->id, $settings['tags'])) !== false) {
                    unset($settings['tags'][$key]);
                    $domain->settings = json_encode($settings, true);
                    $domain->save();
                    unset($settings); //php cpu bug
                    unset($domain); //php cpu bug
                }
            }

            UserLog::create([
                'user_id' => $this->user->id,
                'account_id' => $this->user->account_id,
                'source' => 'tag',
                'log' => json_encode(array(
                    'text' => $tag->name,
                    'action' => 'silindi',
                ))
            ]);
            
            $tag->forceDelete();
            
            Notification::warning('Kategori silindi!');
            return Redirect::to('tag');
        }

        Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::to('tag');
    }

}
