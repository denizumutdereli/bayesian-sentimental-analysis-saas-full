<?php

namespace Ynk\Repos\Model;

use Ynk\Repos\DbRepository;

class DbModelRepository extends DbRepository implements ModelRepositoryInterface {

    protected $model;

    public function __construct() {
        $this->user = \Auth::user();

        $route = \Route::getCurrentRoute()->getPath();

        switch ($route) {

            case 'account':
                $this->model = new \Account();

                if (!$this->user->is_superAdmin())
                    $this->model = $this->model->where('id', '=', $this->user->account_id);
                break;

            case 'domain':
                $this->model = new \Domain();
                $this->model = $this->user->domains()->where('is_active', '=', '1')->where('account_id', '=', $this->user->account_id);
                break;

            case 'user':
                $this->model = new \User();
                if (!$this->user->is_superAdmin())
                    $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;

            case 'source':
                $this->model = new \Source();
                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;

            case 'upload':
                $this->model = new \Upload();
                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;

            case 'tag':
                $this->model = new \Tag();
                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;

            case 'sentimental':
                $this->model = new \Sentimental();
                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;

            case 'ticket':
                $this->model = new \Ticket();
                if (!$this->user->is_superAdmin()) {
                    $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                }
                break;

            case 'userlogs':
                $this->model = new \UserLog();
                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;

            case 'invoice':
                $this->model = new \Invoice();
                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;

            case 'bwatch':
                $this->model = new \Bwatch();
                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;

//            case 'bwrule':
//                $this->model = new \Bwrule();
//                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
//                break;
            
            case 'bwqueries':
                $this->model = new \BwQueries();
                $this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;
            
             case 'adminlog':
                $this->model = new \AdminLog();
                //$this->model = $this->model->where('account_id', '=', $this->user->account_id);
                break;
        }
    }

}
