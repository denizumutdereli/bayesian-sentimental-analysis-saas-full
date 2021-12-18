<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class AccountController extends \BaseController {

    protected $user;
    protected $account;

    public function __construct(ModelRepositoryInterface $account) {
        $this->account = $account;
        $this->user = Auth::user();

        if ($this->user->is_superAdmin())
            $this->prefix = '.admin.';
        else
            $this->prefix = '.';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {

        // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

        // get items for search query
        $query = array('name', 'LIKE', '%' . Input::get('q') . '%');

        // order by items id => desc
        $order = array('id', 'asc');

        $accounts = $this->account->getPaginatedItems($limit, $order, $query);

        return View::make('account.' . $this->prefix . 'index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        return View::make('account' . $this->prefix . 'create', compact('account'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {

        if (!$this->user->is_superAdmin()) {
            Notification::warning('Üzgünüz, bu işlemi yapmak için yetkili değilsiniz'); //not permited message
            return Redirect::to('/');
        }

        $rules = array('name' => 'required|unique:accounts');

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            Notification::danger('Bu isimde farklı bir hesap bulunuyor. Lütfen kontrol edip tekrar ekleyin.'); // another user with same name
            return Redirect::back()->withInput()->withErrors($validator);
        }

        // if form has file upload logo
        if (Input::hasFile('logo')) {
            $file = Input::file('logo');
            $destinationPath = 'uploads';
            $filename = Str::random(16);

            // check uploads folder exists else create
            if (!File::isDirectory('uploads')) {
                File::makeDirectory($destinationPath, 0755);
            }

            $filename = $filename . '.' . $file->getClientOriginalExtension();
            $uploadSuccess = $file->move($destinationPath, $filename);

            if (!$uploadSuccess) {
                Notification::success('Logo yüklenirken bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'); //logo upload error
                return Redirect::back();
            }
        }

        $data = Input::all();
        $data['api'] = ( Input::get('api') ) ? Input::get('api') : 0;
        $data['package'] = ( Input::get('accountType') == 'pitching' ) ? 50 : Input::get('package');

        $data['api_key'] = Str::random(16);
        $data['api_secret'] = Str::random(16);

        $data['is_active'] = ( Input::get('is_active') ) ? Input::get('is_active') : 0;
        $data['created_by'] = $this->user->id;
        $data['updated_by'] = $this->user->id;

        Account::create($data);

        Notification::success('Hesap bilgileri eklendi.'); //account added
        return Redirect::to('account/');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        return $this->index($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {

        if (!$this->user->is_superAdmin())
            $account = $this->user->account()->first();
        else
            $account = Account::find($id);

        if (!$account) {
            Notification::danger('Hesap bilgisi bulunamadı!'); //could not find the account
            return Redirect::home();
        }

        if ($account->accountType == 'pitching') {
            $account->endDate = \Carbon\Carbon::createFromTimestamp($account->created_at->getTimestamp())->addDays(Config::get('settings.api.cache.access_token', 60));
        }

        $created_by = @User::find($account->created_by);
        $account->created_person = (@$created_by->email) ? @$created_by->email : 'Default';

        $updated_by = @User::find($account->updated_by);
        $account->updated_person = (@$updated_by->email) ? $updated_by->email : 'Default';

        return View::make('account' . $this->prefix . 'edit', compact('account'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {

        // get account from db
        if (!$this->user->is_superAdmin())
            $account = $this->user->account()->first();
        else
            $account = Account::find($id);


        if (!$account) {
            Notification::danger('Hesap bilgisi bulunamadı!'); //could not find the account
            return Redirect::home();
        }

        $rules = array('name' => 'required');

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        // if form has file upload logo
        if (Input::hasFile('logo')) {
            $file = Input::file('logo');
            $destinationPath = 'uploads';
            $filename = Str::random(16);

            // check uploads folder exists else create
            if (!File::isDirectory('uploads')) {
                File::makeDirectory($destinationPath, 0755);
            }

            // if exists delete old logo
            if (!is_null($account->logo)) {
                File::delete($destinationPath . '/' . $account->logo);
            }

            $filename = $filename . '.' . $file->getClientOriginalExtension();
            $uploadSuccess = $file->move($destinationPath, $filename);

            if ($uploadSuccess) {
                $account->logo = $filename;
                $account->save();

                Notification::success('Logo yüklendi.'); //logo uploaded
            } else {
                Notification::success('Logo yüklenirken bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'); //logo upload error
            }
            return Redirect::back();
        }

        //Only superadmin can edit
        if ($this->user->is_superAdmin()) {
            $account->api = (Input::get('api')) ? Input::get('api') : 0;
            $account->is_active = ( Input::get('is_active') ) ? Input::get('is_active') : 0;
        }

        $account->updated_by = $this->user->id;
        $account->name = uniqueName($account, addslashes(Input::get('name')));


        $data = Input::all();
        $data['package'] = ( Input::get('accountType') == 'pitching' ) ? 50 : Input::get('package');

        $account->update($data);

        Notification::success('Hesap bilgileri güncellendi.');
        return Redirect::to('account');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        if (!$this->user->is_superAdmin()) {
            Notification::warning('Üzgünüz, bu işlemi yapmak için yetkili değilsiniz'); //not permited
            return Redirect::back()->withInput()->withErrors();
        }

        // get account from db
        if (!$this->user->is_superAdmin())
            $this->account = $this->user->account()->first();
        else
            $this->account = Account::find($id);
        //dd($this->account);

        if (!$this->account) {
            Notification::danger('Hesap bilgisi bulunamadı!'); //could not find the account
            return Redirect::back()->withInput()->withErrors();
        } elseif ($this->account->id == $this->user->account_id) {
            Notification::warning('Sistem hesabı silinemez!'); //system account can not be deleted!
            return Redirect::back()->withInput()->withErrors();
        } elseif ($this->account->delete()) { #force delete the account with related modules

            ##StartProccess
            //Users.
            $this->account->pings()->withTrashed()->delete();
            $this->account->users()->withTrashed()->delete();
            $this->account->sources()->withTrashed()->delete();
            $this->account->uploads()->withTrashed()->delete();
            $this->account->tags()->withTrashed()->delete();
            $this->account->taguploads()->withTrashed()->delete();
            $this->account->domains()->withTrashed()->delete();
            //$this->account->comments()->withTrashed()->delete();
            $this->account->sentimentals()->withTrashed()->delete();
            $this->account->tickets()->withTrashed()->delete();
            $this->account->userlogs()->withTrashed()->delete();
            $this->account->invoices()->withTrashed()->delete();
            $this->account->bwatchs()->withTrashed()->delete();

            $data = ["is_active" => 0, "status" => 0];

            $this->account->bwrules()->update($data);
            $this->account->bwrules()->delete();

            $this->account->bwstats()->update($data);
            $this->account->bwstats()->delete();

            //dd(DB::getQueryLog());
            UserLog::create([
                'user_id' => $this->user->id,
                'tag' => 'account',
                'log' => json_encode(array(
                    'text' => $this->account->name,
                    'action' => 'silindi',
                ))
            ]);

            Notification::success('Kayıt silindi.'); //delete okay
            return Redirect::to('account');
        }

        Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.'); //error when try to delete
        return Redirect::back()->withInput()->withErrors();
    }

    /**
     * Creates API auth token via HTTP bridge
     *
     * @param  int  boolen
     * @return Response
     */
    public function auth() {

        if ($this->user->account->api == 0) { //account does not have API permission
            Notification::warning('Bu hesap için API izni bulunmuyor. Lütfen müşteri temsilcinizle görüşünüz!'); //np api permission for this account
            return Redirect::back();
        } else {
            $api = new HttpController();
            $api->url = Config::get('settings.api.url') . Config::get('settings.api.version') . '/';
            $api->method = 'GET';
            $api->page = 'auth';
            $api->params = [
                'api_key' => $this->user->account->api_key,
                'api_secret' => $this->user->account->api_secret];

            $response = $api->call();

            if ($response['status'] === TRUE) {
                if (Request::isXmlHttpRequest()) {

                    $response = json_decode($response['data']);

                    if (isset($response->data))
                        $response = $response->data;

                    $result = array(
                        'status' => 1,
                        'msg' => 'API izni başarıyla oluşturuldu!', //api key created
                        'access_code' => $response->access_token
                    );
                    return Response::json($result);
                } else {
                    Notification::success('API izni başarıyla oluşturuldu!'); //api permission settled
                    return Redirect::back();
                }
            } else {
                $error = json_decode($response['data']);
                if (Request::isXmlHttpRequest()) {
                    $result = array(
                        'status' => 0,
                        'msg' => $error->error
                    );
                    return Response::json($result);
                } else {
                    Notification::danger($error->error);
                    return Redirect::back();
                }
            }
        }
    }

}
