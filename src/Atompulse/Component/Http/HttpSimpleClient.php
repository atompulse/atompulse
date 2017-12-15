<?php
namespace Atompulse\Component\Http;

/**
 * Class HttpSimpleClient
 * @package Atompulse\Component\Http
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class HttpSimpleClient
{
    protected $res = null;

    protected $options = [
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_RETURNTRANSFER => true, // return value of curl_exec() instead of outputting it out directly
        CURLOPT_HEADER => false,        // include the header in the output
        CURLOPT_TIMEOUT => 60,          // seconds
        CURLOPT_USERAGENT => 'PHP CLIENT'
    ];

    protected $lastUrl = null;
    protected $lastResponse = null;

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (count($options)) {
            $this->addOptions($options);
        }
    }

    /**
     * @param $option
     * @param $optionValue
     */
    public function set($option, $optionValue)
    {
        $this->options[$option] = $optionValue;
    }

    /**
     * @param $options
     */
    public function addOptions($options)
    {
        foreach ($options as $optionName => $optionValue) {
            $this->set($optionName, $optionValue);
        }
    }

    /**
     * Perform an http request
     * @param string $url
     * @param string $method
     * @param mixed $params
     * @return mixed|null
     */
    public function request($url, $method = 'GET', $params = null)
    {
        $this->res = curl_init();

        switch ($method) {
            default:
            case 'GET' :
                if ($params) {
                    $url = $url .(strpos($url, '?') === FALSE ? '?' : ''). http_build_query($params);
                }
                break;
            case 'POST' :
                $this->set(CURLOPT_POST, true);
                $this->set(CURLOPT_POSTFIELDS, $params); // add POST fields
                break;
            case 'PUT' :
                $this->set(CURLOPT_PUT, true);
                $this->set(CURLOPT_POSTFIELDS, $params); // add PUT
                break;
        }

        $this->set(CURLOPT_URL, $url);
        $this->lastUrl = $url;

        $this->applyOptions();

        $this->lastResponse = curl_exec($this->res);

        return $this->lastResponse;
    }

    /**
     * Get used url
     * Useful when request was GET and parameters were sent as array
     * @return string|null
     */
    public function getUrl()
    {
        return $this->lastUrl;
    }

    /**
     * Gets the request headers as a string.
     *
     * @return string
     */
    public function getRequestHeaders()
    {
        return curl_getinfo($this->res, CURLINFO_HEADER_OUT);
    }

    /**
     * Gets the whole response (including headers) as a string.
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Gets the response body as a string.
     *
     * @return string
     */
    public function getRespBody()
    {
        $headerSize = curl_getinfo($this->res, CURLINFO_HEADER_SIZE);

        return substr($this->lastResponse, $headerSize);
    }

    /**
     * Gets the response content type.
     *
     * @return string
     */
    public function getRespContentType()
    {
        return curl_getinfo($this->res, CURLINFO_CONTENT_TYPE);
    }

    /**
     * Gets the response headers as a string.
     *
     * @return string
     */
    public function getRespHeaders()
    {
        $headerSize = curl_getinfo($this->res, CURLINFO_HEADER_SIZE);

        return substr($this->lastResponse, 0, $headerSize);
    }

    /**
     * Gets the response http status code.
     *
     * @return string
     */
    public function getRespStatusCode()
    {
        return curl_getinfo($this->res, CURLINFO_HTTP_CODE);
    }    
    
    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->res) {
            curl_close($this->res);
        }
    }

    /**
     * Apply all the options
     * @return bool|void
     */
    protected function applyOptions()
    {
        return curl_setopt_array($this->res, $this->options);
    }
}
