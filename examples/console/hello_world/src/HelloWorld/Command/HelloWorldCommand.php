<?php

namespace HelloWorld\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloWorldCommand.
 */
class HelloWorldCommand extends Command
{
    const SUCCESS_EXIT_CODE = 0;
    const FAILED_EXIT_CODE = 1;

    /** @var  InputInterface */
    protected $input = null;

    /** @var  OutputInterface */
    protected $output = null;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * @param null|string     $name
     * @param LoggerInterface $logger
     */
    public function __construct($name, LoggerInterface $logger = null)
    {
        parent::__construct($name);
        $this->logger = $logger;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this->setDescription('This is a hello world command. Please enter your name.')
            ->addArgument('your-name',   InputArgument::REQUIRED, 'Your name')
            ->addOption('--age', '-a',   InputOption::VALUE_REQUIRED, 'Your age');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set input and output
        $this->input = $input;
        $this->output = $output;

        $output->writeln("Hi {$input->getArgument('your-name')}!");
        if (!empty($input->getOption('age'))) {
            $output->writeln("{$input->getOption('age')}? That's pretty old. Do you know any dinosaurs?");
        }
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
