<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class UserController extends \BaseController {

    protected $user;
    protected $account;

    public function __construct(ModelRepositoryInterface $users) {
        $this->users = $users; //Multiple!!

        $this->user = Auth::user(); //Current User
        //Permissions
        $this->permissions = Config::get('settings.actions.limited');

        // Dot notation
        //array_forget($this->permissions, 'actions.limited.tag');
        array_forget($this->permissions, 'actions.limited.closure');

        //roles
        $this->roles = Config::get('settings.user.roles');


        //accounts
        if (!$this->user->is_superAdmin()) {
            $this->accounts = Account::where('id', $this->user->account_id)->where('is_active', '1')->lists('name', 'id');

            array_forget($this->permissions, 'actions.limited.account');
            //user roles (Exclude Superadmin)
            array_forget($this->roles, 'super');
        } else {
            $this->accounts = Account::lists('name', 'id');
        }
        if (!$this->accounts) { //SuperAdmin message
            Notification::danger('Kullanıcı bilgilerinin düzenlenebilmesi için ana hesabın aktif olması gerekir!');
            return Redirect::to('user');
        }
    }

    /**
     * Display a listing of the resource.
     * GET /user
     *
     * @return Response
     */
    public function index() {
        // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

        // get items for search query
//        if (!$this->user->is_superAdmin())
//            $query = array('email', 'LIKE', '%' . Input::get('q') . '%', 'account_id', '=', $this->user->account_id);
//        else
        $query = array('email', 'LIKE', '%' . Input::get('q') . '%');

        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $users = $this->users->getPaginatedItems($limit, $order, $query);

        return View::make('user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /user/create
     *
     * @return Response
     */
    public function create() {
        return View::make('user.create', array('permissions' => $this->permissions, 'accounts' => $this->accounts, 'roles' => $this->roles));
    }

    /**
     * Store a newly created resource in storage.
     * POST /user
     *
     * @return Response
     */
    public function store() {

        $rules = array(
            'email' => 'required|email|unique:users',
            'account_id' => 'required',
            'password' => 'required|confirmed|min:6|max:16',
            'password_confirmation' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        //Count check.
        $count = count(User::where('account_id', $this->user->account_id));

        if ($count > Config::get('settings.account.user_count')) {
            Notification::danger('En fazla ' . Config::get('settings.account.user_count') . ' adet kullanıcı eklenebilir.');
            return Redirect::to('user');
        }

        // Create a new user in the database...
        $user = User::create(array(
                    'account_id' => (Input::get('role') == 'super') ? 1 : Input::get('account_id'),
                    'email' => Input::get('email'),
                    'password' => Hash::make(Input::get('password')),
                    'permissions' => json_encode(Input::get('permissions')),
                    'role' => Input::get('role')
        ));

        if ($user) {
            UserLog::create([
                'user_id' => $this->user->id,
                'account_id' => $user->account_id,
                'source' => 'user',
                'log' => json_encode(array(
                    'text' => $user->email,
                    'action' => 'eklendi',
                ))
            ]);

            Notification::success('Kullanıcı kaydedildi!');
        } else {
            Notification::danger('Bir hata oluştu lütfen daha sonra tekrar deneyiniz!');
        }

        return Redirect::to('user');
    }

    /**
     * Show the form for editing the specified resource.
     * GET /user/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {

        $user = User::find($id);

        self::checkownership($user);

        $user_permissions = json_decode($user->permissions ? $user->permissions : '[]');

        return View::make('user.edit', array('user' => $user, 'user_permissions' => $user_permissions, 'permissions' => $this->permissions, 'accounts' => $this->accounts, 'roles' => $this->roles));
    }

    /**
     * Update the specified resource in storage.
     * PUT /user/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {

        // Update user in the database...
        $user = User::find($id);

        self::checkownership($user);

        $rules = array(
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'confirmed|min:6|max:16',
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $user->email = Input::get('email');

        if (Input::has('password')) {
            $user->password = Hash::make(Input::get('password'));
        }
        $user->account_id = (Input::get('role') == 'super') ? 1 : Input::get('account_id');

        $msg = '';
        if ($user->id != $this->user->id OR $this->user->is_superAdmin() == true) {
            $user->permissions = json_encode(Input::get('permissions'));
            $user->role = Input::get('role');
        } else
            $msg = ' Güvenlik ayarları değişmedi.';

        $user->save();

        UserLog::create([
            'user_id' => $this->user->id,
            'account_id' => $user->account_id,
            'source' => 'user',
            'log' => json_encode(array(
                'text' => $user->email,
                'action' => 'güncellendi',
            ))
        ]);

        Notification::success('Kullanıcı düzenlendi! ' . $msg);
        return Redirect::to('user');
    }

    /**
     * Check user / account ownership.
     * $user
     *
     * @param  obj  $user
     * @return Redirect / Response
     */
    public function checkownership($user) {
        if (!$user) {
            if (Request::isXmlHttpRequest()) {
                $result = array(
                    'response' => 0,
                    'msg' => 'Kullanıcı bulunamadı!'
                );
                return Response::json($result);
            } else {
                Notification::danger('Kullanıcı bilgisi bulunamadı!');
                return Redirect::home();
            }
        } elseif ($user->account_id != $this->user->account_id && !$this->user->is_superAdmin()) {

            if (Request::isXmlHttpRequest()) {
                $result = array(
                    'response' => 0,
                    'msg' => 'Sadece size ait kullanıcıları güncelleyebilirsiniz!'
                );
                return Response::json($result);
            } else {
                Notification::danger('Sadece size ait kullanıcıları güncelleyebilirsiniz!');
                return Redirect::to('user');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /user/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        $user = User::find($id);
        self::checkownership($user);

        if ($user->id == $this->user->id) {
            Notification::warning('Kendi kullanıcı isminizi silemezsiniz!');
            return Redirect::back();
        }

        $user->sources()->withTrashed()->delete();
        $user->uploads()->withTrashed()->delete();
        $user->tags()->withTrashed()->delete();
        $user->taguploads()->withTrashed()->delete();
        $user->domains()->withTrashed()->delete();
        $user->sentimentals()->withTrashed()->delete();
        $user->tickets()->withTrashed()->delete();
        $user->userlogs()->withTrashed()->delete();
        $user->bwatchs()->withTrashed()->delete();


        $data = ["is_active" => 0, "status" => 0];

        $user->bwrules()->update($data);
        $user->bwrules()->delete();

        $user->bwstats()->update($data);
        $user->bwstats()->delete();


        if ($user->delete()) {
            UserLog::create([
                'user_id' => $this->user->id,
                'account_id' => $user->account_id,
                'source' => 'user',
                'log' => json_encode(array(
                    'text' => $user->email,
                    'action' => 'silindi',
                ))
            ]);

            Notification::success('Kayıt silindi.');
            return Redirect::back();
        }

        Notification::success('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::back();
    }

}
