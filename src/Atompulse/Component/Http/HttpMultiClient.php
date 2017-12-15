<?php
namespace Atompulse\Component\Http;

/**
 * Class HttpClient
 *
 * Simple CURL wrapper to enable concurrent http requests
 *
 * @package Atompulse\Component\Http
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class HttpMultiClient
{
    private $_maxConcurrent = 0; //max. number of simultaneous connections allowed
    private $_options = []; //shared cURL options
    private $_headers = []; //shared cURL request headers
    private $_callback = null; //default callback
    private $_timeout = 5000; //timeout used for curl_multi_select function
    private $requests = []; //request_queue

    /**
     * @param int $max_concurrent
     */
    public function __construct($max_concurrent = 10)
    {
        $this->setMaxConcurrent($max_concurrent);
    }

    /**
     * @param $max_requests
     */
    public function setMaxConcurrent($max_requests)
    {
        if ($max_requests > 0) {
            $this->_maxConcurrent = $max_requests;
        }
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        if (is_array($headers) && count($headers)) {
            $this->_headers = $headers;
        }
    }

    /**
     * @param callable $callback
     */
    public function setCallback(callable $callback)
    {
        $this->_callback = $callback;
    }

    /**
     * @param $timeout
     */
    public function setTimeout($timeout)
    {
        //in milliseconds
        if ($timeout > 0) {
            $this->_timeout = $timeout / 1000; // to seconds
        }
    }

    /**
     * Add a request to the request queue
     * @param $url
     * @param null $postData
     * @param callable|null $callback
     * @param null $userData
     * @param array|null $options
     * @param array|null $headers
     * @return int
     */
    public function addRequest(
        $url,
        $postData = null,
        array $headers = null, //individual cURL request headers
        array $options = null, //individual cURL options
        callable $callback = null, //individual callback
        $userData = null
    )
    {
        //Add to request queue
        $this->requests[] = [
            'url' => $url,
            'postData' => ($postData) ? $postData : null,
            'callback' => ($callback) ? $callback : $this->_callback,
            'userData' => ($userData) ? $userData : null,
            'options' => ($options) ? $options : null,
            'headers' => ($headers) ? $headers : null
        ];

        return count($this->requests) - 1; //return request number/index
    }

    /**
     * Execute the request queue
     */
    public function execute()
    {
        $max_concurrent = min(count($this->requests), $this->_maxConcurrent);

        //the request map that maps the request queue to request curl handles
        $requests_map = [];
        $multi_handle = curl_multi_init();

        //start processing the initial request queue
        for ($i = 0; $i < $max_concurrent; $i++) {
            $ch = curl_init();
            $request =& $this->requests[$i];
            $this->addTimer($request);
            curl_setopt_array($ch, $this->buildOptions($request));
            curl_multi_add_handle($multi_handle, $ch);
            //add curl handle of a request to the request map
            $key = (string)$ch;
            $requests_map[$key] = $i;
        }
        do {
            while (($mh_status = curl_multi_exec($multi_handle, $active)) == CURLM_CALL_MULTI_PERFORM) {
                ;
            }
            if ($mh_status != CURLM_OK) {
                break;
            }
            // a request is just completed, find out which one
            while ($completed = curl_multi_info_read($multi_handle)) {
                $ch = $completed['handle'];
                $request_info = curl_getinfo($ch);
                $response = curl_multi_getcontent($ch);

                //get request info
                $key = (string)$ch;
                $request =& $this->requests[$requests_map[$key]]; //map handler to request index to get request info
                $url = $request['url'];
                $callback = $request['callback'];
                $user_data = $request['userData'];
                $options = $request['options'];
                $this->stopTimer($request); //record request time
                $time = $request['time'];

                if ($response && (isset($this->_options[CURLOPT_HEADER]) || isset($options[CURLOPT_HEADER]))) {
                    $k = intval($request_info['header_size']);
                    $request_info['response_header'] = substr($response, 0, $k);
                    $response = substr($response, $k);
                }

                //remove completed request and its curl handle
                unset($requests_map[$key]);
                curl_multi_remove_handle($multi_handle, $ch);

                // call the callback function and pass request info and user data to it
                if ($callback) {
                    if (is_array($callback)) {
                        call_user_func_array($callback, [
                            'response' => $response,
                            'request_info' => $request_info,
                            'url' => $url,
                            'time' => $time,
                            'user_data' => $user_data,
                        ]);
                    } else {
                        call_user_func($callback, $response, $request_info, $url, $time, $user_data);
                    }
                }

                $request = null; //free up memory now just incase response was large

                //add/start a new request to the request queue
                if ($i < count($this->requests) && isset($this->requests[$i])) { //if requests left
                    $ch = curl_init();
                    $request =& $this->requests[$i];
                    $this->addTimer($request);
                    curl_setopt_array($ch, $this->buildOptions($request));
                    curl_multi_add_handle($multi_handle, $ch);
                    //add curl handle of a new request to the request map
                    $key = (string)$ch;
                    $requests_map[$key] = $i;
                    $i++;
                }
            }
            if ($active) {
                if (curl_multi_select($multi_handle, $this->_timeout) === -1) { //wait for activity on any connection
                    usleep(5);
                }
            }
        } while ($active || count($requests_map)); //End do-while

        $this->reset();

        curl_multi_close($multi_handle);
    }

    /**
     * @param array $request
     */
    private function addTimer(array &$request)
    {
        //adds timer object to request
        $request['timer'] = microtime(true);
        $request['time'] = false; //default if not overridden by time later
    }

    /**
     * Build individual cURL options for a request
     * @param array $request
     * @return array
     */
    private function buildOptions(array $request)
    {
        $url = $request['url'];
        $post_data = $request['postData'];
        $individual_opts = $request['options'];
        $individual_headers = $request['headers'];
        $options = ($individual_opts) ? $individual_opts + $this->_options : $this->_options; //merge shared and individual request options
        $headers = ($individual_headers) ? $individual_headers + $this->_headers : $this->_headers; //merge shared and individual request headers
        //the below will overide the corresponding default or individual options
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_TIMEOUT] = $this->_timeout;

        if ($url) {
            $options[CURLOPT_URL] = $url;
        }

        if ($headers) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        // enable POST method and set POST parameters
        if ($post_data) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = is_array($post_data) ? http_build_query($post_data) : $post_data;
        }

        return $options;
    }

    /**
     * @param array $request
     */
    private function stopTimer(array &$request)
    {
        $start_time = $request['timer'];
        $end_time = microtime(true);
        $elapsed_time = rtrim(sprintf('%.20F', ($end_time - $start_time)), '0') . 'secs'; //convert float to string
        $request['time'] = $elapsed_time * 1000; //
        unset($request['timer']);
    }

    /**
     * Reset request queue
     */
    public function reset()
    {
        $this->requests = [];
    }
}
