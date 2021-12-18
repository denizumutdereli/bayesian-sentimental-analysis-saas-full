<?php

/*
  |--------------------------------------------------------------------------
  | Application & Route Filters
  |--------------------------------------------------------------------------
  |
  | Below you will find the "before" and "after" events for the application
  | which may be used to do any work before or after a request into your
  | application. Here you may also register your custom route filters.
  |
 */

App::before(function ($request) {
    //
});


App::after(function ($request, $response) {
    //
});

/*
  |--------------------------------------------------------------------------
  | Authentication Filters
  |--------------------------------------------------------------------------
  |
  | The following filters are used to verify that the user of the current
  | session is logged into this application. The "basic" filter easily
  | integrates HTTP Basic authentication for quick, simple checking.
  |
 */

Route::filter('auth', function () {
    if (Auth::guest()) {
        if (Request::ajax()) {
            return Response::make('Unauthorized', 401);
        } else {
            return Redirect::guest('auth/login');
        }
    }

    /* Check if the account is active, even the user logged-in
     * 
     */

    if (!Auth::user()->is_superAdmin()) {
        $account = \Auth::user()->account()->where('is_active', '=', '1')->first();

        if (!$account) {
            Notification::danger('Hesap bilgisi bulunamadı ya da pasif durumda.');
            Auth::logout();
            return Redirect::to('/auth/login');
        }
    }

    $permissions = json_decode(Auth::user()->permissions);
    $permissions = ($permissions ? $permissions : array());

    $route = str_replace('@', '.', Route::currentRouteAction());
    $defaults = Config::get('settings.actions.defaults');
    $injectedPerms = array();

    if (in_array('CommentController.index', $permissions)) {
        $injectedPerms[] = 'CommentController.search';
    }

    //permission exceptions
    $injectedPerms[] = 'DomainController.tags';
    $injectedPerms[] = 'HomeController.google';
    $injectedPerms[] = 'HomeController.googleUpdate';

    foreach ($permissions as $permission) {
        if (ends_with($permission, 'create')) {
            $controllerName = explode('.', $permission);
            $injectedPerms[] = "$controllerName[0].store";
        }

        if (ends_with($permission, 'edit')) {
            $controllerName = explode('.', $permission);
            $injectedPerms[] = "$controllerName[0].update";
        }
    }

    $permissions = array_merge($permissions, $injectedPerms);
    if ($route !== null && $route != '' && !in_array($route, array_merge($permissions, $defaults))) {
        if (Request::ajax()) {
            return Response::make('Unauthorized', 401);
        } else {
            Notification::danger('<i class="fa fa-warning"></i> Sistem tarafından yetkileriniz sınırlandırıldı. Lütfen yöneticinize başvurunuz!');
            return Redirect::route('home');
        }
    }
});


Route::filter('auth.basic', function () {
    return Auth::basic();
});

/*
  |--------------------------------------------------------------------------
  | Guest Filter
  |--------------------------------------------------------------------------
  |
  | The "guest" filter is the counterpart of the authentication filters as
  | it simply checks that the current user is not logged in. A redirect
  | response will be issued if they are, which you may freely change.
  |
 */

Route::filter('guest', function () {
    if (Auth::check())
        return Redirect::route('home');
});

/*
  |--------------------------------------------------------------------------
  | CSRF Protection Filter
  |--------------------------------------------------------------------------
  |
  | The CSRF filter is responsible for protecting your application against
  | cross-site request forgery attacks. If this special token in a user
  | session does not match the one given in this request, we'll bail.
  |
 */

Route::filter('csrf', function () {
    if (Session::token() !== Input::get('_token')) {
        throw new Illuminate\Session\TokenMismatchException;
    }
});

/*
  |--------------------------------------------------------------------------
  | Admin Filter
  |--------------------------------------------------------------------------
  |
  | Only admin users can access this filters applied areas.
  |
 */

Route::filter('admin', function () {
    if (Auth::user()->role != 'super' && Auth::user()->role != 'admin') {
        Notification::danger('<i class="fa fa-warning"></i> Sistem tarafından yetkileriniz sınırlandırıldı. Lütfen yöneticinize başvurunuz!');
        return Redirect::home();
    }
});

Route::filter('perms', function () {
    $currentRouteName = Str::singular(Route::currentRouteName());
    $limitedRoutes = Config::get('settings.actions.limited');

//    dd(Str::singular($currentRouteName), $limitedRoutes);

    if (!in_array($currentRouteName, $limitedRoutes) && !$limitedRoutes[$currentRouteName]['active']) {
        Notification::danger('<i class="fa fa-warning"></i> Sistem tarafından bu bölüm sınırlandırıldı. Lütfen yöneticinize başvurunuz!');
        return Redirect::home();
    }
});

Route::filter('edit', function () {
    if (!$this->user->is_superAdmin()) {
        Notification::warning('Sadece size ait kayıt bilgilerini görebilirsiniz!');
        return Redirect::home();
    }
});