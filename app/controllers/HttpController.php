<?php

use Ynk\Repos\Model\ModelRepositoryInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class HttpController extends \BaseController {

    /**
     * 
     */
    public $method = 'GET';
    public $defaultErrorMsg = 'Bilinmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin!';
    public $page = 'auth';
    public $params;

    /**
     * Guzzle Query
     *
     * @return Response
     */
    public function call() {

        if (!$this->url)
            $this->url = Config::get('settings.api.url');

        $client = new \GuzzleHttp\Client(['base_uri' => $this->url]);
        try {
            $response = $client->request($this->method, $this->page, [
                'query' => $this->params
            ]);
 
            if ($response->getStatusCode() == 200) {
                $response = ['status' => TRUE, 'statusCode' => 200, 'data' => $response->getBody()->getContents()];
                return $response;
            }
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $code = $e->getCode();
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            $response = ['status' => FALSE, 'statusCode' => $code, 'error' => json_decode($responseBodyAsString)];

            return $response;
        } catch (GuzzleHttp\Exception\ServerException $e) {
            $code = $e->getCode();
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();

            $response = ['status' => FALSE, 'statusCode' => $code, 'error' => json_decode($responseBodyAsString)];

            return $response;
        }
    }

}
