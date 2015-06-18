<?php

/**
 * This file is part of the ProxmoxVE PHP API wrapper library (unofficial).
 *
 * @copyright 2014 César Muñoz <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

namespace ProxmoxVE\Exception;

/**
 * BadResponseException class. Is the exception thrown when the proxmox
 * response has an error code of 400 or more which means the request 
 * went wrong
 *
 * @author Benjamin HUBERT <benjamin@alpixel.fr>
 */
class BadResponseException extends \RuntimeException
{
}
