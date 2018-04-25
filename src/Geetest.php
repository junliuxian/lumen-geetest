<?php
namespace Junliuxian\Geetest;

class Geetest
{
    const GT_SDK_VERSION = 'php_3.0.0';

    const GT_URL         = 'http://api.geetest.com/';

    /**
     * HTTP request connect timeout time.
     *
     * @var int
     */
    public static $connectTimeout = 1;

    /**
     * HTTP request timeout time.
     *
     * @var int
     */
    public static $socketTimeout  = 1;


    /**
     * Geetest authorized ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Geetest authorized key
     *
     * @var string
     */
    protected $key;

    /**
     * @param string $id
     * @param string $key
     */
    public function __construct($id, $key)
    {
        $this->id  = $id;
        $this->key = $key;
    }

    /**
     * Get the validation parameters.
     *
     * @param array $param
     * @param int $newCaptcha
     * @return array
     */
    public function preProcess($param, $newCaptcha = 1)
    {
        $response = $this->request('register.php', array_merge([
            'gt'          => $this->id,
            'new_captcha' => $newCaptcha
        ], $param));

        $status    = strlen($response) == 32;
        $challenge = $status ? md5($response.$this->key) : md5(rand(0, 100)).substr(md5(rand(0, 100)), 0, 2);
        return [
            'success'     => $status,
            'gt'          => $this->id,
            'challenge'   => $challenge,
            'new_captcha' => $newCaptcha
        ];
    }

    /**
     * validation result.
     *
     * @param bool $status
     * @param string $challenge
     * @param string $validate
     * @param string $seccode
     * @param array $param
     * @param int $jsonFormat
     * @return bool
     */
    public function validate($status, $challenge, $validate, $seccode, $param, $jsonFormat = 1)
    {
        if ($status) {
            return $this->successValidate($challenge, $validate, $seccode, $param, $jsonFormat);
        }

        return $this->failValidate($challenge, $validate, $seccode);
    }

    /**
     * Get validation result by normal mode.
     *
     * @param string $challenge
     * @param string $validate
     * @param string $seccode
     * @param array $param
     * @param int $jsonFormat
     * @return bool
     */
    public function successValidate($challenge, $validate, $seccode, $param, $jsonFormat = 1)
    {
        if (!$this->checkValidate($challenge, $validate)) {
            return false;
        }

        $response = $this->request('validate.php',array_merge([
            'seccode'     => $seccode,
            'challenge'   => $challenge,
            'captchaid'   => $this->id,
            'json_format' => $jsonFormat,
            'sdk'         => self::GT_SDK_VERSION,
            'timestamp'   => time()
        ], $param), null, 'POST');

        $res = json_decode($response);
        return $res && $res->seccode == md5($seccode);
    }

    /**
     * Get validation result by crash mode.
     *
     * @param string $challenge
     * @param $validate
     * @param string $seccode
     * @return bool
     */
    public function failValidate($challenge, $validate, $seccode)
    {
        return md5($challenge) == $validate;
    }

    /**
     * Pretest verification parameters.
     *
     * @param string $challenge
     * @param string $validate
     * @return bool
     */
    private function checkValidate($challenge, $validate)
    {
        if (strlen($validate) != 32) {
            return false;
        }

        if (md5($this->key . 'geetest' . $challenge) != $validate) {
            return false;
        }

        return true;
    }

    /**
     * Send HTTP request.
     *
     * @param string $path
     * @param string|array|null $data
     * @param array|null $headers
     * @param string $method
     * @return mixed
     */
    protected function request($path, $data = null, $headers = null, $method = 'GET')
    {
        list($url, $method, $data, $headers) = $this->buildRequest($path, $method, $data, $headers);

        if (function_exists('curl_exec')) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$socketTimeout);

            if ($headers !== null) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, explode("\r\n", $headers));
            }

            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }

            $response = curl_exec($ch);
            $errno    = curl_errno($ch);

            curl_close($ch);

            if ($errno === 0) {
                return $response;
            }

        } else {

            $options['http'] = [
                'method'  => $method,
                'content' => $data,
                'header'  => $headers,
                'timeout' => self::$connectTimeout + self::$socketTimeout,
            ];

            $context  = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);

            if ($response){
                return $response;
            }
        }

        return 0;
    }

    /**
     * build request context.
     *
     * @param string $path
     * @param string $method
     * @param array|string|null $data
     * @param array|string|null $headers
     * @return array
     */
    protected function buildRequest($path, $method = 'GET', $data = null, $headers = null)
    {
        $method = strtoupper($method);

        if ($data !== null) {
            $data = http_build_query($data);

            // GET/HEAD/DELETE
            if (!in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $path .= (strrpos($path, '?') !== false?'&':'?').$data;
                $data = null;
            }
        }

        if (is_array($headers)) {
            $headers = implode("\r\n", $headers);
        }

        if ($method == 'POST') {

            if (stripos($headers, 'Content-type:') === false) {
                $headers .= "\r\nContent-type: application/x-www-form-urlencoded";
            }

            $headers .= "\r\nContent-Length: ".strlen($data);
        }

        return [self::GT_URL.$path, $method, $data, $headers];
    }
}