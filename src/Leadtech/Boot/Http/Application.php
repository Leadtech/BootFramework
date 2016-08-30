<?php

namespace Boot\Http;

use Boot\AbstractServiceContainerDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Application extends AbstractServiceContainerDecorator
{
    /** @var  string */
    private $httpServiceIdentifier;

    /**
     * Application constructor.
     *
     * @param ContainerInterface $internal
     * @param $httpServiceIdentifier
     */
    public function __construct(ContainerInterface $internal, $httpServiceIdentifier)
    {
        parent::__construct($internal);

        $this->httpServiceIdentifier = $httpServiceIdentifier;
    }

    /**
     * Run the web application.
     *
     * @param Request|null $request
     */
    public function run(Request $request = null)
    {
        if (!$request) {
            $request = Request::createFromGlobals();
        }

        $this->get($this->httpServiceIdentifier)->handle($request);
    }
}
