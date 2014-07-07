<?php

/**
 * This file is part of the ProxmoxVE PHP API wrapper library (unofficial).
 *
 * @copyright 2014 César Muñoz <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

namespace ProxmoxVE;

/**
 * ProxmoxVE class. In order to interact with the proxmox server, the desired
 * app's code needs to create and use an object of this class.
 *
 * @author César Muñoz <zzantares@gmail.com>
 */
class Proxmox extends ProxmoxVE
{
    /**
     * The object that contains proxmox server authentication data.
     *
     * @var \ProxmoxVE\Credentials
     */
    private $credentials;


    /**
     * Holds the value of the base API URL, by default response is in JSON.
     * Sample value: https://my-proxmox:8006/api2/json
     *
     * @var string
     */
    private $apiUrl;


    /**
     * Holds the response type used to requests the API, possible values are
     * json, extjs, html, text, png.
     *
     * @var string
     */
    private $responseType;


    /**
     * Holds the fake response type, it is useful when you want to get the JSON
     * raw string instead of a PHP array.
     *
     * @var string
     */
    private $fakeType;


    /**
     * Constructor.
     *
     * @param mixed $credentials Credentials object or associative array holding
     *                           the login data.
     *
     * @throws \InvalidArgumentException If bad args supplied.
     */
    public function __construct($credentials, $responseType = 'array')
    {
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


        $this->setResponseType($responseType);
        $this->apiUrl = $this->getApiUrl();

        $authToken = $this->credentials->login();

        if (!$authToken) {
            $error = 'Can\'t login to Proxmox Server! Check your credentials.';
            throw new \RuntimeException($error);
        }

        parent::__construct($authToken);
    }


    /**
     * Sets the response type that is going to be returned when doing requests.
     *
     * @param string $responseType One of json, html, extjs, text, png.
     */
    public function setResponseType($responseType = 'array')
    {
        $supportedFormats = array('json', 'html', 'extjs', 'text', 'png');

        if (!in_array($responseType, $supportedFormats)) {
            if ($responseType == 'pngb64') {
                $this->fakeType = 'pngb64';
                $this->responseType = 'png';
                return;
            }

            $this->responseType = 'json';

            if ($responseType == 'object') {
                $this->fakeType = $responseType;
            } else {
                $this->fakeType = 'array';  // Default format
            }

            return;
        }

        $this->fakeType = false;
        $this->responseType = $responseType;
    }


    /**
     * Returns the response type that is being used by the Proxmox API client.
     *
     * @return string Response type being used.
     */
    public function getResponseType()
    {
        return $this->fakeType ?: $this->responseType;
    }


    /**
     * Returns the Credentials object associated with this proxmox API instance.
     * 
     * @return \ProxmoxVE\Credentials Object containing all proxmox data used to
     *                                connect to the server.
     */
    public function getCredentials()
    {
        return $this->credentials;
    }


    /**
     * Assign the passed Credentials object to the ProxmoxVE.
     *
     * @param ProxmoxVE\Credentials $credentials to assign.
     */
    public function setCredentials(Credentials $credentials)
    {
        $this->credentials = $credentials;
        $token = $credentials->login();

        if (!$token) {
            $error = 'Can\'t login to Proxmox Server! Check your credentials.';
            throw new \RuntimeException($error);
        }

        $this->setAuthToken($token);  // Should we use parent:: ?
    }


    /**
     * GET a resource defined in the pvesh tool.
     *
     * @param string $actionPath The resource tree path you want to ask for, see
     *                           more at http://pve.proxmox.com/pve2-api-doc/
     * @param array $params      An associative array filled with params.
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

        return $this->processResponse(parent::get($url, $params));
    }


    /**
     * SET a resource defined in the pvesh tool.
     *
     * @param string $actionPath The resource tree path you want to ask for, see
     *                           more at http://pve.proxmox.com/pve2-api-doc/
     * @param array $params      An associative array filled with params.
     *
     * @return array             A PHP array json_decode($response, true).
     *
     * @throws \InvalidArgumentException
     */
    public function set($actionPath, $params = array())
    {
        if (!is_array($params)) {
            $errorMessage = 'PUT params should be an associative array.';
            throw new \InvalidArgumentException($errorMessage);
        }

        // Check if we have a prefixed '/' on the path, if not add one.
        if (substr($actionPath, 0, 1) != '/')
            $actionPath = '/' . $actionPath;

        $url = $this->apiUrl . $actionPath;

        return $this->processResponse(parent::put($url, $params));
    }


    /**
     * CREATE a resource as defined by the pvesh tool.
     *
     * @param string $actionPath The resource tree path you want to ask for, see
     *                           more at http://pve.proxmox.com/pve2-api-doc/
     * @param array $params      An associative array filled with POST params
     *
     * @return array             A PHP array json_decode($response, true).
     *
     * @throws \InvalidArgumentException
     */
    public function create($actionPath, $params = array())
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

        return $this->processResponse(parent::post($url, $params));
    }


    /**
     * DELETE a resource defined in the pvesh tool.
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

        return $this->processResponse(parent::delete($url, $params));
    }


    /**
     * Returns the proxmox API URL where requests are sended.
     *
     * @return string Proxmox API URL.
     */
    public function getApiUrl()
    {
        return 'https://' . $this->credentials->getHostname() . ':'
            . $this->credentials->getPort() . '/api2/' . $this->responseType;
    }


    /**
     * Parses the response to the desired return type.
     *
     * @param string $response Response sended by the Proxmox server.
     *
     * @return mixed The parsed response.
     */
    public function processResponse($response)
    {
        if ($this->fakeType) {
            if ($this->fakeType == 'pngb64') {
                $base64 = base64_encode($response);
                return 'data:image/png;base64,' . $base64;
            }

            // For now 'object' is not supported, so we return array by default.
            return json_decode($response, true);
            // Later on need to add a check to see if is 'array' or 'object'
        }

        // Other types of response doesn't need any treatment
        return $response;
    }

}

