#!/usr/bin/env php
<?php
// Autoload packages
require_once __DIR__ . '/../../../../vendor/autoload.php';

// Alias symfony console application
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Boot\Http\Router\RouteOptions;
use Services\EmployeeService;
use Symfony\Component\HttpFoundation\Request;
use Boot\Boot;

// Register autoloader (in your own product use the autoloader that ships with composer, this is just for demo purposes)
$loader = new Psr4ClassLoader();
$loader->addPrefix('Services\\', __DIR__ . '/../src/Services');
$loader->register();

// Build application
$rootDir = realpath(__DIR__ . '/..');

$app = (new \Boot\Http\WebBuilder($rootDir))

    ->appName('SimpleMicroService')
    ->caching('cache', true)
    ->environment(Boot::DEVELOPMENT)
    ->path('resources/config')
    ->parameter('project_dir', $rootDir)
    ->pathDefaults(['countryCode' => 'NL'])

    ->defaultPathRequirements(['countryCode' => 'US|EN|FR|NL'])

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
    ->delete('employees/{countryCode}', EmployeeService::class, 'create', new RouteOptions(
        'delete-employee'
    ))

    ->build()
;

$app->get('http')->handle(
    Request::create('/employees/US')
    // Request::create('/employees/NL', 'DELETE', [], [], [], [], 'foo')
);