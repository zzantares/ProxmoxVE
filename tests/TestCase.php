<?php
/**
 * This file is part of the ProxmoxVE PHP API wrapper library (unofficial).
 *
 * @copyright 2014 César Muñoz <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

namespace ProxmoxVE;

/**
 * @author César Muñoz <zzantares@gmail.com>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function getMockProxmox($method = null, $return = null)
    {
        if ($method) {
            $proxmox = $this->getMockBuilder('ProxmoxVE\Proxmox')
                            ->setMethods(array($method))
                            ->disableOriginalConstructor()
                            ->getMock();

            $proxmox->expects($this->any())
                    ->method($method)
                    ->will($this->returnValue($return));

        } else {
            $proxmox = $this->getMockBuilder('ProxmoxVE\Proxmox')
                            ->disableOriginalConstructor()
                            ->getMock();
        }

        return $proxmox;
    }


    protected function getProxmox($response)
    {
        $httpClient = $this->getMockHttpClient(true, $response);

        $credentials = [
            'hostname' => 'my.proxmox.tld',
            'username' => 'root',
            'password' => 'toor',
        ];

        return new Proxmox($credentials, null, $httpClient);
    }


    protected function getMockHttpClient($successfulLogin, $response = null)
    {
        if ($successfulLogin) {
            $data = '{"data":{"CSRFPreventionToken":"csrf","ticket":"ticket","username":"random"}}';
            $login = new \GuzzleHttp\Psr7\Response(202, ['Content-Length' => 0], $data);
        } else {
            $login = new \GuzzleHttp\Psr7\Response(400, ['Content-Length' => 0]);
        }

        $mock = new \GuzzleHttp\Handler\MockHandler([
            $login,
            new \GuzzleHttp\Psr7\Response(202, ['Content-Length' => 0], $response),
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handler]);

        return $httpClient;
    }
}
