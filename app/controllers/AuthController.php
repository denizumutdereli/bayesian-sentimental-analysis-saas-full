<?php

class AuthController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | Default Auth Controller
    |--------------------------------------------------------------------------
    |
    | You may wish to use controllers instead of, or in addition to, Closure
    | based routes. That's great! Here is an example controller method to
    | get you started. To route to this controller, just add the route:
    |
    |	Route::get('/', 'AuthController@showWelcome');
    |
    */

    public function getRegister()
    {
        return View::make('auth.register');
    }

    public function postRegister()
    {
//        dd(Input::all());
        $rules = array(
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6|max:16',
            'password_confirmation' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails())
        {
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                Notification::danger($message);
            }

            return Redirect::back()->withInput()->withErrors($validator);
        }

        // Create a new user in the database...
        $user = new User;
        $user->email = Input::get('email');
        $user->password = Hash::make(Input::get('password'));

        $user->save();

        return Redirect::to('auth/login');
    }

    public function getLogin()
    {
        return View::make('auth.login');
    }

    public function postLogin()
    {
        $rules = array(
            'email' => 'required|email',
            'password' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails())
        {
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                Notification::danger($message);
            }

            return Redirect::back()->withInput()->withErrors($validator);
        }

        Input::merge(array('is_active' => 1));

        $attempt = Auth::attempt(Input::only('email', 'password', 'is_active'), Input::get('remember', false));

        if ( ! $attempt)
        {
            Notification::danger('E-posta veya şifre hatalı.');
            return Redirect::back();
        }

        $user = Auth::user();
        UserLog::create([
            'user_id' => $user->id,
            'source' => 'login',
            'log'    => json_encode(array(
                'text' => Input::get('email'),
                'action' => ' giriş yaptı',
            ))
        ]);

        return Redirect::to('/');
    }

    public function getLogout()
    {
        Auth::logout();

        return Redirect::to('auth/login');
    }

    public function getChangePassword()
    {
        $user = Auth::user();
        return View::make('auth.changepassword', compact('user'));
    }

    public function postChangePassword()
    {
        $rules = array(
            'password' => 'required|confirmed|min:6|max:16',
            'password_confirmation' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails())
        {
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                Notification::danger($message);
            }

            return Redirect::back()->withInput()->withErrors($validator);
        }

        // Create a new user in the database...
        $user = Auth::user();
        $user->password = Hash::make(Input::get('password'));

        $user->save();

        return Redirect::to('auth/logout');
    }

}
