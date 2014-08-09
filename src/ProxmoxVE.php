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
class ProxmoxVE
{
    /**
     * @const TIMEOUT Time in seconds before droping Proxmox connection.
     */
    const TIMEOUT = 30;


    /**
     * @const USER_AGENT The User-Agent HTTP Header value.
     */
    const USER_AGENT = 'Proxmox VE API';


    /**
     * Sets the current AuthToken to the one is passed.
     *
     * @param \ProxmoxVE\AuthToken $authToken New AuthToken object to use.
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;
    }


    /**
     * Gets the AuthToken that is used to make requests.
     *
     * @return \ProxmoxVE\AuthToken Object containing the ticket and csrf wich
     *                              are used in every request.
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }


}
