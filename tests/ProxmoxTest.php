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
    protected function getMockProxmox($credentials, $method, $return = null)
    {
        $proxmox = $this->getMockBuilder('ProxmoxVE\Proxmox')
                        ->setMethods(array($method))
                        ->disableOriginalConstructor()
                        // Can't mock the login method when object isn't created
                        //->setConstructorArgs(array($credentials))
                        ->getMock();

        $proxmox->expects($this->any())
                ->method($method)
                ->will($this->returnValue($return));

        /**
         * We are tricking us! This is not good X_x!
         */
        $proxmox->setCredentials($credentials);

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


    public function testCanGetAndSetProxmoxCredentials()
    {
        $credentialsA = [
            'hostname' => 'my.proxmox.tld',
            'username' => 'root',
            'password' => 'English motherfucker, do you speak it?',
        ];

        $fakeToken = new AuthToken('csrf', 'ticket', 'user');
        $proxmox = $this->getMockProxmox($credentialsA, 'login', $fakeToken);

        // Add default values for credentials
        $credentialsA['realm'] = 'pam';
        $credentialsA['port'] = '8006';
        $this->assertEquals($proxmox->getCredentials(), $credentialsA);

        $credentialsB = [
            'hostname' => 'your.proxmox.tld',
            'username' => 'peluca',
            'password' => 'Another cool phrase here',
        ];

        $proxmox->setCredentials($credentialsB);

        // Add default values for credentials
        $credentialsB['realm'] = 'pam';
        $credentialsB['port'] = '8006';
        $this->assertEquals($proxmox->getCredentials(), $credentialsB);
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenSettingBadCredentials()
    {
        $credentials = [
            'hostname' => 'some.proxmox.tld',
            'username' => 'Daedaleus',
            'password' => 'Point',
        ];

        $fakeToken = new AuthToken('csrf', 'ticket', 'user');
        $proxmox = $this->getMockProxmox($credentials, 'login', $fakeToken);

        $proxmox->setCredentials('Bad credentials passed!');
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenSettingBadCredentialsArray()
    {
        $credentials = [
            'hostname' => 'some.proxmox.tld',
            'username' => 'root',
            'password' => 'toor',
        ];

        $fakeToken = new AuthToken('csrf', 'ticket', 'user');
        $proxmox = $this->getMockProxmox($credentials, 'login', $fakeToken);

        $proxmox->setCredentials(['un', 'dos', 'tres', 'por todos mis amigos']);
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenSettingIncompleteCredentialsArray()
    {
        $credentials = [
            'hostname' => 'a.proxmox.tld',
            'username' => 'user',
            'password' => 'secret',
        ];

        $fakeToken = new AuthToken('csrf', 'ticket', 'user');
        $proxmox = $this->getMockProxmox($credentials, 'login', $fakeToken);

        $proxmox->setCredentials([
            'username' => 'Sample username',
            'password' => 'Sample password',
        ]);
    }


    /**
     * @expectedException ProxmoxVE\Exception\MalformedCredentialsException
     */
    public function testExceptionIsThrownWhenPassingBadCredentialsObject()
    {
        $credentials = [
            'hostname' => 'a.proxmox.tld',
            'username' => 'user',
            'password' => 'secret',
        ];

        $fakeToken = new AuthToken('csrf', 'ticket', 'user');
        $proxmox = $this->getMockProxmox($credentials, 'login', $fakeToken);

        $credentials = new CustomClasses\Person('Rubius Hadridge', 40);
        $proxmox->setCredentials($credentials);
    }


    public function testExceptionIsNotThrownWhenUsingMagicCredentialsObject()
    {
        $fakeToken = new AuthToken('csrf', 'ticket', 'user');

        $credentials = new CustomClasses\MagicCredentials();
        $proxmox = $this->getMockProxmox($credentials, 'login', $fakeToken);
    }


    public function testGetCredentialsWithAllValues()
    {
        $credentials = [
            'hostname' => 'some.proxmox.tld',
            'username' => 'root',
            'password' => 'I was here',
        ];

        $fakeToken = new AuthToken('csrf', 'ticket', 'user');

        $proxmox = $this->getMockProxmox($credentials, 'login', $fakeToken);
        $credentials = $proxmox->getCredentials();

        $this->assertNotNull($credentials['hostname']);
        $this->assertNotNull($credentials['username']);
        $this->assertNotNull($credentials['password']);
        $this->assertNotNull($credentials['realm']);
        $this->assertNotNull($credentials['port']);
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
    //    $proxmox = $kj
    //}

}

