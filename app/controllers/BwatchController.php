<?php

use Ynk\Repos\Model\ModelRepositoryInterface;
use Ynk\Repos\Bwrules\DbBwrulesRepository;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class BwatchController extends \BaseController {

    /**
     * @var \Illuminate\Auth\UserInterface|null
     */
    protected $user;
    protected $bwatch;
    protected static $api_main = 'http://api.labelai.com/';

    /**
     *
     */
    public function __construct(ModelRepositoryInterface $bwatch) {
        $this->bwatch = $bwatch;
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     * GET /bwatch
     *
     * @return Response
     */
    public function index() {

        // limit per page and check limit
        $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

        // get items for search query
        $query = array('client_name', 'LIKE', '%' . Input::get('q') . '%');

        // order by items id => desc
        $order = array('id', 'desc');
        // get items
        $bwatchs = $this->bwatch->getPaginatedItems($limit, $order, $query);

        //TEMP
        $user = $this->user;


        return View::make('plugins.bwatch.index', compact('bwatchs', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /bwatch/create
     *
     * @return Response
     */
    public function create() {
        //accounts
        $accounts = $this->user->account()->lists('name', 'id');
        return View::make('plugins.bwatch.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /bwatch
     *
     * @return Response
     */
    public function store() {

        $rules = array('username' => 'required|unique:bwatchs', 'password' => 'required');

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            Notification::danger('Lütfen kontrol edip tekrar ekleyin.');
            return Redirect::back()->withInput()->withErrors($validator);
        }

        if (Bwatch::where('account_id', '=', $this->user->account_id)->count() >= Config::get('settings.bwatch.limit')) {
            Notification::danger('En fazla ' . Config::get('settings.bwatch.limit') . ' adet hesap eklenebilir!');
            return Redirect::to('bwatch');
        }


        //Check if account has access_token
        $access_token = Account::find($this->user->account_id)->access_token;

        if (!$access_token) {
            Notification::danger('Bu işlemi yapabilmek için API bağlantısının açık olması gerekmektedir!');
            return Redirect::to('account/' . $this->user->account_id . '/edit');
        } else {

            $api = new HttpController();
            $api->url = 'http://api.labelai.com/' . Config::get('settings.api.version') . '/';
            $api->method = 'GET';
            $api->page = 'bwauth';
            $api->params = [
                'access_token' => $access_token,
                'username' => Input::get('username'),
                'password' => Input::get('password')];

            $response = $api->call();

            $response = json_decode($response['data']);

            if ($response->status == FALSE) {
                switch ($response->statusCode) {
                    default:
                        $error = $response->data->error->errors[0]->message;
                        break;
                }

                Notification::danger('Hatalı bir işlem yürütüldü. <br> ErrorCode:' . $response->statusCode . ' : ' . $error);
                return Redirect::to('bwatch');
            } else { //Connected
                Notification::success('BW kullanıcısı başarıyla eklendi.');
                return Redirect::to('bwatch');
            }
        }
    }

    /**
     * Run the BW account but not the related rules.
     * GET /bwatch/{id}/pause
     *
     * @param  int  $id
     * @return Response
     */
    public function run($id) {
        $user = $this->user;

        if (!$id) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        }

        //Select active projects
        $bwatch = Bwatch::find($id);

        if (!$bwatch) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        } else if ($bwatch->account_id != $this->user->account_id) {
            Notification::danger('Sadece size ait BW kullanıcılarını görebilirsiniz.');
            return Redirect::to('bwatch');
        } else if ($bwatch->getSettings()['uiRole'] != 'admin') {
            Notification::danger('Bu BW kullanıcısı admin olmadığı için işlemlere devam edilemiyor.');
            return Redirect::to('bwatch');
        } else {

            //NOT!! run related BW rules. They should be settled manually further.
            //$bwatch->bwrules();
            $data = ["status" => 1];

            if ($bwatch->update($data)) {

                //$bwatch->bwrules()->update($data);

                UserLog::create([
                    'user_id' => $this->user->id,
                    'account_id' => $this->user->account_id,
                    'source' => 'bwatch',
                    'log' => json_encode(array(
                        'text' => $bwatch->username,
                        'action' => 'bw hesabı çalıştırıldı',
                    ))
                ]);


                Notification::success('<b>' . $bwatch->username . '</b> hesabı tekrar çalıştırıldı. <br><b>Ancak kuralları manüel olarak seçip tekrar çalıştırmanız gerekiyor.</b>');
                return Redirect::to('bwatch');
            }
        }

        Notification::warning('İşlem sırasında bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::to('bwatch');
    }

    /**
     * Pause the BW account and related rules.
     * GET /bwatch/{id}/pause
     *
     * @param  int  $id
     * @return Response
     */
    public function pause($id) {
        $user = $this->user;

        if (!$id) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        }

        //Select active projects
        $bwatch = Bwatch::find($id);

        if (!$bwatch) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        } else if ($bwatch->account_id != $this->user->account_id) {
            Notification::danger('Sadece size ait BW kullanıcılarını görebilirsiniz.');
            return Redirect::to('bwatch');
        } else if ($bwatch->getSettings()['uiRole'] != 'admin') {
            Notification::danger('Bu BW kullanıcısı admin olmadığı için işlemlere devam edilemiyor.');
            return Redirect::to('bwatch');
        } else {

            //Also pausing related BW rules.
            $bwatch->bwrules();
            $data = ["status" => 0];

            if ($bwatch->update($data)) {

                $bwatch->bwrules()->update($data);

                UserLog::create([
                    'user_id' => $this->user->id,
                    'account_id' => $this->user->account_id,
                    'source' => 'bwatch',
                    'log' => json_encode(array(
                        'text' => $bwatch->username,
                        'action' => 'bw hesabı duraklatıldı',
                    ))
                ]);


                Notification::success('<b>' . $bwatch->username . '</b> hesabı ve bu hesaba bağlı tüm kurallar başarıyla durduruldu.');
                return Redirect::to('bwatch');
            }
        }
        Notification::warning('İşlem sırasında bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::to('bwatch');
    }

    /**
     * Display the specified resource.
     * GET /bwatch/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * GET /bwatch/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        
    }

    /**
     * Update the specified resource in storage.
     * PUT /bwatch/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /bwatch/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {

        $bwatch = Bwatch::findOrFail($id);


        if (!$bwatch) {
            Notification::warning('BW kullanıcısı bulunamadı!');
            return Redirect::to('bwatch');
        } elseif ($bwatch->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait BW kullanıcılarını görebilirsiniz!');
            return Redirect::to('bwatch');
        } else {

            //Delete related sections.
            //$bwatch->rules()->withTrashed()->forceDelete();
            //->diğerleri de eklenmeli.

            if (Bwatch::where('id', $id)->withTrashed()->forceDelete()) {

                //BW Rules should be deleted too.!!
                $bwatch->bwrules();
                $data = ["is_active" => 0, "status" => 0];
                $bwatch->bwrules()->update($data);
                $bwatch->bwrules()->delete();

                //$bwatch->bwstats()->update($data);
                //$bwatch->bwstats()->delete();

                Notification::warning('BW kullanıcısı silindi!');
                return Redirect::to('bwatch');
            }
        }

        Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::to('bwatch/' . $id);
    }

}
