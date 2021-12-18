<?php

// Display all SQL executed in Eloquent
Event::listen('illuminate.query', function($query) {
    // var_dump($query);
});


// Api Routes
Route::group(['domain' => 'api.labelai.com'], function () {

    Route::any('/', function() {
        return Response::json('Welcome to API ' . Config::get('settings.api.version'));
    });

    Route::group(['prefix' => Config::get('settings.api.version')], function () {

        $method = Request::method();
        if (Request::isMethod('get')) {
            //get access_token
            Route::get('auth', function() {
                $auth = new \ApiController();
                return $auth->auth();
            });
            //get access_token
            Route::get('bwauth', function() {
                $auth = new \ApiController();
                return $auth->bwauth();
            });

            //get bw query
            Route::get('bwquery', function() {
                $auth = new \ApiController();
                return $auth->bwquery();
            });
        } elseif (Request::isMethod('post')) {
            //sentimental query
            Route::post('check', function() {
                $auth = new \ApiController();
                return $auth->check();
            });

            Route::post('postag', function() {
                $auth = new \ApiController();
                return $auth->postag();
            });

            Route::post('images', function() {
                $auth = new \ApiController();
                return $auth->images();
            });
        } else {
            return Response::json('Method not allowed.');
        }
    });
});


//Route::get('dbmigrate', 'DbmigrateController@index');
// Guest Routes
Route::group(array('before' => 'guest'), function() {
//    Route::get('auth/register', array('as' => 'auth.register', 'uses' => 'AuthController@getRegister'));
//    Route::post('auth/register', array('as' => 'auth.register', 'uses' => 'AuthController@postRegister'));

    Route::get('auth/login', array('as' => 'auth.login', 'uses' => 'AuthController@getLogin'));
    Route::post('auth/login', array('as' => 'auth.login', 'uses' => 'AuthController@postLogin'));
});

// Member Routes
Route::group(array('before' => 'auth'), function() {


    /**
     * Home
     */
    Route::get('/', array('as' => 'home', 'uses' => 'HomeController@index'));

    Route::any('/image', array('as' => 'google', 'uses' => 'HomeController@google'));
    Route::any('/image/update', array('as' => 'google.update', 'uses' => 'HomeController@googleUpdate'));

    Route::get('/cacheflush', function() {
        Cache::flush();

        return Redirect::back();
    });

    // Ajax Routes
    Route::post('traine', array('as' => 'traine', 'uses' => 'HomeController@addTraine'));
    Route::post('analysis', array('as' => 'analysis', 'uses' => 'HomeController@showAnalysis'));
    Route::post('learned', array('as' => 'learned', 'uses' => 'HomeController@getLearned'));

    /**
     * Auth
     */
    Route::get('auth/logout', array('as' => 'auth.logout', 'uses' => 'AuthController@getLogout'));

    Route::get('auth/changepassword', array('as' => 'auth.changepassword', 'uses' => 'AuthController@getChangepassword'));
    Route::post('auth/changepassword', array('as' => 'auth.changepassword', 'uses' => 'AuthController@postChangepassword'));

    /**
     * Sentimental
     */
    Route::post('sentimental/create', array('as' => 'sentimental.create', 'uses' => 'SentimentalController@create'));
    Route::resource('sentimental', 'SentimentalController', array('except' => array('show')));

    /**
     * Account
     */
    Route::resource('account', 'AccountController', array('except' => array('show')));

    /**
     * Sources -> {Comments}
     */
    Route::resource('invoice', 'InvoiceController', array('only' => array('index', 'show')));

    /**
     * Domain
     */
    Route::post('domain/tags', array('as' => 'domain.tags', 'uses' => 'DomainController@tags'));
    Route::any('domain/create', array('as' => 'domain.create', 'uses' => 'DomainController@create'));
    Route::resource('domain', 'DomainController', array('except' => array('show')));

    /**
     * Statistics
     */
    Route::get('statistics', array('as' => 'statistics', 'uses' => 'StatisticsController@index'));
    Route::post('statistics/analysis', array('as' => 'statistics.analysis', 'uses' => 'StatisticsController@analysis'));
    Route::post('statistics/chart', array('as' => 'statistics.chart', 'uses' => 'StatisticsController@chart'));
    Route::post('statistics/unprocessed', array('as' => 'statistics.unprocessed', 'uses' => 'StatisticsController@unprocessed'));

    /**
     * Ticket
     */
    Route::resource('ticket', 'TicketController');
    Route::post('ticket/{ticket}/reply', array('as' => 'ticket.reply', 'uses' => 'TicketController@reply'));

    /**
     * Filter
     */
    Route::resource('filter', 'FilterController', array('except' => array('show')));
    Route::post('filter/tags', array('as' => 'tweets', 'uses' => 'FilterController@tags'));

    /**
     * Tag
     */
    Route::resource('tag', 'TagController', array('except' => array('show')));

    /**
     * taguploads -> {Tags}
     */
    Route::post('tag/remove', array('as' => 'tag.remove', 'uses' => 'TagUploadController@removeTag')); //Acception
    Route::post('tag/add', array('as' => 'tag.add', 'uses' => 'TagUploadController@addTag')); //Acception
    Route::post('tagupload/delete', array('as' => 'tagupload.delete', 'uses' => 'TagUploadController@delete'));
    Route::put('tagupload/create', 'TagUploadController@store');
    Route::any('tagupload/add', array('as' => 'tagupload.add', 'uses' => 'TagUploadController@add'));
    Route::post('tagupload/save', array('as' => 'tagupload.save', 'uses' => 'TagUploadController@updateAjaxTag'));
    Route::resource('tagupload', 'TagUploadController');

    /**
     * Admin
     */
    Route::group(array('before' => 'admin'), function() {
        // User route
        Route::resource('user', 'UserController');
    });


    /**
     * Users
     */
    Route::resource('userlogs', 'UserLogController', array('only' => array('index', 'show')));
    Route::get('userlogs/user/{user_id}', array('as' => 'userlogs.userlog', 'uses' => 'UserLogController@userlog'));
    Route::get('updatelogs', 'UserLogController@updatelogs');

    /**
     * Comments
     */
    Route::any('comments', array('as' => 'comments.index', 'uses' => 'CommentController@index'));
    Route::any('comments/search', array('as' => 'comments.search', 'uses' => 'CommentController@search'));
    Route::any('comments/publish', array('as' => 'comments.publish', 'uses' => 'CommentController@publish'));

    /**
     * Sources -> {Comments}
     */
    Route::resource('source', 'SourceController', array('except' => array('show')));

    /**
     * uploads -> {Sources}
     */
    Route::put('upload/create', 'UploadController@store');
    Route::post('upload/save', array('as' => 'upload.save', 'uses' => 'UploadController@update'));
    Route::resource('upload', 'UploadController');

    /**
     * Brandwatch -> {API}
     */
    Route::get('bwatch/{id}/edit', array('as' => 'bwrules.show', 'uses' => 'BwrulesController@show'));
    Route::get('bwatch/{id}/pause', array('as' => 'bwatch.pause', 'uses' => 'BwatchController@pause'));
    Route::get('bwatch/{id}/run', array('as' => 'bwatch.run', 'uses' => 'BwatchController@run'));
    Route::resource('bwatch', 'BwatchController');

    Route::post('bwrules/{id}/', array('as' => 'bwrules.update', 'uses' => 'BwrulesController@update'));
    Route::get('bwrules/queries/', array('as' => 'bwrules.queries', 'uses' => 'BwrulesController@bwQueryCall'));
    Route::get('bwrules/{id}/pause', array('as' => 'bwrules.pause', 'uses' => 'BwrulesController@pause'));
    Route::get('bwrules/{id}/run', array('as' => 'bwrules.run', 'uses' => 'BwrulesController@run'));
    Route::resource('bwrules', 'BwrulesController');
    
    Route::any('/js', function() {
        print('Yazılımın doğru çalışabilmesi için lütfen Javascript izinlerini aktif olarak değiştirin. <br>  '
                . '<a target="_settings "href="https://www.google.com.tr/search?q=javascript+ayarlar%C4%B1n%C4%B1+a%C3%A7ma&btnG=Search">Nasıl yapılır?</a>'
                . '<br><br><a href="/"> Giriş sayfasına dönün.</a>');
        exit;
    });
});

