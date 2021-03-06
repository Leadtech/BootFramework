<p align="center">
<b><a href="#examples">Examples</a></b>
|
<b><a href="#installation">Installation</a></b>
|
<b><a href="#versioning">Versioning</a></b>
|
<b><a href="#improvements">Improvements</a></b>
|
<b><a href="#contribute">Contribute</a></b>
|
<b><a href="#license">License</a></b>
</p>

<br>

[![Build Status](https://travis-ci.org/Leadtech/BootFramework.svg?branch=master)](https://travis-ci.org/Leadtech/BootFramework)
[![Code Climate](https://codeclimate.com/github/Leadtech/BootFramework/badges/gpa.svg)](https://codeclimate.com/github/Leadtech/BootFramework)
[![Test Coverage](https://codeclimate.com/github/Leadtech/BootFramework/badges/coverage.svg)](https://codeclimate.com/github/Leadtech/BootFramework/coverage)
![License](http://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP->=7.1-blue.svg)
![Platform](https://img.shields.io/badge/platform-Windows%20%7C%20iOS%20%7C%20Linux%20%7C%20Unix-lightgrey.svg)


# Boot Framework

Boot is a **minimalistic** framework designed with simplicity and flexibility in mind. 
This framework is primarily intended for **API's**/**micro services** or **console applications**. 

## Getting started

The goal is to provide a micro framework that is fast, flexible but without sacrificing *most* of the useful functionality that Symfony offers to develop well designed and maintainable applications.
Boot is a micro framework that is build on the components that Symfony developers know and love.
Boot is designed with speed and efficiency in mind.  But the main goal of this framework is not to become *the fastest* framework.
The main goal is to provide a minimalistic framework to help shape well designed PHP applications and to increase productivity.
Boot is highly extensible which makes it easy to fit the framework to your needs.
To wire the application use *or extend* one of the *Builder* classes to configure and build your application.
For examples please go to the <a href="#examples">examples</a> section or go to the examples directory.


### Features

- Clean and flexible bootstrap
- Integrates Symfony Config
- Integrates Symfony Dependency Injection
- Integrates Symfony Event Dispatcher
- Integrates Symfony Console (optional)
- Integrates Symfony Http Foundation (optional)
- Integrates Symfony Router (optional)

<p align="right"><a href="#top">:arrow_up:</a></p>

## Examples

Examples: 

- Example 1:  Basic Application
- Example 2:  Boot Micro Service
- Example 3:  Boot Console Application

**For full usage examples check out the examples folder.**

### Example 1: Basic Application

#### Bootstrapping the application

```php
// Build application
$rootDir = realpath(__DIR__ . '/..');
$app = (new \Boot\Builder($rootDir))
    ->appName('BasicApplication')
    ->optimize('tmp/cache')
    ->environment(Boot::PRODUCTION)
    ->configDir('resources/config')
    ->build()
;

$service = $app->get('my-service');
$service->doSomething();
```


#### Configure service container
```xml
<service id="my-service" class="SomeClass" />
```

### Example 2: Boot Micro Service


#### Bootstrapping the application

```php
$app = (new \Boot\Http\WebBuilder($rootDir))

    // Set application name
    ->appName('SimpleMicroService')
    
    // Set version
    ->appVersion('1.0.0')
    
    // The service ID for the HTTP service, defaults to 'http'
    ->httpServiceIdentifier('http')
    
    // Optimize performance by compiling the resolved state of the service container and routing configuration.
    // The framework will generate highly optimized classes to provide the same context. Only faster :-)
    // This optimization is ny default ignored in any other environment than production.
    ->optimize('tmp/cache')
    
    // Sets the environment (the environment 
    ->environment(Boot::DEVELOPMENT)
    
    // Add path to a config directory
    ->configDir('../../shared/config')
    
    // Add path to another config directory
    ->configDir('resources/config')

    // Add a parameter (available in the service container as %project_dir% and can be injected to other services)
    ->parameter('project_dir', $rootDir)
    
    // Sets default values for route parameters
    ->defaultRouteParams(['countryCode' => 'NL'])

    // Sets default constraints for route parameters
    ->defaultRouteRequirements(['countryCode' => 'US|EN|FR|NL'])

    // Register endpoint to get employees
    ->get('employees/{countryCode}', EmployeeService::class, 'all', new RouteOptions(
        'all-employees'
    ))

    // Register endpoint to create a new employee
    ->post('employees/{countryCode}', EmployeeService::class, 'create', new RouteOptions(
        'create-employee'
    ))

    // Register endpoint to update an employee
    ->put('employees/{countryCode}', EmployeeService::class, 'update', new RouteOptions(
        'update-employee'
    ))

    // Register endpoint to delete an employee
    ->delete('employees/{countryCode}', EmployeeService::class, 'create', new RouteOptions(
        'delete-employee'
    ))

    ->build()
;

// Handle HTTP request
$app->run();
// is equivalent to: $app->get($builder->getHttpServiceIdentifier())->handle(Request::createFromGlobals());
```

#### Micro-service Implementation

Although services in boot are very similar to controllers. I chose to use a different terminology for Boot. Controllers are typical to MVC frameworks. If feel like the term 'controller' usually implies an architecture in which a single controller is one amongst many.
In order to emphasize the intended purpose of this framework to use it for micro-services / API's I felt like it would be more appropriate to call them Services.

```php
class EmployeeService extends AbstractService
{
    /**
     * Returns all employees
     *
     * @return array               Arrays or instances of JsonSerializable are automatically encoded as json
     */
    public function all()
    {
        // For demo purposes:
        // echo $this->getServiceContainer()->get('blaat');
        // print_r($this->getRouteMatch()->getRouteParams());
        // $request = $this->getRequest();
    
        return [
            ['id' => 1, 'firstName' => 'Jan', 'lastName' => 'Bakker', 'age' => 30],
            ['id' => 2, 'firstName' => 'Ben', 'lastName' => 'Gootmaker', 'age' => 32],
        ];
    }

    /**
     * Update an employee
     *
     * @return string              A textual response is outputted as is
     */
    public function update()
    {
        return __METHOD__;
    }

    /**
     * This method will delete an employee and send a 201 Accepted on success.
     *
     * @return Response           A regular symfony response object
     */
    public function delete()
    {
        return Response::create('ACCEPTED', 201);
    }

    /**
     * This method will add an employee and send a 201 Accepted on success.
     *
     * @return Response           A regular symfony response object
     */
    public function create()
    {
        return Response::create('ACCEPTED', 201);
    }
    
}
```


### Example 3: Boot Console Application

#### Bootstrapping the application

```php
// Build application
$rootDir = realpath(__DIR__ . '/..');
$app = (new \Boot\Console\ConsoleBuilder($rootDir))
    ->appName('SimpleConsoleApplication')
    ->optimize('tmp/cache')
    ->environment(Boot::PRODUCTION)
    ->configDir('resources/config')
    ->configDir('src/MyPackage/resources/config')
    ->parameter('project_dir', $rootDir)
    ->parameter('some_other_variable', 123)
    ->consoleServiceIdentifier('my_custom_console_id')
    ->build()
;

// Run the application
$app->run();
```


#### Configure service container
```xml
<!--
CONSOLE COMMANDS
-->
<service id="command.hello_world" class="HelloWorld\Command\HelloWorldCommand">
    <argument type="string">hello:world</argument>
    <argument type="service" id="logger" />
    <tag name="console_command" />
</service>
```

<p align="right"><a href="#top">:arrow_up:</a></p>

## Installation

### Dependencies

To get started you will need to install composer. I will assume you have composer installed or know how to do it.
Otherwise go to https://getcomposer.org and follow the steps needed to install composer. 

### Composer

#### Add dependency to composer.json

*For PHP 5.5, 5.6 and 7.0*
```json
{
    "require": {
        "leadtech/boot-framework": "2.8.*"
    }
}
```

*For PHP >= 7.1*
```json
{
    "require": {
        "leadtech/boot-framework": "3.3.*"
    }
}
```

#### Install dependencies
```console
$ php composer.phar install
```


## Compatibility

To ensure compatibility with applications build on Symfony and to minimize the required maintainance efforts I chose not to extend any of the Symfony components.
With boot I wanted to be able to cut them loose, discard all logic that was not required to implement the component and finally provide a way to wire them up. 
Symfony compiles the router and the dependency injection container to generate highly optimized classes to be used in production.
Boot fully supports both optimizations.

<p align="right"><a href="#top">:arrow_up:</a></p>

## Versioning

The goal is to support (at least) every Symfony LTS version that is still maintained by Symfony.
The major and minor release numbers of stable Boot framework releases reflects the Symfony release that Boot is build on.
*Boot Framework 2.8.0 has been developed against Symfony 2.8.9.*.

<p align="right"><a href="#top">:arrow_up:</a></p>

## Improvements


### Prepare for next LTS release (Symfony 3.2)

- Assess the impact of the changes in the next LTS release

### Roadmap

- More code examples -/ starter projects
- 100% code coverage
- Add console command to the clear the cache
- Add console command to print all routes to the console
- Improve doc blocks
- Integrate php-cs-fixer in travis

### Wishlist...
- Add new features targeted towards developing API's (e.g. generate api docs, support for swagger etc)
- Add support for distributed tracing

### Caveats

- At this time only the XML configuration format is supported. Personally, I prefer XML. But the others can be implemented in the future.


## Contribute

Contributions to Boot Framework are welcome.  

### Issues

Feel free to submit issues and enhancement requests.

### Contributions

For this project use a "fork-and-pull" workflow.

 1. **Fork** the repo on GitHub
 2. **Clone** the project to your own machine
 3. **Commit** changes to your own branch
 4. **Run php-cs-fixer** to fix code style inconsistencies automatically
  ```console
  > php php-cs-fixer.phar fix src/Leadtech/Boot/NewAwesomeFeature/ --level=symfony
  ```
 4. **Push** your work back up to your fork
 5. Submit a **Pull request** 

*Be sure to merge the latest from "upstream" before making a pull request.*


### Design Considerations

Contributions to Boot Framework are welcome. And the least I can do to show my gratitude is look at the contribution in due time. 
The project is not at a point yet to think about an extensive document about design considerations and coding guidelines.
But if you are an early contributor please look at the current source code to get a sense of how the application is structured. If you are in doubt feel free
to contact me. I will do what I can to help. 


<p align="right"><a href="#top">:arrow_up:</a></p>

## License

The MIT License

Copyright (c) 2016  -  Daan Biesterbos

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
