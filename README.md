ProxmoxVE API Client
====================

This **PHP 5.4+** library allows you to interact with your Proxmox server via API.

[![Build Status](https://travis-ci.org/lumaserv/ProxmoxVE.svg?branch=master)](https://travis-ci.org/lumaserv/ProxmoxVE)
[![Latest Stable Version](https://poser.pugx.org/lumaserv/proxmoxve/v/stable.svg)](https://packagist.org/packages/lumaserv/proxmoxve)
[![Total Downloads](https://poser.pugx.org/lumaserv/proxmoxve/downloads.svg)](https://packagist.org/packages/lumaserv/proxmoxve)
[![Latest Unstable Version](https://poser.pugx.org/lumaserv/proxmoxve/v/unstable.svg)](https://packagist.org/packages/lumaserv/proxmoxve)
[![License](https://poser.pugx.org/lumaserv/proxmoxve/license.svg)](https://packagist.org/packages/lumaserv/proxmoxve)

> If you find any errors, typos or you detect that something is not working as expected please open an [issue](https://github.com/lumaserv/ProxmoxVE/issues/new) or tweetme [@lumaserv](https://twitter.com/lumaserv). I'll try to release a fix asap.

**Looking for a PHP 5.3 library version?** Search through the [releases](https://github.com/lumaserv/ProxmoxVE/releases) one that fits your needs, I recommend using the [2.1.1](https://github.com/lumaserv/ProxmoxVE/releases/tag/v2.1.1) version.

Installation
------------

Recomended installation is using [Composer], if you do not have [Composer] what are you waiting?

In the root of your project execute the following:

```sh
$ composer require lumaserv/proxmoxve ~4.0.4
```

Or add this to your `composer.json` file:

```json
{
    "require": {
        "lumaserv/proxmoxve": "~4.0.4"
    }
}
```

Then perform the installation:
```sh
$ composer install --no-dev
```

Usage
-----

```php
<?php

// Require the autoloader
require_once 'vendor/autoload.php';

// Use the library namespace
use ProxmoxVE\Proxmox;

// Create your credentials array
$credentials = [
    'hostname' => 'proxmox.server.com',  // Also can be an IP
    'username' => 'root',
    'password' => 'secret',
];

// realm and port defaults to 'pam' and '8006' but you can specify them like so
$credentials = [
    'hostname' => 'proxmox.server.com',
    'username' => 'root',
    'password' => 'secret',
    'realm' => 'pve',
    'port' => '9009',
];

// Then simply pass your credentials when creating the API client object.
$proxmox = new Proxmox($credentials);

$allNodes = $proxmox->get('/nodes');

print_r($allNodes);
```


Sample output:

```php
Array
(
    [data] => Array
        (
            [0] => Array
                (
                    [disk] => 2539465464
                    [cpu] => 0.031314446882002
                    [maxdisk] => 30805066770
                    [maxmem] => 175168446464
                    [node] => mynode1
                    [maxcpu] => 24
                    [level] => 
                    [uptime] => 139376
                    [id] => node/mynode1
                    [type] => node
                    [mem] => 20601992182
                )

        )

)
```

Using custom credentials object
-------------------------------

Also is possible to create a ProxmoxVE instance passing a custom object that has all related data needed to connect to the Proxmox server:

```php
<?php
// Once again require the autoloader
require_once 'vendor/autoload.php';

// Sample custom credentials class
class CustomCredentials
{
    public function __construct($host, $user, $pass)
    {
        $this->hostname = $host;
        $this->username = $user;
        $this->password = $pass;
    }
}

// Create ProxmoxVE instance by passing your custom credentials object
$credentials = new CustomCredentials('proxmox.server.com', 'root', 'secret');
$proxmox = new ProxmoxVE\Proxmox($credentials);

// Then you can use it, for example create a new user.

// Define params
$params = [
    'userid' => 'new_user@pve',  // Proxmox requires to specify the realm (see the docs)
    'comment' => 'Creating a new user',
    'password' => 'canyoukeepasecret?',
];

// Send request passing params
$result = $proxmox->create('/access/users', $params);

// If an error occurred the 'errors' key will exist in the response array
if (isset($result['errors'])) {
    error_log('Unable to create new proxmox user.');
    foreach ($result['errors'] as $title => $description) {
        error_log($title . ': ' . $description);
    }
} else {
    echo 'Successful user creation!';
}
```

Using a custom credentials object is useful when your application uses some *ORM models* with the connecting data inside them, so you can pass for example an *Eloquent* model that holds the credentials inside.

License
-------

This project is released under the MIT License. See the bundled [LICENSE] file for details.

[LICENSE]:./LICENSE
[PVE2 API Documentation]:http://pve.proxmox.com/pve-docs/api-viewer/index.html
[ProxmoxVE API]:http://pve.proxmox.com/wiki/Proxmox_VE_API
[Proxmox wiki]:http://pve.proxmox.com/wiki
[Composer]:https://getcomposer.org/
