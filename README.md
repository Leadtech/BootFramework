# PHP Boot

### What is it, and who should use it?

Boot is a **minimalistic** implementation of the Symfony\DependencyInjection package that aims to provide a fully featured service container to light weight applications.
Most development teams with a need for speed will eventually consider micro frameworks. Boot is for developers who want to be able to keep using the wonderful
services container that symfony provides. Boot is designed not to solve as little as possible.
Boot is well suited for both high demanding projects or small applications that don't need a full stack framework.
Boot does not make assumptions about your tooling. You need to configure your services yourself. Boot provides a useful builder to bootstrap your new application.
Look at the examples to see how Boot can be used to bootstrap your application.

*Boot aims to be particularly useful for the following type of projects:*
* Micro-services / API's
* Console applications
* High demanding PHP web-applications



## Installation

### Composer

Add this to your composer.json:
```
{
    "require": {
        "leadtech/boot": "dev-master"
    }
}
````

## Examples

`For now there is only example of a hello world console application. More examples may be added in the near future.`

### Example: Boot Console Application


#### Bootstrap application
```php
// Autoload packages
require_once __DIR__ . '/vendor/autoload.php';

// Build application
$rootDir = realpath(__DIR__ . '/..');
$app = (new \Boot\Builder($rootDir))
    ->appName('SimpleConsoleApplication')
    ->caching('cache', true)
    ->environment('prod')
    ->path('resources/config')
    ->parameter('project_dir', $rootDir)
    ->beforeOptimization(new \Boot\Console\CompilerPass\CommandCompilerPass())
    ->build()
;

/** @var Symfony\Component\Console\Application $console */
$console = $app->get('console');
$console->run();
```


#### Configure service container
```
<!--
CONSOLE SERVICE
-->

<service id="console" class="Symfony\Component\Console\Application">
    <argument type="service" id="logger" />
    <call method="setName">
        <argument>%APP_NAME%</argument>
    </call>
    <call method="setVersion">
        <argument>%APP_VERSION%</argument>
    </call>
</service>

<!--
CONSOLE COMMANDS
-->

<service id="command.hello_world" class="HelloWorld\Command\HelloWorldCommand">
    <argument type="string">hello:world</argument>
    <argument type="service" id="logger" />
    <tag name="console_command" />
</service>
```
