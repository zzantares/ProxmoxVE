<?php
/**
 * This file is part of the ProxmoxVE PHP API wrapper library (unofficial).
 *
 * @copyright 2014 César Muñoz <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

namespace ZzAntares\ProxmoxVE;

/**
 * @author César Muñoz <zzantares@gmail.com>
 */
class ProxmoxVETest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $fakeToken = new AuthToken('csrf', 'ticket', 'owner');

        $this->credentials = $this->getMockBuilder('ZzAntares\ProxmoxVE\Credentials')
                                  ->setMethods(array('login'))
                                  ->setConstructorArgs(array('myproxmox.tld', 'root', 'abc123'))
                                  ->getMock();

        $this->credentials->expects($this->any())
            ->method('login')
            ->will($this->returnValue($fakeToken));

        $this->proxmox = new ProxmoxVE($this->credentials);
    }


    public function testProxmoxObjectSavesTokenCorrectly()
    {
        $this->assertSame($this->credentials, $this->proxmox->getCredentials());
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionWhenBadParamsArePassed()
    {
        $data = array('hostname', 'password', 'username', 'port', 'realm');
        $proxmoxApi = new ProxmoxVE($data);
    }


    public function testChangesCredentialsCorrectly()
    {
        $newCredentials = new Credentials('host', 'user', 'pass');
        $this->proxmox->setCredentials($newCredentials);

        $this->assertEquals($newCredentials, $this->proxmox->getCredentials());
    }


    /*
     * Add test for get, post, put and delete functions. Need to create mocks. 
     */
}
