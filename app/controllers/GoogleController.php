<?php

use Yandex\Translate\Translator;
use Yandex\Translate\Exception;

class GoogleController extends BaseController {

    protected $type;

    public function __construct() {
        //
    }

    public function imageCheck() {
        $result = array();
        $bucket = Config::get('settings.api.google.bucket', 'sentima');
        $api_key = Config::get('settings.api.google.api_key');
        $cvurl = 'https://vision.googleapis.com/v1/images:annotate?key=' . $api_key;
        $type = Input::get('type', 'label');
        $count = Input::get('count', Config::get('settings.api.functions.image.max_result'));
        $count = ( $count > Config::get('settings.api.functions.image.max_result') ) ? Config::get('settings.api.functions.image.max_result') : $count;

        $lang = Input::get('lang', 'en');
        $lang = (strlen($lang) > 2 OR strlen($lang) < 2) ? 'tr' : $lang;

        switch ($type) {
            case "label":
                $type = 'LABEL_DETECTION';
                $annotation = 'labelAnnotations';
                break;
            case "ocr":
                $type = 'TEXT_DETECTION';
                $annotation = 'textAnnotations';
                $lang = 'en';
                break;
            case "geo":
                $type = 'LANDMARK_DETECTION';
                $annotation = 'landmarkAnnotations';
                $count = 1; //Only one result
                break;
            case "logo":
                $type = 'LOGO_DETECTION';
                $annotation = 'logoAnnotations';
                $lang = 'en';
                break;
            case "safe":
                $type = 'SAFE_SEARCH_DETECTION';
                $annotation = 'safeSearchAnnotation';
                $count = 1;
                break;
            default :
                $type = 'LABEL_DETECTION';
                $annotation = 'labelAnnotations';
                break;
        }

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

                    // Create this JSON
                    $request_json = '
            {
                "requests": [
                    {
                        "image": {
                            "content":"' . $base64 . '"
                        },
                        "features": [
                            {
                                "type": "' . $type . '",
                                "maxResults": "' . $count . '"
                            }
                        ]
                    }
                ]
            }';
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

                        $array_response = json_decode($json_response);

                        $results = (array) $array_response->responses[0];

                        if (count($results) === 0) {
                            $result['error'] = 'No results!';
                            return $result;
                        }

                        $temp = array();

                        if ($annotation == 'safeSearchAnnotation') {
                            $obj = (array) $results[$annotation];

                            foreach ($obj as $key => $val) {
                                switch ($val) {
                                    case "VERY_UNLIKELY":
                                        $score = 0;
                                        break;
                                    case "UNLIKELY":
                                        $score = 30;
                                        break;
                                    case "LIKELY":
                                        $score = 50;
                                        break;
                                    case "VERY_LIKELY":
                                        $score = 70;
                                        break;
                                    case "POSSIBLE":
                                        $score = 90;
                                        break;
                                    default:
                                        $score = $val;
                                        break;
                                }
                                $temp[$key] = $score;
                            }
                        } else {
                            //YandexTranslate
                            $translator = new Translator(Config::get('settings.api.yandex.api_key'));

                            $i = -1;
                            foreach ($results[$annotation] as $obj) {

                                $i++;
                                if ($i >= $count)
                                    break;

                                if ($lang != 'en') {
                                    try {
                                        $label = $translator->translate($obj->description, 'en-' . $lang)->getResult();
                                        $temp[$i]['label'] = $label[0];
                                        $temp[$i]['score'] = $obj->score;
                                    } catch (Exception $e) {
                                        $result['error'] = $e;
                                    }
                                } else {
                                    $temp[$i]['label'] = $obj->description;
                                    if ($annotation == 'labelAnnotations') {
                                        $temp[$i]['score'] = $obj->score;
                                    }
                                }
                            }
                        }
                        return $temp;
                    } else {
                        $result['error'] = 'Unkown error. Please try again later!';
                        return $result;
                    }
                }
            } else {
                // if there is an error, set that to be the returned message
                $result['error'] = 'Ooops!  Your upload triggered the following error:  ' . $file->getErrorMessage();
                return $result;
            }
        } else {
            $result['error'] = 'Ooops! image file could not find! ';
            return $result;
        }
    }

    public function postag() {

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
