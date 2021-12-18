<?php

use Ynk\Repos\Bwrules\BwRuleStatsRepositoryInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class BwruleStatsController extends \BaseController {

    /**
     * @var \Illuminate\Auth\UserInterface|null
     */
    protected $user;
    protected $bwrules;

    /**
     *
     */
    public function __construct(BwRuleStatsRepositoryInterface $bwstats) {
        $this->bwstats = $bwstats;
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     * GET /bwrulestats
     *
     * @return Response
     */
    public function index($id = null) {

        //Fix ID and Model
        if (!$id) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        }

        //Check 
        $bwatch = Bwatch::find($id);

        if (!$bwatch) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        } else if ($bwatch->account_id != $this->user->account_id) {
            Notification::danger('Sadece size ait BW kullanıcılarını görebilirsiniz.');
            return Redirect::to('bwatch');
        } else {
            // limit per page and check limit
            $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

            // get items for search query
            $query = array('name', 'LIKE', '%' . Input::get('q') . '%', 'bwatch_id', '=', $bwatch->id);

            // order by items id => desc
            $order = array('id', 'desc');
            // get items
            $bwrules = $this->bwstats->getPaginatedItems($limit, $order, $query);

            //TEMP
            $user = $this->user;

            return View::make('plugins.bwatch.rules.index', compact('bwatch', 'bwrules', 'user'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * GET /bwrulestats/create
     *
     * @return Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     * POST /bwrulestats
     *
     * @return Response
     */
    public function store() {
        //
    }

    /**
     * Display the specified resource.
     * GET /bwrulestats/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * GET /bwrulestats/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     * PUT /bwrulestats/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /bwrulestats/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

}
