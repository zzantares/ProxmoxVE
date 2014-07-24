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


    public function testGetAccess()
    {
        $fakeResponse = <<<'EOD'
{"data":[{"subdir":"users"},{"subdir":"groups"},{"subdir":"roles"},{"subdir":"acl"},{"subdir":"domains"},{"subdir":"ticket"},{"subdir":"password"}]}
EOD;

        $fakeAccess = json_decode($fakeResponse, true);
        $proxmox = $this->getMockProxmox('get', $fakeAccess);
        $this->assertEquals($proxmox->getAccess(), $fakeAccess);
    }


    public function testGetCluster()
    {
        $fakeResponse = <<<'EOD'
{"data":[{"name":"log"},{"name":"options"},{"name":"resources"},{"name":"tasks"},{"name":"backup"},{"name":"ha"},{"name":"status"},{"name":"nextid"}]}
EOD;

        $fakeCluster = json_decode($fakeResponse, true);
        $proxmox = $this->getMockProxmox('get', $fakeCluster);
        $this->assertEquals($proxmox->getCluster(), $fakeCluster);
    }


    public function testGetNodes()
    {
        $fakeResponse = <<<'EOD'
{"data":[{"disk":944705536,"cpu":0.00299545408156743,"maxdisk":5284429824,"maxmem":1038385152,"node":"masterpve","maxcpu":1,"level":"","uptime":1834785,"id":"node/masterpve","type":"node","mem":310874112},{"disk":944705536,"cpu":0.00299545408156743,"maxdisk":5284429824,"maxmem":1038385152,"node":"slavepve","maxcpu":1,"level":"","uptime":1834785,"id":"node/slavepve","type":"node","mem":310874112}]}
EOD;

        $fakeNodes = json_decode($fakeResponse, true);
        $proxmox = $this->getMockProxmox('get', $fakeNodes);
        $this->assertEquals($proxmox->getNodes(), $fakeNodes);
    }


    public function testGetNode()
    {
        $fakeResponse = <<<'EOD'
{"data":[{"name":"ceph"},{"name":"apt"},{"name":"version"},{"name":"syslog"},{"name":"bootlog"},{"name":"status"},{"name":"subscription"},{"name":"tasks"},{"name":"rrd"},{"name":"rrddata"},{"name":"vncshell"},{"name":"spiceshell"},{"name":"time"},{"name":"dns"},{"name":"services"},{"name":"scan"},{"name":"storage"},{"name":"qemu"},{"name":"openvz"},{"name":"vzdump"},{"name":"ubcfailcnt"},{"name":"network"},{"name":"aplinfo"},{"name":"startall"},{"name":"stopall"},{"name":"netstat"}]}
EOD;

        $fakeNode = json_decode($fakeResponse, true);
        $proxmox = $this->getMockProxmox('get', $fakeNode);
        $this->assertEquals($proxmox->getNode('centos'), $fakeNode);
    }


    public function testGetPools()
    {
        $fakeResponse = <<<'EOD'
{"data":[{"comment":"Simple pool","poolid":"Marketing"},{"comment":"Pool for sales people","poolid":"Sales"}]}
EOD;

        $fakePools = json_decode($fakeResponse, true);
        $proxmox = $this->getMockProxmox('get', $fakePools);
        $this->assertEquals($proxmox->getPools(), $fakePools);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateNewPoolThrowsExceptionIfArrayIsNotPassed()
    {
        $credentials = $this->getMockCredentials(array('host', 'user', 'pass'));
        $proxmox = new Proxmox($credentials);
        $proxmox->createPool('not an array');
    }


    public function testGetPool()
    {
        $fakeResponse = <<<'EOD'
{"data":{"members":[],"comment":"Pool for sales people"}}
EOD;

        $fakePool = json_decode($fakeResponse, true);
        $proxmox = $this->getMockProxmox('get', $fakePool);
        $this->assertEquals($proxmox->getPool('Sales'), $fakePool);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetPoolDataThrowsExceptionIfArrayIsNotPassed()
    {
        $credentials = $this->getMockCredentials(array('host', 'user', 'pass'));
        $proxmox = new Proxmox($credentials);
        $proxmox->setPool('this is', 'not an array');
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

