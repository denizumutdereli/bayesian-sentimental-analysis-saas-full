<?php

use Ynk\Repos\Tagupload\TagUploadRepositoryInterface;

class TagUploadController extends \BaseController {

    protected $upload;

    public function __construct(TagUploadRepositoryInterface $upload) {
        $this->upload = $upload;
        $this->user = Auth::user(); //Current User
    }

    /**
     * Display a listing of the reupload.
     * GET /tagupload
     *
     * @return Response
     */
    public function index($id = null) {

        if (!$id)
            return Redirect::to('tag');

        //Check 
        $tag = Tag::find($id);

        if (!$tag) {
            Notification::warning('Kelime kategorisi bulunamadı.');
            return Redirect::to('tag');
        } else if ($tag->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait Kelime Dosyalarını görebilirsiniz.');
            return Redirect::to('tag');
        } else {
            // limit per page and check limit
            $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

            $query = array('tag', 'LIKE', '%' . Input::get('q') . '%', 'tag_id', '=', $tag->id);

            // order by items id => desc
            $order = array('id', 'desc');
            // get items
            $uploads = $this->upload->getPaginatedItems($limit, $order, $query);

            return View::make('tagupload.index', compact('uploads', 'tag'));
        }
    }

    /**
     * Show the form for creating a new reupload.
     * GET /tagupload/create
     *
     * @return Response
     */
    public function create() {
        return false;
    }

    /**
     * Store a newly created reupload in storage.
     * POST /tagupload/create
     *
     * @return Response
     */
    public function store() {

        $id = Input::get('tag_id');

        if (!$id) {
            Notification::danger('Kelime kategorisi bulunamadı!');
            return Redirect::back()->withInput();
        } else {
            $tag = Tag::find($id);
            if (!$tag) {
                Notification::danger('Kelime kategorisi bulunamadı!');
                return Redirect::back()->withInput();
            } elseif (Input::hasFile('csvfile')) {

                ini_set('max_execution_time', '300');
                ini_set('memory_limit', '512M');
                ini_set('upload_max_filesize', '16M');
                ini_set('max_execution_time', '3000');

                /* File check
                 * return Redirect
                 */

                #Extension
                $acceptableExtensions = array('xls', 'xlsx');
                $extension = Input::file('csvfile')->getClientOriginalExtension();

                if (!in_array($extension, $acceptableExtensions)) {
                    Notification::danger('Yanlış dosya biçimi. Sadece Excel dosyaları yüklenebilir! XLS, XLSX');
                    return Redirect::back();
                }

                #FileSize
                $filesize = Input::file('csvfile')->getClientSize();
                $acceptableFileSize = 5000000; //approx.2Mb
                if ($filesize > $acceptableFileSize) {
                    Notification::danger('Bir seferde enfazla 5MB dosya yüklenebilir! Lütfen daha küçük bir dosya ile tekrar deneyin.');
                    return Redirect::back();
                }

                //if everything is fine, save first disk for performance improve. Dealing on cloud was worst!

                $file = Input::file('csvfile');
                $destinationPath = 'uploads/';
                $filename = Str::random(9);

                // check uploads folder exists else create
                if (!File::isDirectory('uploads')) {
                    File::makeDirectory($destinationPath, 0755);
                }

                $filename = $filename . '.' . $file->getClientOriginalExtension();
                $uploadSuccess = $file->move($destinationPath, $filename);

                if (!$uploadSuccess) {
                    Notification::success('Dosya yüklenirken bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.');
                    return Redirect::back();
                }

                $path = $destinationPath . $filename;
                $data = Excel::selectSheetsByIndex(0)->load($path, function($reader) {
                            
                        })->calculate(false)->get();

                //File loaded now delete.
                @File::delete($path);

                switch ($data->count()) {
                    case ($data->count() > Config::get('settings.taguploads.limit')):
                        Notification::danger('Bir seferde en fazla ' . Config::get('settings.taguploads.limit') . ' kayıt ekleyebilirsiniz.');
                        return Redirect::back();
                        break;
                    case ($data->count() < 10): //unutma def.10
                        Notification::danger('Bir seferde en az 10 kayıt ekleyebilirsiniz. Lütfen örnekte belirtildiği gibi bir Excel dosyası kullandığınızdan emin olun!');
                        return Redirect::back();
                        break;
                }

                //if dont have data
                if (empty($data)) {
                    Notification::danger('Kelime dosyası yüklenemedi. Lütfen tekrar deneyin!');
                    return Redirect::back();
                }

                $insert = array();
                foreach ($data as $key => $value) {

                    $value->tag = trim($value->tag);

                    if (strlen($value->tag) > Config::get('settings.tags.maxlength'))
                        $max[] = $value->tag;
                    else {
                        //Check if previously added
                        $checkdb = TagUpload::where('tag', '=', $value->tag)
                                        ->where('tag_id', '=', $id)
                                        ->where('account_id', '=', $tag->account_id)->first();

                        if (count($checkdb)) {
                            $dublicate[] = $value->tag;
                        } else {
                            if (isset($value->tag) && !empty($value->tag)) {

                                $insert[$value->tag] = [
                                    'tag' => $value->tag,
                                    'type' => 'upload',
                                    'user_id' => $this->user->id,
                                    'account_id' => $tag->account_id,
                                    'tag_id' => $id,
                                    'created_at' => \Carbon\Carbon::now(),
                                    'updated_at' => \Carbon\Carbon::now(),
                                    'created_by' => $this->user->id,
                                    'updated_by' => $this->user->id
                                ];
                            }
                        }
                    }
                }
            } elseif (Input::has('tag_id')) {

                $rules = array(
                    'tag' => 'required'
                );

                $validator = Validator::make(Input::all(), $rules);
                if ($validator->fails()) {
                    return Redirect::back()->withInput()->withErrors($validator);
                }

                if (TagUpload::where('tag_id', '=', Input::get('tag_id'))->count() >= Config::get('settings.taguploads.limit')) {
                    Notification::danger('Bir kategoriye en fazla ' . Config::get('settings.taguploads.limit') . ' kayıt ekleyebilirsiniz.');
                    return Redirect::to('tagupload/' . $tag->id);
                }

                $value = trim(Input::get('tag'));

                if (strlen($value) > Config::get('settings.tags.maxlength'))
                    $max[] = $value;
                else {
                    //Check if previously added
                    $checkdb = TagUpload::where('tag', '=', $value)
                                    ->where('tag_id', '=', $id)
                                    ->where('account_id', '=', $tag->account_id)->first();

                    if (count($checkdb)) {
                        Notification::warning('Bu kelime daha önce kaydedilmiş!');
                        return Redirect::back()->withInput();
                    } else {
                        $insert[$value] = [
                            'tag' => $value,
                            'type' => 'manual',
                            'user_id' => $this->user->id,
                            'account_id' => $tag->account_id,
                            'tag_id' => $id,
                            'created_at' => \Carbon\Carbon::now(),
                            'updated_at' => \Carbon\Carbon::now(),
                            'created_by' => $this->user->id,
                            'updated_by' => $this->user->id
                        ];
                    }
                }
            }

            if (count($insert))
                $result = DB::table('tag_uploads')->insert($insert);
            else {
                Notification::danger('Lütfen örnekte belirtildiği gibi bir Excel dosyası kullandığınızdan emin olun!');
                return Redirect::back();
            }

            if ($result) {
                
                //Record the request
                        Ping::create(
                                [
                                    'account_id' => $tag->account_id,
                                    'user_id' => $this->user->id,
                                    'section' => 'tag',
                                    'amount' => count($insert)
                                ]
                        );
                
                Notification::success('Kelime dosyası yüklendi!');
                return Redirect::to('tagupload/' . $tag->id);
            } else {
                Notification::warning('Geçersiz bir işlem yürütüldü. Lütfen daha sonra tekrar deneyin!');
                return Redirect::to('tagupload/' . $tag->id);
            }
        }
    }

    /**
     * Display the specified reupload.
     * GET /tagupload/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        Return $this->index($id);
    }

    /**
     * Show the form for editing the specified reupload.
     * GET /tagupload/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        $tag = TagUpload::find($id);

        if (!$tag) {
            Notification::warning('Kelime bulunamadı!');
            return Redirect::to('tag');
        } elseif ($tag->account_id != $this->user->account_id) {

            Notification::warning('Sadece size ait kategorileri görebilirsiniz!');
            return Redirect::to('tag');
        }

        //accounts
        $accounts = $this->user->account()->lists('name', 'id');

        return View::make('tagupload.edit', compact('tag', 'accounts'));
    }

    /**
     * Show the form for create the specified reupload.
     * GET /tagupload/add
     *
     * @param  int  $id
     * @return Response
     */
    public function add() {
        $id = Input::get('id');

        $tag = Tag::find($id);
        if (!$id OR ! $tag) {
            Notification::warning('Lütfen kelime kategorisi seçin.!');
            return Redirect::to('tag');
        } elseif ($tag->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait kelimeleri güncelleyebilirsiniz.!');
            return Redirect::back();
        } else {
            return View::make('tagupload.create', compact('tag'));
        }
    }

    /**
     * Update the specified reupload in storage.
     * PUT /tagupload/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {
        $tag = TagUpload::find($id);
        if ($tag->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait kelimeleri güncelleyebilirsiniz.!');
            return Redirect::to('tagupload/' . $tag->tag_id);
        } else {

            $rules = array(
                'tag' => 'required'
            );

            $validator = Validator::make(Input::all(), $rules);
            if ($validator->fails()) {
                return Redirect::back()->withInput()->withErrors($validator);
            }

            //check dublicates and unique Laravel 4 Bug.
            $checkdb = TagUpload::where('tag', '=', trim(Input::get('tag')))
                            ->where('tag_id', '=', $tag->tag_id)
                            ->where('tag', '!=', $tag->tag)
                            ->where('account_id', '=', $this->user->account_id)->first();

            if (count($checkdb)) {
                Notification::danger('Bu kelime daha önce eklenmiş. Lütfen farklı bir kelime yazın!');
                return Redirect::back()->withInput();
            }

            $tag->tag = trim(Input::get('tag'));
            if ($tag->save()) {
                Notification::success('Kelime güncellendi!');
                return Redirect::to('tagupload/' . $tag->tag_id);
            } else {
                Notification::warning('İşlem sırasında bir hata oluştu, lütfen daha sonra tekrar deneyin!');
                return Redirect::back()->withInput()->withErrors();
            }
        }
    }

    /**
     * Update the specified reupload in storage.
     * PUT /domain/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function updateAjaxTag() {

        if (Request::ajax()) {

            //Permisson check.
            if (!Config::get('settings.actions.limited.tagupload.active')) {
                $response['success'] = false;
                $response['msg'] = 'Dosya isimlerini değiştirmeye, yetkili değilsiniz!';
                return $response;
            }

            $request = Request::all();

            $upload = TagUpload::findOrFail($request['pk']);
            $value = trim($request['value']);

            if (!$upload) {
                $response['success'] = false;
                $response['msg'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            } else if (trim($value) == '' || strlen($value) > Config::get('settings.tags.maxlength')) {
                $response['success'] = false;
                $response['msg'] = 'Dosya ismi boş bırakılamaz ve en fazla ' . Config::get('settings.tags.maxlength') . ' karakter girilebilir!';
            } else {

                //check dublicates and unique Laravel 4 Bug.
                $checkdb = TagUpload::where('tag', '=', $value)
                                ->where('tag_id', '=', $upload->tag_id)
                                ->where('tag', '!=', $upload->tag)
                                ->where('account_id', '=', $this->user->account_id)->first();
                if (count($checkdb)) {
                    $response['success'] = false;
                    $response['msg'] = 'Bu kelime daha önce kaydedilmiş.';
                    return $response;
                }

                $upload->tag = $value;
                $upload->save();
                if (!$upload) {
                    $response['success'] = false;
                    $response['msg'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                } else {
                    $response['success'] = true;
                }
            }
            return $response;
        }
    }

    /**
     * Remove the specified reupload from storage.
     * DELETE /tagupload/delete/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function delete() {
        $id = Input::get('id');
        $upload = TagUpload::find($id);

        if ($upload->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait kelimeleri silebilirsiniz.!');
            return Redirect::to('tagupload');

            if (Request::isXmlHttpRequest()) {
                $result = array(
                    'response' => 0,
                    'data' => $tag,
                );
                return Response::json($result);
            }
        }

        $user = Auth::user();
        UserLog::create([
            'user_id' => $user->id,
            'upload' => 'tagupload',
            'log' => json_encode(array(
                'text' => $upload->tag,
                'action' => 'silindi',
            ))
        ]);

        $upload->forceDelete();

        Notification::success('Kayıt silindi.');

        if (Request::isXmlHttpRequest()) {
            $result = array(
                'response' => 1
            );
            return Response::json($result);
        }
        return Redirect::back();


        Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');

        if (Request::isXmlHttpRequest()) {
            $result = array(
                'response' => 0
            );
            return Response::json($result);
        }
        return Redirect::back();
    }

    /**
     * add the specified tag from storage.
     * Add /tag/add
     *
     * @param  int  $id
     * @return Response
     */
    public function addTag() {

        if (Request::ajax() && Input::has('tag_id') && Input::has('tag')) {

            $tag = trim(Input::get('tag'));
            $tag_id = Input::get('tag_id');


            if (strlen($tag) > Config::get('settings.tags.maxlength')) {
                $test = ['response' => 0, 'msg' => 'Bu kelime çok uzun olduğu için eklenemiyor. Max: ' . Config::get('settings.tags.maxlength') . ' harf uzunlukta kelime eklenebilir.'];
                return Response::json($test);
            } else {
                //Check if previously added
                $checkdb = TagUpload::where('tag', '=', $tag)
                                ->where('tag_id', '=', $tag_id)
                                ->where('account_id', '=', $this->user->account_id)->first();

                if (count($checkdb)) {
                    $result = array('response' => 0, 'msg' => 'Bu kelime daha önce kaydedilmiş!');
                    return Response::json($result);
                } else {
                    $insert = [
                        'tag' => $tag,
                        'type' => 'manual',
                        'user_id' => $this->user->id,
                        'account_id' => $this->user->account_id,
                        'tag_id' => $tag_id,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                        'created_by' => $this->user->id,
                        'updated_by' => $this->user->id
                    ];
                }
            }

            $result = TagUpload::create($insert);

            if ($result) {
                $result = array('response' => 1, 'id' => $result->id, 'tag' => $result, 'msg' => 'Kayıt eklendi.');
                return Response::json($result);
            }
        }
        $result = array('response' => 0, 'msg' => 'İşlem sırasında bir hata oluştu, lütfen bir süre sonra tekrar deneyin!');
        return Response::json($result);
    }

    /**
     * Remove the specified tag from storage.
     * DELETE /tag/remove
     *
     * @param  int  $id
     * @return Response
     */
    public function removeTag() {

        if (Request::ajax() && Input::has('tag_id')) {

            $tag = TagUpload::where('id', '=', Input::get('tag_id'))->first();

            if (!$tag || $tag->account_id != $this->user->account_id) {
                $result = array('response' => 0, 'msg' => 'Sadece size ait kelimeleri silebilirsiniz!');
                return Response::json($result);
            } else {
                $delete = TagUpload::where('id', '=', Input::get('tag_id'))->where('account_id', '=', $this->user->account_id)->forceDelete();
            }


            if ($delete) {
                //Check availability of TagUpload index, if neccessary delete all.
                $tagupload_check = TagUpload::where('tag_id', '=', $tag->tag_id)->get();

                UserLog::create([
                    'user_id' => $this->user->id,
                    'source' => 'tag',
                    'log' => json_encode(array(
                        'text' => '{' . Input::get('tag_id') . ' : ' . Input::get('tag') . '} nolu tag',
                        'action' => 'silindi',
                    ))
                ]);

                $result = array('response' => 1);
                return Response::json($result);
            }

            $result = array('response' => 0, 'msg' => 'İşlem sırasında bir hata oluştu, lütfen bir süre sonra tekrar deneyin!');
            return Response::json($result);
        } else {
            $result = array('response' => 0, 'msg' => 'Bu işleme yetkiniz bulunmamaktadır!');
            return Response::json($result);
        }
    }

}
