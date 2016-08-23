<?php

namespace Boot\Console;

use Boot\AbstractServiceContainerDecorator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends AbstractServiceContainerDecorator
{
    /** @var  string */
    private $consoleServiceIdentifier;

    /**
     * Application constructor.
     *
     * @param ContainerInterface $internal
     * @param $consoleServiceIdentifier
     */
    public function __construct(ContainerInterface $internal, $consoleServiceIdentifier)
    {
        parent::__construct($internal);

        $this->consoleServiceIdentifier = $consoleServiceIdentifier;
    }

    /**
     * Run the console application.
     */
    public function run()
    {
        /** @var \Symfony\Component\Console\Application $console */
        $console = $this->get($this->consoleServiceIdentifier);
        $console->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The environment name.', 'dev')
        );

        $console->run();
    }
}
