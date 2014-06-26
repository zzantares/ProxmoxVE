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
        $this->credentials = new Credentials('myproxmox.tld', 'root', 'abc123');

        $this->proxmox = $this->getMockBuilder('ZzAntares\ProxmoxVE\ProxmoxVE')
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->proxmox->expects($this->any())
            ->method('getCredentials')
            ->will($this->returnValue($this->credentials));
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


    /*
     * Add test for get, post, put and delete functions. Need to create mocks. 
     */
}
