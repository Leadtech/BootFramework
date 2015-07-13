# PHP Boot
Most development teams with a need for speed will consider micro frameworks. This is a minimalistic implementation of the symfony service container to provide
the luxury of a fully featured IoC component for light weight applications.
This package provides an easy to use builder to configure the application context.
If caching is enabled the builder will compile the application context to PHP.
With performance and flexibility in mind this package is designed to be as bare-bones as can be.
The only goal of this package is to provide an advanced dependency injection component without making assumptions about other tooling.



# Example: Boot Console Application

`For now there is only example of a hello world console application. More examples may be added in the near future.`

// Autoload packages
require_once __DIR__ . '/vendor/autoload.php';

// Alias symfony console application
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\ClassLoader\Psr4ClassLoader;

// Register autoloader (in your own product use the autoloader that ships with composer, this is just for demo purposes)
$loader = new Psr4ClassLoader();
$loader->addPrefix('HelloWorld\\', __DIR__ . '/../src/HelloWorld');
$loader->register();

// Build application
$rootDir = realpath(__DIR__ . '/..');
$app = (new \Leadtech\Boot\Builder($rootDir))
    ->appName('SimpleConsoleApplication')
    ->caching('cache', true)
    ->environment('prod')
    ->path('resources/config')
    ->parameter('project_dir', $rootDir)
    ->beforeOptimization(new \Leadtech\Boot\Console\CompilerPass\CommandCompilerPass())
    ->build()
;

/** @var ConsoleApplication $console */
$console = $app->get('console');
$console->run();

