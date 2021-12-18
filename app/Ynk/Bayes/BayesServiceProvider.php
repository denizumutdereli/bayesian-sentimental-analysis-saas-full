<?php
namespace Ynk\Bayes;

use Illuminate\Support\ServiceProvider;

class BayesServiceProvider extends ServiceProvider {


    public function register()
    {
        $this->app->bind('bayes', 'Ynk\Bayes\Bayes');
    }
}