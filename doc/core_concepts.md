Core concepts
=============

You must now that any ProxmoxVE server has a web service that can be used to manage all Proxmox resources, it uses a REST like API. The base URI to the API is like: `https://your.server:8006/api2/json/`, of course if you have Proxmox listening on another port you would replace it in the URI. Read more at the [Proxmox wiki](http://pve.proxmox.com/wiki/Proxmox_VE_API).


ProxmoxVE API Client library
----------------------------

This PHP5 library will handle all HTTP requests that the Proxmox web service needs in order to create, fetch, modify and delete all Proxmox resources.


Installation
------------

Recomended installation is using [Composer](https://getcomposer.org/), if you do not have [Composer](https://getcomposer.org/) what are you waiting?

In the root of your project execute the following:

```sh
$ composer require zzantares/proxmoxve ~1.0
```

Or add this to your `composer.json` file:

```json
{
    "require": {
        "zzantares/proxmoxve": "~1.0"
    }
}
```

Then perform the installation:
```sh
$ composer install --no-dev
```


Available functions
-------------------

On your proxmox client object you can use `get()`, `create()`, `set()` and `delete()` functions for all resources specified at [PVE2 API Documentation](http://pve.proxmox.com/pve2-api-doc/
), params are passed as the second parameter in an associative array.

- [Read more about create() function](https://github.com/ZzAntares/ProxmoxVE/blob/master/doc/create.md).
- [Read more about get() function](https://github.com/ZzAntares/ProxmoxVE/blob/master/doc/get.md).
- [Read more about set() function](https://github.com/ZzAntares/ProxmoxVE/blob/master/doc/set.md).
- [Read more about delete() function](https://github.com/ZzAntares/ProxmoxVE/blob/master/doc/delete.md).


FAQ
---

**What resources or paths can I interact with and how?**

In your proxmox server you can use the [pvesh CLI Tool](http://pve.proxmox.com/wiki/Proxmox_VE_API#Using_.27pvesh.27_to_access_the_API) to manage all the pve resources, you can use this library in the exact same way you would use the pvesh tool. For instance you could run `pvesh` then, as the screen message should say, you can type `help [path] [--verbose]` to see how you could use a path and what params you should pass to it. Be sure to [read about the pvesh CLI Tool](http://pve.proxmox.com/wiki/Proxmox_VE_API#Using_.27pvesh.27_to_access_the_API) at the [Proxmox wiki](http://pve.proxmox.com/wiki).

**How does the Proxmox API works?**

Consult the [ProxmoxVE API](http://pve.proxmox.com/wiki/Proxmox_VE_API) article at the [Proxmox wiki](http://pve.proxmox.com/wiki).

**I need more docs!**

See the [doc](https://github.com/ZzAntares/ProxmoxVE/tree/master/doc) directory for more detailed documentation. Or use the [Proxmox forums support](http://forum.proxmox.com/).

