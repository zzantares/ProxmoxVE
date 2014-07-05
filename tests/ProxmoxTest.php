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
class ProxmoxTest extends \PHPUnit_Framework_TestCase
{
    public function getMockCredentials($constructArgs = array(), $fail = false)
    {
        $mockCredentials = $this->getMockBuilder('ProxmoxVE\Credentials')
                                ->setMethods(array('login'))
                                ->setConstructorArgs($constructArgs)
                                ->getMock();

        if ($fail) {
            $mockCredentials->expects($this->any())
                            ->method('login')
                            ->will($this->returnValue(false));
        } else {
            $fakeToken = new AuthToken('csrf', 'ticket', 'owner');
            $mockCredentials->expects($this->any())
                            ->method('login')
                            ->will($this->returnValue($fakeToken));
        }

        return $mockCredentials;
    }


    public function testGetAndSetCredentials()
    {
        $credentials = $this->getMockCredentials(array('my.proxmox.tld', 'root', '123abc'));
        $proxmox = new Proxmox($credentials);
        $this->assertSame($credentials, $proxmox->getCredentials());

        $newCredentials = $this->getMockCredentials(array('host', 'user', 'pass'));
        $proxmox->setCredentials($newCredentials);
        $this->assertEquals($newCredentials, $proxmox->getCredentials());
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testGivingWrongCredentialsMustThrowAnException()
    {
        $credentials = $this->getMockCredentials(array('put', 'three', 'values'), true);
        $proxmox = new Proxmox($credentials);
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testSettingWrongCredentialsMustThrowAnException()
    {
        $credentials = $this->getMockCredentials(array('using', 'demo', 'data'));
        $proxmox = new Proxmox($credentials);

        $newCredentials = $this->getMockCredentials(array('bad', 'user', 'pass'), true);
        $proxmox->setCredentials($newCredentials);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionWhenWrongParamsPassed()
    {
        $data = array('hostname', 'password', 'username', 'port', 'realm');
        $proxmoxApi = new Proxmox($data);
    }


    public function testGetApiUrlWithResponseType()
    {
        $host = 'host';
        $port = '8006';

        $credentials = $this->getMockCredentials(array($host, 'user', 'passwd'));
        $apiUrl = 'https://' . $host . ':' . $port . '/api2/';

        $proxmox = new Proxmox($credentials);
        $this->assertEquals($apiUrl . 'json', $proxmox->getApiUrl());

        $proxmox = new Proxmox($credentials, 'png');
        $this->assertEquals($apiUrl . 'png', $proxmox->getApiUrl());

        $proxmox = new Proxmox($credentials, 'non-existant');
        $this->assertEquals($apiUrl . 'json', $proxmox->getApiUrl());
    }
}

