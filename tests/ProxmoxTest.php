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
    public function setUp()
    {
        $fakeToken = new AuthToken('csrf', 'ticket', 'owner');

        $this->credentials = $this->getMockBuilder('ProxmoxVE\Credentials')
                                  ->setMethods(array('login'))
                                  ->setConstructorArgs(array('myproxmox.tld', 'root', 'abc123'))
                                  ->getMock();

        $this->credentials->expects($this->any())
            ->method('login')
            ->will($this->returnValue($fakeToken));

        $this->proxmox = new Proxmox($this->credentials);
    }


    public function testGetCredentials()
    {
        $this->assertSame($this->credentials, $this->proxmox->getCredentials());
    }


    public function testSetCredentials()
    {
        $fakeToken = new AuthToken('csrf', 'ticket', 'owner');

        $newCredentials = $this->getMockBuilder('ProxmoxVE\Credentials')
                               ->setMethods(array('login'))
                               ->setConstructorArgs(array('host', 'user', 'pass'))
                               ->getMock();

        $newCredentials->expects($this->any())
                       ->method('login')
                       ->will($this->returnValue($fakeToken));

        $this->proxmox->setCredentials($newCredentials);

        $this->assertEquals($newCredentials, $this->proxmox->getCredentials());
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
