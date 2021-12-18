<?php

use Yandex\Translate\Translator;
use Yandex\Translate\Exception;

class ClarifiaController extends BaseController {

    protected $type;

    public function __construct() {
        //
    }

    public function auth() {
        $api_url = Config::get('settings.api.clarifia.api_url');
        $client_id = Config::get('settings.api.clarifia.client_id');
        $client_secret = Config::get('settings.api.clarifia.client_secret');

        $cl_token = md5('clarifiatoken');

        if (Cache::has($cl_token)) {
            $result = Cache::get($cl_token);
            return $result;
        } else { //expired
            //Check Connection
            $api = new HttpController();
            $api->url = $api_url;
            $api->page = 'token';
            $api->method = 'POST';
            $api->params = [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'client_credentials'
            ];

            $response = $api->call();

            if ($response['statusCode'] != 200) {
                return json_decode($response);
            }

            if ($response['status'] === TRUE) { //Connected
                $response = json_decode($response['data']);

                if (isset($response->data)) {
                    $response = $response->data;
                }

                Cache::put($cl_token, $response->access_token, $response->expires_in / 60); //the same as Clarifia api
            }

            return $response->access_token;
        }


        return $json_response;
    }

    public function check() {

        $api_url = Config::get('settings.api.clarifia.api_url');
        $access_token = self::auth();

        $result = array();

        $type = Input::get('type', 'label');
        $count = Input::get('count', Config::get('settings.api.functions.image.max_result'));
        $count = ( $count > Config::get('settings.api.functions.image.max_result') ) ? Config::get('settings.api.functions.image.max_result') : $count;

        $lang = Input::get('lang', 'en');
        $lang = (strlen($lang) > 2 OR strlen($lang) < 2) ? 'tr' : $lang;

        //Did they upload a file...
        if (Input::hasFile('image')) {
            // if no errors...
            if (Input::file('image')->isValid()) {


                ini_set('max_execution_time', '300');
                ini_set('memory_limit', '512M');
                ini_set('upload_max_filesize', '16M');
                ini_set('max_execution_time', '3000');

                /* File check
                 * return Redirect
                 */

                #Extension
                $acceptableExtensions = array('jpeg', 'bmp', 'png', 'jpg');
                $extension = Input::file('image')->getClientOriginalExtension();

                if (!in_array($extension, $acceptableExtensions)) {
                    $result['error'] = 'Wrong file format:jpeg/jpg, bmp, png will be accepted!';
                    return $result;
                }

                #FileSize
                $filesize = Input::file('image')->getClientSize();
                $acceptableFileSize = Config::get('settings.api.functions.image.max_size'); //approx.2Mb
                if ($filesize > $acceptableFileSize) {
                    $result['error'] = 'Maximum file size (' . Config::get('settings.api.functions.image.max_size') / 1000000 . ' Mb ) error!';
                    return $result;
                }

                //if everything is fine, save first disk for performance improve. Dealing on cloud was worst!

                $file = Input::file('image');
                $destinationPath = 'uploads/';
                $filename = Str::random(9);

                // check uploads folder exists else create
                if (!File::isDirectory('uploads')) {
                    File::makeDirectory($destinationPath, 0755);
                }

                $filename = $filename . '.' . $file->getClientOriginalExtension();
                $uploadSuccess = $file->move($destinationPath, $filename);

                if (!$uploadSuccess) {
                    $result['error'] = 'File upload error! Please try again later.';
                    return $result;
                } else {
                    $path = $destinationPath . $filename;
                    $valid_file = true;
                }


                // if the file has passed the test
                if ($valid_file) {
                    // convert it to base64
                    $data = file_get_contents($path);
                    $base64 = base64_encode($data);

                    //File loaded now delete.
                    @File::delete($path);

                    $params = ['access_token' => $access_token, 'encoded_data' => $base64];

                    $curl = curl_init();
                    $headers = array("Content-Type:multipart/form-data");
                    curl_setopt($curl, CURLOPT_URL, $api_url . 'tag');
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                    $response = curl_exec($curl);
                    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                    curl_close($curl);

                    $response = bigInt($response);

                    if ($response->status_code != 'OK') {
                        return json_decode($response);
                    } elseif ($response->status_code == 'OK') { //Connected
                        $results = (array) $response->results;

                        if (count($results) === 0) {
                            $results['error'] = 'No results!';
                            return $results;
                        } else {

                            $temp = array();

                            //YandexTranslate
                            $translator = new Translator(Config::get('settings.api.yandex.api_key'));

                            $i = -1;
                            foreach ($results[0]->result->tag->classes as $obj) { //Merge probs
                                $i++;

                                if ($i >= $count)
                                    break;

                                if ($lang != 'en') {
                                    try {
                                        $obj = $translator->translate($obj, 'en-' . $lang)->getResult();
                                        $temp[$i]['label'] = $obj[0];
                                        $temp[$i]['score'] = $results[0]->result->tag->probs[$i];
                                    } catch (Exception $e) {
                                        $result['error'] = $e;
                                    }
                                } else {
                                    $temp[$i]['label'] = $obj;
                                    $temp[$i]['score'] = $results[0]->result->tag->probs[$i];
                                }
                            }

                            return $temp;
                        }
                    } else {
                        $result['error'] = 'Unkown error. Please try again later!';
                        return $result;
                    }
                } else {
                    $result['error'] = 'This is not a regular image!';
                    return $result;
                }
            }
        } else {
            $result['error'] = 'Ooops! image file could not find! ';
            return $result;
        }
    }

    public
            function postag() {

        $type = Input::get('type');

        $translator = new Translator(Config::get('settings.api.yandex.api_key'));
        $text = $translator->translate(Input::get('text'), 'tr-en')->getResult();
        if (count($text)) {
            $text = $text[0];
        } else {
            $result['error'] = 'No result!';
        }

        switch ($type) {
            case "entity":
            default:
                $type = 'analyzeEntities';
                $request_json = '
                    {
                        "document":{
                        "type":"PLAIN_TEXT",
                        "content":"' . $text . '"
                        },
                        "encodingType":"UTF8"
                    }';
                break;
            case "sentiment":
                $type = 'analyzeSentiment';
                $request_json = '
                    {
                        "document":{
                        "type":"PLAIN_TEXT",
                        "content":"' . $text . '"
                        }
                    }';
                break;
            case "annotate":
                $type = 'annotateText';
                $request_json = '
                    {
                        "document":{
                        "type":"PLAIN_TEXT",
                        "content":"' . $text . '"
                        },
                        "features": {
                          "extractSyntax": true,
                          "extractEntities": true,
                          "extractDocumentSentiment": true,
                        },
                        "encodingType":"UTF8",
                    }';
                break;
        }


        $result = array();
        $bucket = Config::get('settings.api.google.bucket', 'sentima');
        $api_key = Config::get('settings.api.google.api_key');
        $cvurl = 'https://language.googleapis.com/v1beta1/documents:' . $type . '?key=' . $api_key;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $cvurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        if ($status == 200) {
            return $json_response;
        } else {
            $result['error'] = 'Unkown error. Please try again!';
            return $result;
        }
    }

}
