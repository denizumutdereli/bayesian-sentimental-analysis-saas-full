<?php namespace Ynk;

use Illuminate\Support\ServiceProvider;


class YnkServiceProvider extends ServiceProvider {

    public function register()
    {
        // Model binding
        $this->app->bind(
            'Ynk\Repos\Model\ModelRepositoryInterface',
            'Ynk\Repos\Model\DbModelRepository'
        );

        // TagUpload binding
        $this->app->bind(
            'Ynk\Repos\Tagupload\TagUploadRepositoryInterface',
            'Ynk\Repos\Tagupload\DbTagUploadRepository'
        );
        
        // Upload binding
        $this->app->bind(
            'Ynk\Repos\Upload\UploadRepositoryInterface',
            'Ynk\Repos\Upload\DbUploadRepository'
        );
        
        // Bwrules binding
        $this->app->bind(
            'Ynk\Repos\Bwrules\BwRulesRepositoryInterface',
            'Ynk\Repos\Bwrules\DbBwRulesRepository'
        );
       
    }
}
