<?php

//use Ynk\Bayes;
use Ynk\Repos\Model\ModelRepositoryInterface;

class UserLogController extends BaseController {

     protected $user_logs;
   
   public function __construct(ModelRepositoryInterface $user)
    {
        $this->user_logs = $user;
    }
    
    public function index()
    {
            // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $logs = $this->user_logs->getPaginatedItems($limit, $order);


        return View::make('userlog.index', array('logs' => $logs));
    }
    
    public function show($id)
    {
            // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);
        
        $query = array('id', '=', $id);
        
        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $logs = $this->user_logs->getPaginatedItems($limit, $order, $query);


        return View::make('userlog.index', array('logs' => $logs));
    }

    public function userlog($id)
    {
            // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

        $query = array('user_id', '=', $id);

        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $logs = $this->user_logs->getPaginatedItems($limit, $order, $query);


        return View::make('userlog.index', array('logs' => $logs));
    }

    public function updatelogs()
    {
        return View::make('userlog.updatelogs');
    }
    
}