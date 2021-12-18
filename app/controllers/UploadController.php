<?php

use Ynk\Repos\Upload\UploadRepositoryInterface;

class UploadController extends \BaseController {

    protected $upload;

    public function __construct(UploadRepositoryInterface $upload) {
        $this->upload = $upload;
        $this->user = Auth::user(); //Current User
    }

    /**
     * Display a listing of the reupload.
     * GET /uploads
     *
     * @return Response
     */
    public function index($id = null) {

        if (!$id)
            return Redirect::to('source');

        //Check 
        $source = Source::find($id);

        if (!$source) {
            Notification::warning('Kaynak kategorisi bulunamadı.');
            return Redirect::to('source');
        } else if ($source->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait Kaynak Dosyalarını görebilirsiniz.');
            return Redirect::to('source');
        } else {
            // limit per page and check limit
            $limit = (Input::get('limit', 10) > 100) ? 100 : Input::get('limit', 10);

            $query = array('name', 'LIKE', '%' . Input::get('q') . '%', 'source_id', '=', $source->id);

            // order by items id => desc
            $order = array('id', 'desc');
            // get items
            $uploads = $this->upload->getPaginatedItems($limit, $order, $query);

            return View::make('upload.index', compact('uploads', 'source'));
        }
    }

    /**
     * Show the form for creating a new reupload.
     * GET /upload/create
     *
     * @return Response
     */
    public function create() {
        return false;
    }

    /**
     * Store a newly created reupload in storage.
     * POST /upload/create
     *
     * @return Response
     */
    public function store() {

        $id = Input::get('source_id');

        if (!$id) {
            Notification::danger('Kaynak kategorisi bulunamadı!');
            return Redirect::back();
        } else {
            $source = Source::find($id);
            if (!$source) {
                Notification::danger('Kaynak kategorisi bulunamadı!');
                return Redirect::back();
            } else if (count($source->uploads) >= Config::get('settings.uploads.limit.file')) {
                Notification::danger('Bir kaynak kategorisine en fazla ' . Config::get('settings.uploads.limit.file') . ' adet yükleme yapılabilir!');
                return Redirect::to('upload/' . $source->id . '/edit');
            } else {
                if (Input::hasFile('csvfile')) {

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
                        case ($data->count() > Config::get('settings.uploads.limit.line')):
                            Notification::danger('Bir seferde en fazla ' . Config::get('settings.uploads.limit.line') . ' kayıt ekleyebilirsiniz.');
                            return Redirect::back();
                            break;
                        case ($data->count() < 10): //unutma def.10
                            Notification::danger('Bir seferde en az 10 kayıt ekleyebilirsiniz. Lütfen örnekte belirtildiği gibi bir Excel dosyası kullandığınızdan emin olun!');
                            return Redirect::back();
                            break;
                    }

                    //if dont have data
                    if (empty($data)) {
                        Notification::danger('Kaynak dosyası yüklenemedi. Lütfen tekrar deneyin!');
                        return Redirect::back();
                    }

                    //First insert file upload info
                    $upload['name'] = $filename;
                    $upload['account_id'] = $source->account_id;
                    $upload['source_id'] = $source->id;
                    $upload['user_id'] = $this->user->id;
                    $upload['count'] = $data->count();
                    $upload['created_by'] = $this->user->id;
                    $upload['updated_by'] = $this->user->id;
                    $upload['created_at'] = \Carbon\Carbon::now();
                    $upload['updated_at'] = \Carbon\Carbon::now();

                    $file_id = Upload::insertGetId($upload);

                    $insert = [];
                    if (!Input::get('firstrow') == 1) {
                        foreach ($data as $key => $value) {

                            if (!empty(trim($value->text))) {
                                $insert[] = [
                                    'post_id' => $value->post_id,
                                    'post_title' => $value->post_title,
                                    'text' => $value->text,
                                    'user_id' => $this->user->id,
                                    'account_id' => $source->account_id,
                                    'source_id' => $id,
                                    'file_id' => $file_id,
                                    'created_at' => \Carbon\Carbon::now(),
                                    'updated_at' => \Carbon\Carbon::now()
                                ];
                            }
                        }

                        if (count($insert)) {
                            $result = DB::table('comments')->insert($insert);
                        } else {
                            //delete temporary upload file
                            Upload::find($file_id)->forceDelete();

                            Notification::danger('Lütfen örnekte belirtildiği gibi bir Excel dosyası kullandığınızdan emin olun!');
                            return Redirect::back();
                        }
                    } else {
                        if ($data->count() > Config::get('settings.uploads.limit.line')) {
                            Notification::danger('İlk satır işaretliyken, bir seferde en fazla ' . Config::get('settings.uploads.limit.line') . ' kayıt ekleyebilirsiniz.');
                            return Redirect::back();
                        } else {

                            $chunk = [];
                            foreach ($data->toArray() as $row) {

                                if (array_key_exists('text', $row)) {
                                    $row['user_id'] = $this->user->id;
                                    $row['account_id'] = $source->account_id;
                                    $row['source_id'] = $id;
                                    $row['file_id'] = $file_id;
                                    $row['created_at'] = \Carbon\Carbon::now();
                                    $row['updated_at'] = \Carbon\Carbon::now();
                                    $chunk[] = $row;
                                }
                            }

                            if (count($chunk)) {
                                //Performance & MariaDb placeholder limit fix.
                                foreach (array_chunk($chunk, 1000) as $record) {
                                    $result = DB::table('comments')->insert($record);
                                }
                            } else {
                                //delete temporary upload file
                                Upload::find($file_id)->forceDelete();

                                Notification::danger('Lütfen örnekte belirtildiği gibi bir Excel dosyası kullandığınızdan emin olun!');
                                return Redirect::back();
                            }
                        }
                    }

                    if ($result) {
                        //Record the request
                        Ping::create(
                                [
                                    'account_id' => $source->account_id,
                                    'user_id' => $this->user->id,
                                    'section' => 'source',
                                    'amount' => $data->count()
                                ]
                        );

                        Notification::success('Kaynak dosyası yüklendi!');
                        return Redirect::to('upload/' . $source->id);
                    }
                } else {
                    Notification::danger('Yükleme dosyası bulunamadı!');
                    return Redirect::back();
                }
            }
        }
    }

    /**
     * Display the specified reupload.
     * GET /upload/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        Return $this->index($id);
    }

    /**
     * Show the form for editing the specified reupload.
     * GET /upload/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        Return $this->index($id);
    }

    /**
     * Update the specified reupload in storage.
     * PUT /upload/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update() {


        if (Request::ajax()) {


            //Permisson check.
            if (!Config::get('settings.actions.limited.upload.active')) {
                $response['success'] = false;
                $response['msg'] = 'Dosya isimlerini değiştirmeye, yetkili değilsiniz!';
                return $response;
            }

            $request = Request::all();

            $upload = Upload::findOrFail($request['pk']);
            $value = $request['value'];

            if (!$upload) {
                $response['success'] = false;
                $response['msg'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            } else if (trim($value) == '' || strlen($value) > 20) {
                $response['success'] = false;
                $response['msg'] = 'Dosya ismi boş bırakılamaz ve en fazla 20 karakter girilebilir!';
            } else {
                $upload->name = $value;
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
     * DELETE /domain/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        $upload = Upload::find($id);

        if ($upload->account_id != $this->user->account_id) {
            Notification::warning('Sadece size ait Kaynak Kategorilerini silebilirsiniz.!');
            return Redirect::back();
        } else {

            //Delete related sections.
            //$upload->comments()->forceDelete(); LARAVEL BUG
            DB::table('comments')->where('file_id', '=', $upload->id)->delete();

            UserLog::create([
                'user_id' => $this->user->id,
                'account_id' => $this->user->account_id,
                'source' => 'upload',
                'log' => json_encode(array(
                    'text' => $upload->name . ':' . $upload->id,
                    'action' => 'silindi',
                ))
            ]);

            $upload->forceDelete();

            Notification::success('Kayıt silindi.');
            return Redirect::back();
        }

        Notification::warning('Kayıt silinirken bir hata oluştu lütfen daha sonra tekrar deneyiniz.');
        return Redirect::back();
    }

}
