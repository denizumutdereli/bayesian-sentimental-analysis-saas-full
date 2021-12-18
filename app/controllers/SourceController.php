<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class SourceController extends \BaseController {

    protected $source;

    public function __construct(ModelRepositoryInterface $source) {
        $this->source = $source;
        $this->user = Auth::user(); //Current User
    }

    /**
     * Display a listing of the resource.
     * GET /source
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
        $sources = $this->source->getPaginatedItems($limit, $order, $query);

        //TEMP
        $user = $this->user;


        return View::make('source.index', compact('sources', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /source/create
     *
     * @return Response
     */
    public function create() {
        //accounts
        $accounts = $this->user->account()->lists('name', 'id');
        return View::make('source.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /domain
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

        if (Source::where('account_id', '=', $this->user->account_id)->count() >= Config::get('settings.sources.limit')) {
            Notification::danger('En fazla ' . Config::get('settings.sources.limit') . ' adet kategori eklenebilir!');
            return Redirect::to('source');
        }

        $source = new Source;

        $source->account_id = Auth::User()->account_id;
        $source->user_id = \Auth::User()->id;
        $source->name = uniqueName($source, Input::get('name'));
        $source->about = Input::get('about');
        $source->created_by = $this->user->id;
        $source->updated_by = $this->user->id;

        if ($source->save()) {
            Notification::success('Kaynak kaydedildi!');
            return Redirect::to('source');
        } else {
            Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
            return Redirect::to('source');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * GET /source/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        $source = Source::find($id);

        if (!$source) {
            Notification::warning('Kaynak kategorisi bulunamadı!');
            return Redirect::to('source');
        } elseif ($source->account_id != $this->user->account_id) {

            Notification::warning('Sadece size ait Kaynak Kategori bilgilerini görebilirsiniz!');
            return Redirect::to('source');
        }

        //accounts
        $accounts = $this->user->account()->lists('name', 'id');

        return View::make('source.edit', compact('source', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     * PUT /source/{id}
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

        $source = Source::find($id);
        if (!$source) {
            Notification::warning('Kaynak kategorisi bulunamadı!');
            return Redirect::to('source');
        } elseif ($source->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait Kaynak Kategori bilgilerini görebilirsiniz!');
            return Redirect::to('source');
        }

        $source->name = ($source->name != Input::get('name')) ? uniqueName($source, Input::get('name')) : $source->name;

        if ($source->save()) {
            Notification::success('Kaynak düzenlendi!');
            return Redirect::to('source');
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /source/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        $source = Source::find($id);

        if (!$source) {
            Notification::warning('Kaynak kategorisi bulunamadı!');
            return Redirect::to('source');
        } elseif ($source->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait Kaynak Kategori bilgilerini görebilirsiniz!');
            return Redirect::to('source');
        } else {
            //Delete related sections.
            $source->uploads()->withTrashed()->forceDelete();
            $source->comments()->withTrashed()->forceDelete();

            //exclude from related domains
            $domains = Domain::all();
            foreach ($domains as $domain) {
                $settings = json_decode($domain->settings, true);

                if (($key = array_search($source->id, $settings['sources'])) !== false) {
                    unset($settings['sources'][$key]);
                    $domain->settings = json_encode($settings, true);
                    $domain->save();
                    unset($settings); //php cpu bug
                    unset($domain); //php cpu bug
                }
            }

            UserLog::create([
                'user_id' => $this->user->id,
                'account_id' => $this->user->account_id,
                'source' => 'source',
                'log' => json_encode(array(
                    'text' => $source->name,
                    'action' => 'silindi',
                ))
            ]);

            $source->forceDelete();

            Notification::warning('Kaynak silindi!');
            return Redirect::to('source');
        }

        Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::to('source');
    }

}
