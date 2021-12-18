<?php


// admin group
Route::group(['prefix' => 'v1'], function ()
{
    // about route
    Route::get('about2', [
        'before'     => 'about_filter',
        'as'         => 'about2',
        'uses'       => 'AboutController@About2',
        'namespaces' => 'Ynk'
    ]);
});
