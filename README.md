# PHP Boot

Boot is a **minimalistic** framework aimed to develop lightweight PHP applications.
Well suited use cases for Boot are **micro services** and other applications for which micro frameworks are 
often a natural fit such as **console applications** or background processes. 


For usage examples check out the examples folder.

## Why Boot? 

The motivation for writing this framework arises from the need for a micro framework without having to sacrifice good design practices. 
My goal was to develop a framework that is fast, simple, free of as much verbosity as possible, but without sacrificing the features, clearity
and design practices that makes symfony one of the most popular PHP frameworks today.

The framework is a mimimal wrapper of the well known service container, router and http components. 
This framework will sacrifice a few milliseconds per request for implementing core components that feature the full stack framework.
By following largely the same patterns as you would have using the full stack framework boot projects are mostly compatible with 
symfony making it easier to migrate in case an application grows bigger than expected and the fullstack framework is the 
preferred alternative.


Boot provides a builder to bootstrap a PHP application. A special web builder is provided to bootstrap micro services. 

*Boot aims to be particularly useful for the following type of projects:*
* Micro-services or other light weight web applications
* Console applications / background processes
* Customized frameworks


Boot is highly extensible which makes it easy to fit the framework to your needs.

 
#### Build on symfony components

The framework is a mimimal implementation of well known service container, router and http components. 


## Installation

### Composer

Add this to your composer.json:
```
{
    "require": {
        "leadtech/boot": "^1.0"
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
    ->path('src/MyPackage/resources/config')
    ->parameter('project_dir', $rootDir)
    ->parameter('some_other_variable', 123)
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
