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
            $login = "HTTP/1.1 202 OK\r\nContent-Length: 0\r\n\r\n{$data}";
        } else {
            $login = "HTTP/1.1 400\r\nContent-Length: 0\r\n\r\n";
        }

        $mock = new \GuzzleHttp\Subscriber\Mock([
            $login,
            "HTTP/1.1 202 OK\r\nContent-Length: 0\r\n\r\n{$response}",
        ]);

        $httpClient = new \GuzzleHttp\Client();
        $httpClient->getEmitter()->attach($mock);


        return $httpClient;
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
        $credentials = [
            'hostname' => 'proxmox.server.tld',
            'username' => 'are not',
            'password' => 'valid folks!',
        ];

        $httpClient = $this->getMockHttpClient(false); // Simulate failed login

        $proxmox = new Proxmox($credentials, null, $httpClient);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetResourceWithBadParamsThrowsException()
    {
        $proxmox = $this->getProxmox(null);
        $proxmox->get('/someResource', 'wrong params here');
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateResourceWithBadParamsThrowsException()
    {
        $proxmox = $this->getProxmox(null);
        $proxmox->create('/someResource', 'wrong params here');
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetResourceWithBadParamsThrowsException()
    {
        $proxmox = $this->getProxmox(null);
        $proxmox->set('/someResource', 'wrong params here');
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testDeleteResourceWithBadParamsThrowsException()
    {
        $proxmox = $this->getProxmox(null);
        $proxmox->delete('/someResource', 'wrong params here');
    }


    public function testGetResource()
    {
        $fakeResponse = <<<'EOD'
{"data":[{"disk":940244992,"cpu":0.000998615325210486,"maxdisk":5284429824,"maxmem":1038385152,"node":"office","maxcpu":1,"level":"","uptime":3296027,"id":"node/office","type":"node","mem":311635968}]}
EOD;
        $proxmox = $this->getProxmox($fakeResponse);

        $this->assertEquals($proxmox->get('/nodes'), json_decode($fakeResponse, true));
    }

}

