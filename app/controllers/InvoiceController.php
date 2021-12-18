<?php

use Ynk\Repos\Model\ModelRepositoryInterface;

class InvoiceController extends \BaseController {

    /**
     * @var \Illuminate\Auth\UserInterface|null
     */
    protected $user;
    protected $invoice;

    /**
     *
     */
    public function __construct(ModelRepositoryInterface $invoice) {
        $this->invoice = $invoice;
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     * GET /invoice
     *
     * @return Response
     */
    public function index() {

        // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

        // get items for search query
        $query = array('id', 'LIKE', '%' . Input::get('q') . '%');

        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $invoices = $this->invoice->getPaginatedItems($limit, $order, $query);

        //TEMP
        $user = $this->user;


        return View::make('invoice.index', compact('invoices', 'user'));
    }

    /**
     * Display the specified resource.
     * GET /invoice/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {

        $invoice = Invoice::find($id);

        if (!$invoice OR ! $id) {
            Notification::warning('Üzgünüz, fatura bulunamadı!');
            return Redirect::to('/invoice');
        } elseif ($invoice->account_id != $this->user->account_id) {
            Notification::warning('Üzgünüz, fatura bulunamadı!');
            return Redirect::to('/invoice');
        }

        $details = (array) json_decode($invoice->details);

        return View::make('invoice.show', compact('invoice', 'details'));
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /invoice/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        //
    }

}
