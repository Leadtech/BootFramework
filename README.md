# PHP Boot

**What is it?**
Most development teams with a need for speed will consider micro frameworks. This is a minimalistic implementation of the symfony service container to provide
the luxury of a fully featured IoC component to light weight applications.
This package provides an easy to use builder to setup the application context.
When caching is enabled the builder will compile the application context to PHP.
With performance and flexibility in mind this package is designed to be as bare-bones as it can be.
The only goal of this package is to provide an advanced dependency injection component without making assumptions about other tooling.


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
