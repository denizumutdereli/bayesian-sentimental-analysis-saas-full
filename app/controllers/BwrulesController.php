<?php

use Ynk\Repos\Bwrules\BwRulesRepositoryInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class BwrulesController extends BaseController {

    /**
     * @var \Illuminate\Auth\UserInterface|null
     */
    protected $user;
    protected $bwrules;

    /**
     *
     */
    public function __construct(BwRulesRepositoryInterface $bwrules) {
        $this->bwrules = $bwrules;
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     * GET /bwrules
     *
     * @return Response
     */
    public function index($id = null) {

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
            $bwrules = $this->bwrules->getPaginatedItems($limit, $order, $query);

            //TEMP
            $user = $this->user;

            return View::make('plugins.bwatch.rules.index', compact('bwatch', 'bwrules', 'user'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * GET /bwrules/create
     *
     * @return Response
     */
    public function create() {
        $user = $this->user;

        $id = Input::get('id');
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

            $projects = $this->getProjects($bwatch);
            $bwtoken = $bwatch->bw_token;
            $domains = Account::find($this->user->account_id)->domains()->lists('name', 'id');

            //Actions
            $actions = [
                //'category' => 'Kategori değiştir',//
                'sentiment' => 'Sentiment değiştir',
                'delete' => 'Mention Sil',
                'tag' => 'Tag ekle'
            ];
            $queries = [];

            return View::make('plugins.bwatch.rules.create', compact('user', 'projects', 'queries', 'bwtoken', 'domains', 'actions', 'bwatch'));
        }
    }

    /* Get active BW Projects
     * 
     *  response json id
     */

    public function getProjects($bwatch) {
        $api = new HttpController();
        $api->url = 'http://api.labelai.com/' . Config::get('settings.api.version') . '/';
        $api->method = 'GET';
        $api->page = 'bwquery';
        $api->params = [
            'access_token' => $this->user->account->access_token,
            'bw_token' => $bwatch->bw_token,
            'endpoint' => 'projects'];

        $response = $api->call();

        $response = json_decode($response['data']);

        if ($response->status === TRUE) { //data collected
            //Check if user has permission
            $response = $response->data;

            if (isset($response->data)) { //Cache Switch
                $response = $response->data;
            }

            $response = $response->results;

            foreach ($response as $val) {
                $projects[$val->id] = $val->name;
            }

            return $projects;

            if (sizeof($projects) === 0) {
                Notification::danger('Bir kural eklenebilmesi için en az bir adet BW projesinin aktif olması gerekir.');
                return Redirect::to('bwatch');
            }
        } else {
            Notification::danger($response->data->error);
            return Redirect::to('bwatch');
        }
    }

    /**
     * Store a newly created resource in storage.
     * POST /bwrules
     *
     * @return Response
     */
    public function store() {

        $rules = array(
            'name' => 'required',
            'project' => 'required',
            'domain' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        if (Bwrule::where('account_id', '=', $this->user->account_id)->count() >= Config::get('settings.bwatch.rules.limit')) {
            Notification::danger('En fazla ' . Config::get('settings.bwatch.rules.limit') . ' adet kural eklenebilir!');
            return Redirect::to('bwatch');
        }

        //SIMILAR RULES WILL BE CHECKED
        //DOUBLE CHECK WHETHER BW IDS ARE ACTIVE - ie. Tag Ids. Project ids.
        //Check 
        $bwatch = Bwatch::find(Input::get('bwatch_id'));

        if (!$bwatch) {
            Notification::danger('BW kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        } else if ($bwatch->account_id != $this->user->account_id) {
            Notification::danger('Sadece size ait BW kullanıcılarını görebilirsiniz.');
            return Redirect::to('bwatch');
        } else {

            $bwrule = new Bwrule;

            $bwrule->account_id = \Auth::User()->account_id;
            $bwrule->user_id = \Auth::User()->id;
            $bwrule->bwatch_id = $bwatch->id;
            $bwrule->name = uniqueName($bwrule, Input::get('name'));
            $bwrule->bw_token = Input::get('bwtoken');
            $bwrule->project_id = Input::get('project');
            $bwrule->queries = serialize(Input::get('to'));
            $bwrule->domain_id = Input::get('domain');
            $bwrule->action = Input::get('action');
            $bwrule->datamark = Input::get('datamark');
            $bwrule->fromdate = Input::get('fromdate');
            $bwrule->param1 = Input::get('param1');
            $bwrule->param2 = Input::get('param2');
            $bwrule->param3 = Input::get('param3');
            $bwrule->sentiment = Input::get('sentiment');
            $bwrule->categories = Input::get('category');
            $bwrule->tags = (input::get('action') == 'tag') ? serialize(input::get('tagging')) : '';
            $bwrule->delete = (Input::get('delete') ? 1 : 0);
            $bwrule->rule = serialize(Input::all());
            $bwrule->expires_in = $bwatch->expires_in;
            $bwrule->status = 1;
            $bwrule->is_active = 1;

            $bwrule->created_by = $this->user->id;
            $bwrule->updated_by = $this->user->id;

//            echo '<pre>';
//            print_r($bwrule);
//
//            exit;

            if ($bwrule->save()) {
                Notification::success('Kural kaydedildi!');
                return Redirect::to('bwatch/' . $bwatch->id . '/edit');
            } else {
                Notification::warning('Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.');
                return Redirect::to('tag');
            }
        }
    }

    /**
     * Run the BW account rule.
     * GET /bwatch/{id}/pause
     *
     * @param  int  $id
     * @return Response
     */
    public function run($id) {
        $user = $this->user;

        if (!$id) {
            Notification::danger('BW kuralı bulunamadı.');
            return Redirect::to('bwatch');
        }

        $bwrule = Bwrule::find($id);
        $bwatch = Bwatch::find($bwrule->bwatch_id);

        if (!$bwrule) {
            Notification::danger('BW kuralı bulunamadı.');
            return Redirect::to('bwatch');
        } else if ($bwatch->account_id != $this->user->account_id) {
            Notification::danger('Sadece size ait BW hesaplarını görebilirsiniz.');
            return Redirect::to('bwatch');
        } else if ($bwatch->getSettings()['uiRole'] != 'admin') {
            Notification::danger('Bu BW kullanıcısı admin olmadığı için işlemlere devam edilemiyor.');
            return Redirect::to('bwatch');
        } else {


            //If owner BW account paused - rules can not be run.


            if ($bwatch->status == 0) {
                Notification::danger('BW hesabı aktif olmadığı için bu hesaba bağlı kurallar da çalıştırılamaz.');
                return Redirect::to('bwatch');
            }



            //NOT!! run related BW rules. They should be settled manually further.
            //$bwatch->bwrules();
            $data = ["status" => 1];

            if ($bwrule->update($data)) {

                //$bwatch->bwrules()->update($data);

                UserLog::create([
                    'user_id' => $this->user->id,
                    'account_id' => $this->user->account_id,
                    'source' => 'bwrule',
                    'log' => json_encode(array(
                        'text' => $bwrule->name,
                        'action' => 'kural çalıştırıldı',
                    ))
                ]);


                Notification::success('<b>' . $bwrule->name . '</b> kuralı tekrar çalıştırıldı.');
                return Redirect::to('bwatch/' . $bwrule->bwatch_id . '/edit');
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
            Notification::danger('BW kuralı bulunamadı.');
            return Redirect::to('bwatch');
        }

        //Select active projects
        $bwrule = Bwrule::find($id);
        $bwatch = Bwatch::find($bwrule->bwatch_id);

        if (!$bwrule) {
            Notification::danger('Bw kuralı bulunamadı.');
            return Redirect::to('bwatch');
        } else if ($bwatch->account_id != $this->user->account_id) {
            Notification::danger('Sadece size ait BW kurallarını görebilirsiniz.');
            return Redirect::to('bwatch');
        } else if ($bwatch->getSettings()['uiRole'] != 'admin') {
            Notification::danger('Bu BW kullanıcısı admin olmadığı için işlemlere devam edilemiyor.');
            return Redirect::to('bwatch');
        } else {

            $data = ["status" => 0];

            if ($bwrule->update($data)) {

                UserLog::create([
                    'user_id' => $this->user->id,
                    'account_id' => $this->user->account_id,
                    'source' => 'bwrule',
                    'log' => json_encode(array(
                        'text' => $bwrule->name,
                        'action' => 'kural duraklatıldı',
                    ))
                ]);


                Notification::success('<b>' . $bwrule->name . '</b> kuralı başarıyla durduruldu.');
                return Redirect::to('bwatch/' . $bwrule->bwatch_id . '/edit');
            }
        }
        Notification::warning('İşlem sırasında bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::to('bwatch');
    }

    /**
     * Retriew BW Project Queries.
     * GET /bwrules/queries
     *
     * @return Response Json
     */
    public function bwQueryCall() {

        $projectid = Input::get('projectid');
        $bwtoken = Input::get('bwtoken');
        $endpoint = Input::get('endpoint');

        if (!$projectid && !$bwtoken) {
            return Response::json(array('status' => false, 'msg' => 'Proje bulunamadı.'));
        } else {
            $api = new HttpController();
            $api->url = 'http://api.labelai.com/' . Config::get('settings.api.version') . '/';
            $api->method = 'GET';
            $api->page = 'bwquery';
            $api->params = [
                'access_token' => $this->user->account->access_token,
                'bw_token' => $bwtoken,
                'endpoint' => 'projects/' . $projectid . '/' . $endpoint];

            $response = $api->call();

            $response = json_decode($response['data']);

            $data = [];

            if ($response->status === TRUE) { //data collected
                $response = $response->data;
                if (isset($response->data)) {
                    $response = $response->data;
                }

                foreach ($response->results as $val) {
                    $data[$val->id] = $val;
                }

                if (sizeof($data) > 0)
                    $status = true;
                else
                    $status = false;

                return Response::json(array('status' => $status, 'data' => $data));
            }
        }
    }

    /**
     * Display the specified resource.
     * GET /bwrules/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        return $this->index($id);
    }

    /**
     * Show the form for editing the specified resource.
     * GET /bwrules/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {

        $user = $this->user;

        if (!$id) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        }

        //Select active account
        $bwrule = Bwrule::find($id);
        $bwatch = Bwatch::find($bwrule->bwatch_id);

        if (!$bwrule) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        } else if ($bwatch->account_id != $this->user->account_id) {
            Notification::danger('Sadece size ait BW kullanıcıları ve kuralları görebilirsiniz.');
            return Redirect::to('bwatch');
        } else if ($bwatch->getSettings()['uiRole'] != 'admin') {
            Notification::danger('Bu BW kullanıcısı admin olmadığı için işlemlere devam edilemiyor.');
            return Redirect::to('bwatch');
        } else {
            $projects = $this->getProjects($bwatch);
            $bwtoken = $bwatch->bw_token;
            $domains = Account::find($this->user->account_id)->domains()->lists('name', 'id');

            #data building procudure
            $bwrule->queries = unserialize($bwrule->queries);
            $data = "";
            foreach ($bwrule->queries as $val)
                $data .= $val . ',';

            $bwrule->queries = substr($data, 0, -1);

            if ($bwrule->action == 'tag') {
                $bwrule->tags = unserialize($bwrule->tags);
                $data = "";
                foreach ($bwrule->tags as $val)
                    $data .= $val . ',';

                $bwrule->tags = substr($data, 0, -1);
            }

            //Actions
            $actions = [
                //'category' => 'Kategori değiştir',//
                'sentiment' => 'Sentiment değiştir',
                'delete' => 'Mention Sil',
                'tag' => 'Tag ekle'
            ];
            $queries = [];

            return View::make('plugins.bwatch.rules.edit', compact('user', 'projects', 'queries', 'bwtoken', 'domains', 'actions', 'bwrule', 'bwatch'));
        }
    }

    /**
     * Update the specified resource in storage.
     * PUT /bwrules/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $user = $this->user;

        if (!$id) {
            Notification::danger('Bw kullanıcısı bulunamadı.');
            return Redirect::to('bwatch');
        }

        $rules = array(
            'name' => 'required',
            'project' => 'required',
            'domain' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        //SIMILAR RULES WILL BE CHECKED
        //DOUBLE CHECK WHETHER BW IDS ARE ACTIVE - ie. Tag Ids. Project ids.
        //Check 
        $bwrule = Bwrule::find($id);
        $bwatch = Bwatch::find($bwrule->bwatch_id);

        if (!$bwrule) {
            Notification::danger('BW kuralı bulunamadı.');
            return Redirect::to('bwatch');
        } else if ($bwatch->account_id != $this->user->account_id) {
            Notification::danger('Sadece size ait BW kullanıcıları ve kurallarını görebilirsiniz.');
            return Redirect::to('bwatch');
        } else {

            $bwrule->account_id = \Auth::User()->account_id;
            $bwrule->user_id = \Auth::User()->id;
            $bwrule->bwatch_id = $bwatch->id;
            $bwrule->name = ($bwrule->name == Input::get('name')) ? Input::get('name') : uniqueName($bwrule, Input::get('name'));
            //$bwrule->bw_token = Input::get('bwtoken');
            $bwrule->project_id = Input::get('project');
            $bwrule->queries = serialize(Input::get('to'));
            $bwrule->domain_id = Input::get('domain');
            $bwrule->action = Input::get('action');
            $bwrule->fromdate = Input::get('fromdate');
            $bwrule->datamark = Input::get('datamark');
            $bwrule->param1 = Input::get('param1');
            $bwrule->param2 = Input::get('param2');
            $bwrule->param3 = Input::get('param3');
            $bwrule->sentiment = Input::get('sentiment');
            $bwrule->categories = Input::get('category');
            $bwrule->tags = (input::get('action') == 'tag') ? serialize(input::get('tagging')) : '';
            $bwrule->delete = (Input::get('delete') ? 1 : 0);
            $bwrule->rule = serialize(Input::all());
            $bwrule->expires_in = $bwatch->expires_in;
            //$bwrule->status = 1;
            //$bwrule->is_active = 1;
            //$bwrule->created_by = $this->user->id;
            $bwrule->updated_by = $this->user->id;

            if ($bwrule->save()) {
                Notification::success('Kural güncellendi!');
                return Redirect::to('bwatch/' . $bwatch->id . '/edit');
            } else {
                Notification::warning('Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.');
                return Redirect::to('tag');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /bwrules/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {

        $bwrule = Bwrule::find($id);
        $bwatch = Bwatch::find($bwrule->bwatch_id);

        if (!$bwrule) {
            Notification::warning('BW kuralı bulunamadı!');
            return Redirect::to('bwatch');
        } elseif ($bwatch->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait BW kurallarını görebilirsiniz!');
            return Redirect::to('bwatch');
        } else {

            if ($bwrule->delete()) {

                UserLog::create([
                    'user_id' => $this->user->id,
                    'account_id' => $this->user->account_id,
                    'source' => 'bwrule',
                    'log' => json_encode(array(
                        'text' => $bwrule->name,
                        'action' => 'bw kuralı silindi',
                    ))
                ]);

                Notification::warning('BW kuralı silindi!');
                return Redirect::to('bwatch/' . $bwatch->id . '/edit');
            }
        }

        Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::to('bwatch');
    }

}
