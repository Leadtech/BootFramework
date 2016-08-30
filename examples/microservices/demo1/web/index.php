#!/usr/bin/env php
<?php
// Autoload packages
require_once __DIR__.'/../../../../vendor/autoload.php';

// Alias symfony console application
use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Boot\Http\Router\RouteOptions;
use Services\EmployeeService;
use Symfony\Component\HttpFoundation\Request;
use Boot\Boot;
use Boot\Http\Router\RouteOptionsBuilder;

// Just for demo purposes, auto loading could be moved to composer config
$loader = new Psr4ClassLoader();
$loader->addPrefix('Services\\', __DIR__.'/../src/Services');
$loader->register();

// Build application
$rootDir = realpath(__DIR__.'/..');


$app = (new \Boot\Http\WebBuilder($rootDir))

    // Set application name
    ->appName('SimpleMicroService')

    // Optimize performance by compiling the resolved state of the service container and routing configuration.
    // The framework will generate highly optimized classes to provide the same context. Only faster :-)
    // This optimization is ny default ignored in any other environment than production.
    ->optimize('tmp/cache')

    // Sets the environment (the environment
    ->environment(Boot::DEVELOPMENT)

    // Add path to another config directory
    ->configDir('resources/config')

    // Add a parameter (available in the service container as %project_dir% and can be injected to other services)
    ->parameter('project_dir', $rootDir)

    // Sets default values for route parameters
    ->defaultRouteParams(['countryCode' => 'NL'])

    // Sets default constraints for route parameters
    ->defaultRouteRequirements(['countryCode' => 'US|EN|FR|NL'])

    // Register endpoint to get employees
    ->get('employees/{countryCode}', EmployeeService::class, 'all',(new RouteOptionsBuilder)
        ->routeName('all-employees')
        ->build()
    )
    // Register endpoint to create a new employee
    ->post('employees/{countryCode}', EmployeeService::class, 'create', (new RouteOptionsBuilder)
        ->routeName('create-employee')
        ->build()
    )

    // Register endpoint to update an employee
    ->put('employees/{countryCode}', EmployeeService::class, 'update', (new RouteOptionsBuilder)
        ->routeName('update-employee')
        ->build()
    )

    // Register endpoint to delete an employee
    ->delete('employees/{countryCode}', EmployeeService::class, 'delete',  (new RouteOptionsBuilder)
        ->routeName('delete-employee')
        ->remoteAccessPolicy(\Boot\Http\Security\RemoteAccessPolicy::forPrivateService())
        ->build()
    )

    ->build()
;

// Create fake request
$app->run(
    Request::create('/employees/US')
    // Request::create('/employees/NL', 'DELETE', [], [], [], [], 'foo')
    // Request::createFromGlobals()
);
