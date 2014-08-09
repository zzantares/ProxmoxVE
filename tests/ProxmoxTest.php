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


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownIfBadParamsPassed()
    {
        $proxmox = new Proxmox('bad param');
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenNonAssociativeArrayIsGivenAsCredentials()
    {
        $proxmox = new Proxmox([
            'root', 'So Bruce Wayne is alive? or did he die in the explosion?',
        ]);
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenIncompleteCredentialsArrayIsPassed()
    {
        $proxmox = new Proxmox([
            'username' => 'root',
            'password' => 'The NSA is watching us! D=',
        ]);
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenWrongCredentialsObjectIsPassed()
    {
        $credentials = new CustomClasses\Person('Harry Potter', 13);
        $proxmox = new Proxmox($credentials);
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenIncompleteCredentialsObjectIsPassed()
    {
        $credentials = new CustomClasses\IncompleteCredentials("user", "and that's it");
        $proxmox = new Proxmox($credentials);
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenProtectedCredentialsObjectIsPassed()
    {
        $credentials = new CustomClasses\ProtectedCredentials('host', 'user', 'pass');
        $proxmox = new Proxmox($credentials);
    }


    /**
     * @expectedException GuzzleHttp\Exception\RequestException
     */
    public function testProxmoxExceptionIsNotThrownWhenUsingMagicCredentialsObject()
    {
        $credentials = new CustomClasses\MagicCredentials();
        $proxmox = new Proxmox($credentials);
    }


    public function testGetCredentialsWithAllValues()
    {
        $ids = [
            'hostname' => 'some.proxmox.tld',
            'username' => 'root',
            'password' => 'I was here',
        ];

        $fakeAuthToken = new AuthToken('csrf', 'ticket', 'username');
        $proxmox = $this->getMockProxmox('login', $fakeAuthToken);
        $proxmox->setCredentials($ids);

        $credentials = $proxmox->getCredentials();

        $this->assertEquals($credentials->hostname, $ids['hostname']);
        $this->assertEquals($credentials->username, $ids['username']);
        $this->assertEquals($credentials->password, $ids['password']);
        $this->assertEquals($credentials->realm, 'pam');
        $this->assertEquals($credentials->port, '8006');
    }


    /**
     * @expectedException Exception
     */
    public function testUnresolvedHostnameThrowsException()
    {
        $credentials = [
            'hostname' => 'proxmox.example.tld',
            'username' => 'user',
            'password' => 'pass',
        ];

        $proxmox = new Proxmox($credentials);
    }


    /**
     * @expectedException ProxmoxVE\Exception\AuthenticationException
     */
    public function testLoginErrorThrowsException()
    {
        /**
         * This is real, need to mock guzzle to not send the http request.
         */

        $credentials = [
            'hostname' => 'centos.vpservers.com',
            'username' => 'are not',
            'password' => 'valid folks!',
        ];

        $proxmox = new Proxmox($credentials);
    }


    //public function testGetResourceInTheDesiredResponseFormat()
    //{
    //          var_dump($ex);
    //          full reference.full reference.
    //
    //
    //    $proxmox = $kj
    //}

}

