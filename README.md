# PHP Boot

The motivation for writing this framework arises from the need for a micro framework without having to sacrifice good design practices.


## Overview 


Boot is a **minimalistic** framework aimed to develop lightweight PHP applications.
Well suited use cases for Boot are **micro services** or other applications for which micro frameworks are 
often a better fit such as **console applications** or other forms of background processes. 

For usage examples check out the examples folder.

The motivation for writing this framework arises from the need for a micro framework without having to sacrifice good design practices. 
Most if not all micro frameworks sacrifice clarity and good design practices in exchange for performance.
This framework will sacrifice a few milliseconds for implementing some of the core functionality that feature the full stack framework.
Making it possible to develop well designed micro services -/ applications following largely the same patterns as you would using Symfony.
This also ensures better compatibility and will make it easier to migrate to Symfony if an application unexpectedly grows bigger 
than intented and the full stack framework is preferred.


This tool is designed to add as little overhead as possible. Boot provides a builder to bootstrap a PHP application.
A WebBuilder class is used to bootstrap a micro service. Please look at the examples to see how Boot can be used.


*Boot aims to be particularly useful for the following type of projects:*
* Micro-services / API's
* Console applications / background processes
* Customized frameworks
* Other lightweight web applications that are better off using a micro framework

 
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
