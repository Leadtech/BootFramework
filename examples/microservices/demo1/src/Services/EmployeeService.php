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
     * Create the service with optional dependency lookup...
     *
     * @throws ServiceNotFoundException
     * @param ContainerInterface $serviceContainer
     * @return ServiceInterface
     */
    public static function createService(ContainerInterface $serviceContainer)
    {
        $service = new static();
        $service->setSomeDependency($serviceContainer->get('some.dependency'));

        return $service;
    }

    /**
     * @param Request $request
     * @return array
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
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        return __METHOD__;
    }

    /**
     * This method will delete an employee and send a 201 Accepted on success.
     *
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        // This service method returns a normal symfony response instance.
        return Response::create('ACCEPTED', 201);
    }

    /**
     * This method will add an employee and send a 201 Accepted on success.
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        // This service method returns a normal symfony response instance.
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