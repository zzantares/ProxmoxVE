<?php

/**
 * This file is part of the ProxmoxVE PHP API wrapper library (unofficial).
 *
 * @copyright 2014 César Muñoz <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

namespace ZzAntares\ProxmoxVE;

/**
 * ProxmoxVE class. In order to interact with the proxmox server, the desired
 * app's code needs to create and use an object of this class.
 *
 * @author César Muñoz <zzantares@gmail.com>
 */
class ProxmoxVE
{
    /**
     * @todo May be we need to throw InvalidArgumentException for all functions
     * receiving invalid params.
     */


    /**
     * @const TIMEOUT Time in seconds before droping Proxmox connection.
     */
    const TIMEOUT = 30;


    /**
     * @const USER_AGENT The User-Agent HTTP Header value.
     */
    const USER_AGENT = 'Proxmox VE API';


    /**
     * Makes an standard HTTP request to the specified URL with the HTTP method,
     * data, HTTP header and cookies specified.
     *
     * @param string $url     The requested URL.
     * @param string $method  The HTTP requesting method, GET, POST, PUT, DELETE
     *                        are supported.
     * @param string $params  The POST/PUT data to send in URL encoded format.
     *                        If going to send request via GET method, params
     *                        should be already encoded in the URL.
     * @param array $headers  The indexed array filled with the HTTP headers to
     *                        set in the request.
     * @param string $cookies The cookies to send in the HTTP request, multiple
     *                        cookies are separated with '; ' (note the space
     *                        after the semicolon).
     *
     * @throws RuntimeException If request can't be sent or no response was
     *                          received.
     *
     * @return string The response that server send back.
     */
    public static function request(
        $url,
        $method = 'GET',
        $params = array(),
        $headers = array(),
        $cookies = null
    ) {
        $curlSession = curl_init();

        switch($method) {
            case 'POST':
                curl_setopt($curlSession, CURLOPT_POST, true);
                curl_setopt($curlSession, CURLOPT_POSTFIELDS, $params);
                curl_setopt($curlSession, CURLOPT_HTTPHEADER, $headers);
                break;

            case 'PUT':
                curl_setopt($curlSession, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curlSession, CURLOPT_POSTFIELDS, $params);
                curl_setopt($curlSession, CURLOPT_HTTPHEADER, $headers);
                break;

            case 'DELETE':
                curl_setopt($curlSession, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curlSession, CURLOPT_POSTFIELDS, $params);
                curl_setopt($curlSession, CURLOPT_HTTPHEADER, $headers);
                break;

            case 'GET':
            default:
        }

        if ($cookies) curl_setopt($curlSession, CURLOPT_COOKIE, $cookies);

        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curlSession, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);

        $response = curl_exec($curlSession);
        $statusCode = curl_getinfo($curlSession, CURLINFO_HTTP_CODE);
        curl_close($curlSession);

        if ($statusCode >= 300) {
            $error = "Error sending request to {$url}.\nResponse code was: ";
            throw new \RuntimeException($error . $statusCode);
        }

        // Never parse response, that depends on the API response type.
        return $response;
    }


    /**
     * The object that contains proxmox server authentication data.
     *
     * @var \ZzAntares\ProxmoxVE\Credentials
     */
    protected $credentials;


    /**
     * Holds the value of the base API URL, by default response is in JSON.
     * Sample value: https://my-proxmox:8006/api2/json
     *
     * @var string
     */
    protected $apiUrl;


    /**
     * Token object holding Proxmox ticket session and CSRF prevention token.
     *
     * @var \ZzAntares\ProxmoxVE\AuthToken
     */
    protected $authToken;


    /**
     * @todo What if the used program needs the raw json string? then why 
     *       we would parse response? need to add something to handle that.
     * @todo Add param to pass a HttpAdapter object to use in the application,
     *       if no adapter object is defined then we will use cURL.
     * @todo Add param to pass a logger psr-3 compliant? 
     */


    /**
     * Constructor.
     *
     * @param mixed $credentials Credentials object or associative array holding
     *                           the login data.
     *
     * @throws \RuntimeException
     */
    public function __construct($credentials)
    {
        // Check if CURL is enabled
        if (!function_exists('curl_version')) {
            throw new RuntimeException('PHP5-CURL needs to be enabled!');
        }

        if ($credentials instanceof Credentials) {
            $this->credentials = $credentials;

        } elseif (is_array($credentials)) {
            $keys = array('hostname', 'username', 'password', 'realm', 'port');

            // Check if array has all needed data.
            if (count(array_diff($keys, array_keys($credentials))) != 0) {
                $errorMessage = 'PVE credentials needs ' . implode(', ', $keys);
                throw new \InvalidArgumentException($errorMessage);
            }

            $this->credentials = new Credentials(
                $credentials['hostname'],
                $credentials['username'],
                $credentials['password'],
                $credentials['realm'],
                $credentials['port']
            );

        } else {
            $errorMessage = 'PVE API needs a Credentials object or an array.';
            throw new \InvalidArgumentException($errorMessage);
        }

        $this->apiUrl = $this->credentials->getApiUrl();

        if (!$this->authToken = $this->credentials->login()) {
            $error = 'Can\'t login to Proxmox Server! Check your credentials.';
            throw new \RuntimeException($error);
        }
    }


    /**
     * Returns the AuthToken object associated with this proxmox API wrapper.
     * 
     * @return AuthToken The token containing all proxmox data used to connect
     *                   to the server.
     */
    public function getCredentials()
    {
        return $this->credentials;
    }


    /**
     * Assign the passed Credentials object to the ProxmoxVE.
     *
     * @param ZzAntares\ProxmoxVE\Credentials $credentials to assign.
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }


    /**
     * Performs a GET request to the Proxmox server.
     *
     * @param string $actionPath The resource tree path you want to ask for, see
     *                           more at http://pve.proxmox.com/pve2-api-doc/
     * @param array $params      An associative array filled with GET params.
     * 
     * @return array             A PHP array json_decode($response, true).
     *
     * @throws \InvalidArgumentException
     */
    public function get($actionPath, $params = array())
    {
        if (!is_array($params)) {
            $errorMessage = 'GET params should be an associative array.';
            throw new \InvalidArgumentException($errorMessage);
        }

        // Check if we have a prefixed '/' on the path, if not add one.
        if (substr($actionPath, 0, 1) != '/')
            $actionPath = '/' . $actionPath;

        $url = $this->apiUrl . $actionPath;

        if ($params) $url .= '?' . http_build_query($params);

        $cookies = 'PVEAuthCookie=' . $this->authToken->getTicket();

        $response = self::request($url, 'GET', null, null, $cookies);

        return json_decode($response, true);
    }


    /**
     * Performs a POST request to the Proxmox server, this function cant be used
     * to login into the server, for that need to call Credentials->login().
     *
     * @param string $actionPath The resource tree path you want to ask for, see
     *                           more at http://pve.proxmox.com/pve2-api-doc/
     * @param array $params      An associative array filled with POST params to
     *                           send in the request.
     *
     * @return array             A PHP array json_decode($response, true).
     *
     * @throws \InvalidArgumentException
     */
    public function post($actionPath, $params = array())
    {
        if (!is_array($params)) {
            $errorMessage = 'POST params should be an asociative array.';
            throw new \InvalidArgumentException($errorMessage);
        }

        // Check if we have a prefixed '/' on the path, if not add one.
        if (substr($actionPath, 0, 1) != '/') {
            $actionPath = '/' . $actionPath;
        }

        $url = $this->apiUrl . $actionPath;
        $params = http_build_query($params);
        $cookies = 'PVEAuthCookie=' . $this->authToken->getTicket();
        $headers = array('CSRFPreventionToken: ' . $this->authToken->getCsrf());

        $response = self::request($url, 'POST', $params, $headers, $cookies);

        return json_decode($response, true);
    }


    /**
     * Performs a PUT request to the Proxmox server.
     *
     * @param string $actionPath The resource tree path you want to ask for, see
     *                           more at http://pve.proxmox.com/pve2-api-doc/
     * @param array $params      An associative array filled with params.
     *
     * @return array             A PHP array json_decode($response, true).
     *
     * @throws \InvalidArgumentException
     */
    public function put($actionPath, $params = array())
    {
        if (!is_array($params)) {
            $errorMessage = 'PUT params should be an associative array.';
            throw new \InvalidArgumentException($errorMessage);
        }

        // Check if we have a prefixed '/' on the path, if not add one.
        if (substr($actionPath, 0, 1) != '/')
            $actionPath = '/' . $actionPath;

        $url = $this->apiUrl . $actionPath;
        $params = http_build_query($params);
        $cookies = 'PVEAuthCookie=' . $this->authToken->getTicket();
        $headers = array('CSRFPreventionToken: ' . $this->authToken->getCsrf());

        $response = self::request($url, 'PUT', $params, $headers, $cookies);

        return json_decode($response, true);
    }


    /**
     * Performs a DELETE request to the Proxmox server.
     *
     * @param string $actionPath The resource tree path you want to ask for, see
     *                           more at http://pve.proxmox.com/pve2-api-doc/
     * @param array $params      An associative array filled with params.
     *
     * @return array             A PHP array json_decode($response, true).
     *
     * @throws \InvalidArgumentException
     */
    public function delete($actionPath, $params = array())
    {
        if (!is_array($params)) {
            $errorMessage = 'DELETE params should be an associative array.';
            throw new \InvalidArgumentException($errorMessage);
        }

        // Check if we have a prefixed '/' on the path, if not add one.
        if (substr($actionPath, 0, 1) != '/')
            $actionPath = '/' . $actionPath;

        $url = $this->apiUrl . $actionPath;
        $params = http_build_query($params);
        $cookies = 'PVEAuthCookie=' . $this->authToken->getTicket();
        $headers = array('CSRFPreventionToken: ' . $this->authToken->getCsrf());

        $response = self::request($url, 'DELETE', $params, $headers, $cookies);

        return json_decode($response, true);  // Some deletes return strings
    }

}
