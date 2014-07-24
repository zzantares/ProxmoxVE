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


    public function getMockProxmox($method, $returns = null)
    {
        $credentials = $this->getMockCredentials(array('host', 'user', 'pass'));
        $proxmox = $this->getMockBuilder('ProxmoxVE\Proxmox')
                        //->setMethods(array('processResponse'))
                        ->setMethods(array($method))
                        ->setConstructorArgs(array($credentials))
                        ->getMock();

        $proxmox->expects($this->any())
                //->method('processResponse')
                ->method($method)
                ->will($this->returnValue($returns));

        return $proxmox;
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
    public function testPassingWrongCredentialsObjectThrowsException()
    {
        $proxmox = new Proxmox('bad params');
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


    public function testSettingResponseType()
    {
        $credentials = $this->getMockCredentials(array('host', 'user', 'passwd'));
        $proxmox = new Proxmox($credentials);
        $this->assertEquals($proxmox->getResponseType(), 'array');

        $proxmox->setResponseType('json');
        $this->assertEquals($proxmox->getResponseType(), 'json');

        $proxmox->setResponseType('non-existant');
        $this->assertEquals($proxmox->getResponseType(), 'array');

        $proxmox->setResponseType('png');
        $this->assertEquals($proxmox->getResponseType(), 'png');

        $proxmox->setResponseType('pngb64');
        $this->assertEquals($proxmox->getResponseType(), 'pngb64');
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGettingResourcesThrowsExceptionWhenWrongParamsGiven()
    {
        $proxmox = $this->getMockProxmox('processResponse');
        $proxmox->get('/nodes', 'bad param');
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingResourcesThrowsExceptionWhenWrongParamsGiven()
    {
        $proxmox = $this->getMockProxmox('processResponse');
        $proxmox->set('/access/users/bob@pve', 'bad param');
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreattingResourcesThrowsExceptionWhenWrongParamsGiven()
    {
        $proxmox = $this->getMockProxmox('processResponse');
        $proxmox->create('/access/users', 'bad param');
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDelettingResourcesThrowsExceptionWhenWrongParamsGiven()
    {
        $proxmox = $this->getMockProxmox('processResponse');
        $proxmox->delete('/access/users/user@realm', 'bad param');
    }


    public function testProcessResponse()
    {
        $credentials = $this->getMockCredentials(array('host', 'user', 'pass'));
        $proxmox = new Proxmox($credentials);

        $json = '{"data":{"vmid":"4242"}}';
        $this->assertEquals(json_decode($json, true), $proxmox->processResponse($json));

        $proxmox->setResponseType('json');
        $this->assertEquals($json, $proxmox->processResponse($json));

        $proxmox->setResponseType('non-existant');
        $this->assertEquals(json_decode($json, true), $proxmox->processResponse($json));

        $emptyPNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAADElEQVR4nGP4//8/AAX+Av4N70a4AAAAAElFTkSuQmCC';
        $proxmox->setResponseType('pngb64');
        $this->assertEquals('data:image/png;base64,' . $emptyPNG, $proxmox->processResponse(base64_decode($emptyPNG)));

        $proxmox->setResponseType('png');
        $this->assertEquals(base64_decode($emptyPNG), $proxmox->processResponse(base64_decode($emptyPNG)));
    }


    public function testValidCredentialsObject()
    {
        $credentials = $this->getMockCredentials(array('host', 'user', 'pass'));
        $proxmox = new Proxmox($credentials);

        $this->assertFalse($proxmox->validCredentialsObject('not an object'));

        $propertiesCredentials = new CustomCredentials\PropertiesCredentials('host', 'user', 'pass', 'realm', 'port');
        $this->assertTrue($proxmox->validCredentialsObject($propertiesCredentials));

        $methodsCredentials = new CustomCredentials\MethodsCredentials('host', 'user', 'pass', 'realm', 'port');
        $this->assertTrue($proxmox->validCredentialsObject($methodsCredentials));

        $propertiesOptCredentials = new CustomCredentials\PropertiesOptCredentials('host', 'user', 'pass');
        $this->assertTrue($proxmox->validCredentialsObject($propertiesOptCredentials));

        $methodsOptCredentials = new CustomCredentials\MethodsOptCredentials('host', 'user', 'pass');
        $this->assertTrue($proxmox->validCredentialsObject($methodsOptCredentials));

        $badCredentials = new CustomCredentials\BadCredentials('bad', 'user', 'passwd');
        $this->assertFalse($proxmox->validCredentialsObject($badCredentials));
    }


    public function testGetStorages()
    {
        $fakeResponse = <<<'EOD'
{"data":[{"priority":0,"content":"images,iso,vztmpl,rootdir","digest":"da39a3ee5e6b4b0d3255bfef95601890afd80709","maxfiles":0,"path":"/var/lib/vz","type":"dir","storage":"local"}]}
EOD;

        $fakeStorages = json_decode($fakeResponse, true);
        $proxmox = $this->getMockProxmox('get', $fakeStorages);
        $this->assertEquals($proxmox->getStorages(), $fakeStorages);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateNewStorageThrowsExceptionIfArrayIsNotPassed()
    {
        $credentials = $this->getMockCredentials(array('host', 'user', 'pass'));
        $proxmox = new Proxmox($credentials);

        $proxmox->createStorage('not an array');
    }


    public function testGetStorage()
    {
        $fakeResponse = <<<'EOD'
{"data":{"priority":0,"content":"images,iso,vztmpl,rootdir","digest":"da39a3ee5e6b4b0d3255bfef95601890afd80709","maxfiles":0,"path":"/var/lib/vz","type":"dir","storage":"local"}}
EOD;

        $fakeStorage = json_decode($fakeResponse, true);
        $proxmox = $this->getMockProxmox('get', $fakeStorage);
        $this->assertEquals($proxmox->getStorage('local'), $fakeStorage);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetStorageDataThrowsExceptionIfArrayIsNotPassed()
    {
        $credentials = $this->getMockCredentials(array('host', 'user', 'pass'));
        $proxmox = new Proxmox($credentials);

        $proxmox->setStorage('this is', 'not an array');
    }
}

