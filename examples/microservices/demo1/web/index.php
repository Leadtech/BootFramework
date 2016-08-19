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

// Just for demo purposes, auto loading could be moved to composer config
$loader = new Psr4ClassLoader();
$loader->addPrefix('Services\\', __DIR__.'/../src/Services');
$loader->register();

// Build application
$rootDir = realpath(__DIR__.'/..');

$app = (new \Boot\Http\WebBuilder($rootDir))

    ->appName('SimpleMicroService')

    ->caching('cache', true)

    ->environment(Boot::DEVELOPMENT)

    ->configDir('resources/config')

    ->parameter('project_dir', $rootDir)

    ->defaultRouteParams(['countryCode' => 'NL'])

    ->defaultRouteRequirements(['countryCode' => 'US|EN|FR|NL'])

    // Get employees
    ->get('employees/{countryCode}', EmployeeService::class, 'all', new RouteOptions(
        'all-employees'
    ))

    // Create employee
    ->post('employees/{countryCode}', EmployeeService::class, 'create', new RouteOptions(
        'create-employee'
    ))

    // Update employee
    ->put('employees/{countryCode}', EmployeeService::class, 'update', new RouteOptions(
        'update-employee'
    ))

    // Delete employee
    ->delete('employees/{countryCode}', EmployeeService::class, 'delete', new RouteOptions(
        'delete-employee'
    ))

    ->build()
;

// Create fake request
$app->get('http')->handle(
    Request::create('/employees/US')
    // Request::create('/employees/NL', 'DELETE', [], [], [], [], 'foo')
    // Request::createFromGlobals()
);
