<?php
namespace Ynk\Comments;

use Illuminate\Support\ServiceProvider;

class CommentServiceProvider extends ServiceProvider {


    public function register()
    {
        $this->app->bind('commentutils', 'Ynk\Comments\CommentUtils');
    }
}