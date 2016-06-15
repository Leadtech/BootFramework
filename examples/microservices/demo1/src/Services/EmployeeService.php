<?php
namespace Services;

use Boot\Http\Service\AbstractService;
use Boot\Http\Service\ServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeService extends AbstractService
{
    /** @var  object */
    protected $someDependency;

    /**
     * Create the service and do optional dependency lookup for demonstration purposes...
     * When no dependency lookup is needed this method
     *
     * @throws ServiceNotFoundException
     *
     * @param  ContainerInterface $serviceContainer
     *
     * @return ServiceInterface
     */
    public static function createService(ContainerInterface $serviceContainer)
    {
        /** @var self $service */
        $service = parent::createService($serviceContainer);
        $service->setSomeDependency($serviceContainer->get('some.dependency'));

        return $service;
    }

    /**
     * Returns all employees
     *
     * @param Request $request     A request object
     *
     * @return array               Arrays or instances of JsonSerializable are automatically encoded as json
     */
    public function all(Request $request)
    {
        // This service method returns a raw array
        return [
            ['id' => 1, 'firstName' => 'Jan', 'lastName' => 'Bakker', 'age' => 30],
            ['id' => 2, 'firstName' => 'Ben', 'lastName' => 'Gootmaker', 'age' => 32],
            ['id' => 3, 'firstName' => 'Nico', 'lastName' => 'Fransen', 'age' => 24],
            ['id' => 4, 'firstName' => 'Jacob', 'lastName' => 'Roos', 'age' => 27],
        ];
    }

    /**
     * Update an employee
     *
     * @param Request $request     A request object
     *
     * @return string              A textual response is outputted as is
     */
    public function update(Request $request)
    {
        return __METHOD__;
    }

    /**
     * This method will delete an employee and send a 201 Accepted on success.
     *
     * @param Request $request    A request object
     * @return Response           A regular symfony response object
     */
    public function delete(Request $request)
    {
        return Response::create('ACCEPTED', 201);
    }

    /**
     * This method will add an employee and send a 201 Accepted on success.
     *
     * @param Request $request    A request object
     * @return Response           A regular symfony response object
     */
    public function create(Request $request)
    {
        return Response::create('ACCEPTED', 201);
    }

    /**
     * @return object
     */
    public function getSomeDependency()
    {
        return $this->someDependency;
    }

    /**
     * @param object $someDependency
     */
    public function setSomeDependency($someDependency)
    {
        $this->someDependency = $someDependency;
    }
}