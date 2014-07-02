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
    public function getMockCredentials($constructArgs = array())
    {
        $mockCredentials = $this->getMockBuilder('ProxmoxVE\Credentials')
                                ->setMethods(array('login'))
                                ->setConstructorArgs($constructArgs)
                                ->getMock();

        $fakeToken = new AuthToken('csrf', 'ticket', 'owner');

        $mockCredentials->expects($this->any())
                        ->method('login')
                        ->will($this->returnValue($fakeToken));

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
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionWhenBadParamsArePassed()
    {
        $data = array('hostname', 'password', 'username', 'port', 'realm');
        $proxmoxApi = new Proxmox($data);
    }

}
