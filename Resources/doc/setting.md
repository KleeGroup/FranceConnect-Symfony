Step 1: Setting up the bundle
=============================
### 1) Add FranceConnectBundle to your project

```bash
composer require kleegroup/france-connect
```

### 2) Enable the bundle

Enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new KleeGroup\FranceConnectBundle\FranceConnectBundle(),
    );
}
```

### 3) Import routes

``` yaml
france_connect:
    resource: "@FranceConnectBundle/Controller/"
    type:     annotation
    prefix:   /
``` 

[Step 2: Configuration](configuration.md)