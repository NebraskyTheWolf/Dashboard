<?php

/**
 * Class Requester
 *
 * The Requester class is responsible for making HTTP requests to a specified endpoint.
 * It supports different request methods such as GET, POST, PUT, DELETE.
 */
class Requester {
    private $endpoint = '';
    private $accessToken = '';

    public function __construct($endpoint = 'http://127.0.0.1:8080', $accessToken) {
        $this->endpoint = $endpoint;
        $this->accessToken = $accessToken;
    }

    private function __request($action, $method = 'GET', $body = []) {
        // Init request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->endpoint.$action);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // headers
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if (!empty($body))
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        // execute
        $result = @json_decode(curl_exec($curl), true);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_errno($curl);
        curl_close($curl);

        return [$result, $code, $error];
    }

    public function get($route, $method = 'GET', $body = array()) {
        list($body, $code, $error) = $this->__request($route, $method, $body);
        return (object)[
            'status' => ($code === 200),
            'code' => $code,
            'success' => (isset($body['status'])) ? $body['status'] : false,
            'error' => (isset($body['error'])) ? $body['error'] : '',
            'body' => (isset($body['data'])) ? $body['data'] : ''
        ];
    }
}
