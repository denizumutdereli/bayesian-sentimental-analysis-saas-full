<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class TicketController extends \BaseController {

    protected $user;
    protected $ticket;

    public function __construct(ModelRepositoryInterface $ticket) {
        $this->user = Auth::user();
        $this->ticket = $ticket;
    }

    /**
     * Display a listing of the resource.
     * GET /ticket
     *
     * @return Response
     */
    public function index() {
        // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : (int) Input::get('limit', 10);

        // get items for search query       
        $query = (Input::has('q')) ? array('title', 'LIKE', '%' . Input::get('q') . '%') : array('parent_id', '=', 0);

        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $tickets = $this->ticket->getPaginatedItems($limit, $order, $query);

        return View::make('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /ticket/create
     *
     * @return Response
     */
    public function create() {
        return View::make('tickets.create');
    }

    /**
     * Store a newly created resource in storage.
     * POST /ticket
     *
     * @return Response
     */
    public function store() {
//        dd(Input::all());

        $rules = array(
            'title' => 'required',
            'description' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $ticket = new Ticket(array(
            'parent_id' => Input::get('parent_id', 0),
            'account_id' => $this->user->account_id,
            'title' => Input::get('title'),
            'description' => Input::get('description'),
            'status' => Input::get('status', 1),
        ));

        $ticket = $this->user->tickets()->save($ticket);

        ($ticket) ? Notification::success('Ticket kaydedildi!') : Notification::danger('Ticket kaydedilirken bir hata oluştu lütefen daha sonra tekrar deneyiniz!');

        return Redirect::to('ticket');
    }

    /**
     * Display the specified resource.
     * GET /ticket/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        $ticket = Ticket::where('account_id', '=', $this->user->account_id)->where('id','=',$id)->first();
        if (!$ticket) {
            Notification::danger('Böyle bir <strong>aktif</strong> ticket bulunamadı.');
            return Redirect::to('ticket');
        }

        return View::make('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     * GET /ticket/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        $ticket = Ticket::active()->find($id);

        if (!$ticket) {
            Notification::danger('Böyle bir <strong>aktif</strong> ticket bulunamadı.');
            return Redirect::to('ticket');
        }

        return View::make('tickets.edit', compact('ticket'));
    }

    /**
     * Update the specified resource in storage.
     * PUT /ticket/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {

        $rules = array(
            'title' => 'required',
            'description' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $ticket = Ticket::find($id);
        $ticket->title = Input::get('title');
        $ticket->description = Input::get('description');
        $ticket->status = Input::get('status', 1);

        $ticket = $ticket->save();

        ($ticket) ? Notification::success('Ticket düzenlendi!') : Notification::danger('Ticket düzenlenirken bir hata oluştu lütfen daha sonra tekrar deneyiniz!');

        return Redirect::to('ticket');
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /ticket/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        $ticket = Ticket::find($id);

            $user = Auth::user();
            UserLog::create([
                'user_id' => $user->id,
                'account_id'=> $user->account_id,
                'source' => 'ticket',
                'log' => json_encode(array(
                    'text' => $ticket->title,
                    'action' => 'silindi',
                ))
            ]);

            Notification::success('Kayıt silindi.');

            $ticket->forceDelete();
              
            if (Request::isXmlHttpRequest()) {
                $result = array(
                    'response' => 1
                );
                return Response::json($result);
            }
            return Redirect::back();
   

        Notification::success('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');

        if (Request::isXmlHttpRequest()) {
            $result = array(
                'response' => 0
            );
            return Response::json($result);
        }
        return Redirect::back();
    }

    public function reply() {
        $rules = array(
            'description' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            Notification::danger('Lütfen zorunlu alanları doldurunuz!');
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $ticket = new Ticket(array(
            'parent_id' => Input::get('parent_id'),
            'account_id' => Input::get('account_id'),
            'title' => Input::get('title'),
            'description' => Input::get('description'),
            'status' => Input::get('status', 1),
        ));

        $ticket = $this->user->tickets()->save($ticket);

        ($ticket) ? Notification::success('Cevap gönderildi!') : Notification::danger('Cevap gönderilirken bir hata oluştu lütefen daha sonra tekrar deneyiniz!');

        return Redirect::back();
    }

}
