Esb Health Check Symfony Bundle
=================================

Installation
============

Step 1: Download the Bundle
----------------------------------
Open a command console, enter your project directory and execute:

###  Applications that use Symfony Flex

```console
$ composer require esb/health-check-symfony
```


Step 2: Enable the Bundle
----------------------------------
Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
<?php

// config/bundles.php

return [
    // ...
    Esb\HealthCheckSymfony\HealthCheckSymfonyBundle::class => ['all' => true],    
];
```

Create Config files:
----------------------------------
`config/packages/health_check_symfony.yaml`

```yaml
health_check_symfony:
    checks:
        - id: Esb\HealthCheckSymfony\Checks\DoctrineCheck
        - id: Esb\HealthCheckSymfony\Checks\RedisCheck

    rabbitmq_queues: # optional
      - name: 'queue_name'

    kafka: # optional
        group:
        broker_list:
        sasl_username:
        sasl_password:
        security_protocol:
        sasl_mechanism:
        topics:
            - name:
```

----------------------------------
`config/routes/health_check_symfony.yaml`

```yaml
health_check:
    path: /healthcheck
    methods: GET
    controller: Esb\HealthCheckSymfony\Controller\HealthCheckController::index

```

Step 3: Configuration
=============

Security Optional:
----------------------------------
`config/packages/security.yaml`

If you are using [symfony/security](https://symfony.com/doc/current/security.html) and your health check is to be used anonymously, add a new firewall to the configuration

```yaml
    firewalls:
        healthcheck:
            pattern: ^/healthcheck
            security: false
```

Step 4: Additional settings
=============

Add Custom Check:
----------------------------------
It is possible to add your custom health check:

```php
<?php
declare(strict_types=1);
namespace YourProject\Check;

use Esb\HealthCheck\HealthCheck;
use Esb\HealthCheck\Status;

class CustomCheck implements HealthCheck
{
    public function name(): string
    {
        return 'custom_check_name';
    }

    public function handle(): Status
    {
        return $this->okay();
    }
}
```

Then add custom health check to collection

```yaml
health_check_symfony:
    checks:
        - id: Esb\HealthCheckSymfony\Checks\DoctrineCheck
        - id: Esb\HealthCheckSymfony\Checks\RedisCheck
        - id: YourProject\Check\CustomCheck
```
