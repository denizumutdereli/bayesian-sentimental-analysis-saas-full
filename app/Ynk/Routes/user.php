<?php


// admin group
Route::group(['prefix' => 'v1'], function ()
{
    // about route
    Route::get('about', [
        'before'     => 'about_filter',
        'as'         => 'about',
        'uses'       => 'AboutController',
        'namespaces' => 'Ynk'
    ]);
});
