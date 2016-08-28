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
use Boot\Http\Metadata\Schema\SchemaFactory;
use Boot\Http\Router\RouteOptionsBuilder;
use Boot\Http\Metadata\RouteSpecs;

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
        ->remoteAccessPolicy(\Boot\Http\Security\RemoteAccessPolicy::forPrivateService()->denyIpAddress('127.0.*.*'))
        ->build()
    )
    // Register endpoint to create a new employee
    ->post('employees/{countryCode}', EmployeeService::class, 'create', (new RouteOptionsBuilder)
        ->routeName('create-employee')
        ->remoteAccessPolicy(\Boot\Http\Security\RemoteAccessPolicy::forPrivateService())
        ->build()
    )

    // Register endpoint to update an employee
    ->put('employees/{countryCode}', EmployeeService::class, 'update', new RouteOptions(
        'update-employee',
        function(SchemaFactory $schema) {
            return (new RouteSpecs)
                ->setDescription("Endpoint to update some random employees")
                ->setOperationId("updateEmployees")  // by default == route name, so for example update-employee
                ->requestHeader('X-AUTH', $schema->base64("Optional authentication header for registered users")
                    ->optional()
                )
                ->queryParam('cat', $schema->string("Provides the the name of a category.")
                    ->optional()
                )
                ->queryParam("topicIds", $schema->iterable('Several topic id\'s')
                    ->required()
                    ->minOccurs(1)
                    ->maxOccurs(10)
                    ->valueSeparator(',')  // automatically parse string value
                    ->valueSpecs(
                        $schema->int("A topic ID")->minValue(2)
                    )
                )
                ->pathParam('id', $schema->int("Unique identifier")
                    ->required()
                    ->inRange(0, PHP_INT_MAX)
                )
                ->postFields([
                    'foo' => $schema->string("Some foo value")->required()->minLength(5),
                    'password' => $schema->string("User password")->required()->minLength(5)->asPassword(),
                ])
                ->requestBody(
                    $schema->object("The request document")
                        ->required()
                        //->encoder(new JsonCodec)  // optional
                        //->decoder(new JsonCodec)  // optional
                        ->property("requestId", $schema->int("The request ID")
                            ->optional()
                            ->minValue(4)
                        )
                )
                ->authScopes([
                    "some-scope" => "some scope description"
                ])
                ->addResponse(200,  "", "application/json")
                ->addResponse(500, "unknown error occurred", "plain/text")
            ;
        }
    ))

    // Register endpoint to delete an employee
    ->delete('employees/{countryCode}', EmployeeService::class, 'create', new RouteOptions(
        'delete-employee'
    ))

    ->build()
;

// Create fake request
$app->run(
    Request::create('/employees/US')
    // Request::create('/employees/NL', 'DELETE', [], [], [], [], 'foo')
    // Request::createFromGlobals()
);
