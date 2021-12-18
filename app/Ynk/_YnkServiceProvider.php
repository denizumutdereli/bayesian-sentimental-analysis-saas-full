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
    	
        // Ticket binding
        $this->app->bind(
            'Ynk\Repos\Ticket\TicketRepositoryInterface',
            'Ynk\Repos\Ticket\DbTicketRepository'
        );

        // TagUpload binding
        $this->app->bind(
            'Ynk\Repos\Tagupload\TagUploadRepositoryInterface',
            'Ynk\Repos\Tagupload\DbTagUploadRepository'
        );
        
        // Account binding
        $this->app->bind(
            'Ynk\Repos\Account\AccountRepositoryInterface',
            'Ynk\Repos\Account\DbAccountRepository'
        );
            
        // Upload binding
        $this->app->bind(
            'Ynk\Repos\Upload\UploadRepositoryInterface',
            'Ynk\Repos\Upload\DbUploadRepository'
        );
        
       // UserLog binding
        $this->app->bind(
            'Ynk\Repos\UserLog\UserLogRepositoryInterface',
            'Ynk\Repos\UserLog\DbUserLogRepository'
        );

        // WordsRepo binding
        $this->app->bind(
            'Ynk\Repos\WordsRepo',
            'Ynk\Repos\DbWordsRepo'
        );
    }
}
