<?php

namespace Services;

use Boot\Http\Service\AbstractService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EmployeeService.
 */
class EmployeeService extends AbstractService
{
    /**
     * Returns all employees.
     *
     * @param Request $request A request object
     *
     * @return array Arrays or instances of JsonSerializable are automatically encoded as json
     */
    public function all(Request $request)
    {
        // Only for demo purposes:
        //$service = $this->getServiceContainer()->get('some-service');

        // This service method returns a raw array
        return [
            ['id' => 1, 'firstName' => 'Jan', 'lastName' => 'Bakker', 'age' => 30],
            ['id' => 2, 'firstName' => 'Ben', 'lastName' => 'Gootmaker', 'age' => 32],
            ['id' => 3, 'firstName' => 'Nico', 'lastName' => 'Fransen', 'age' => 24],
            ['id' => 4, 'firstName' => 'Jacob', 'lastName' => 'Roos', 'age' => 27],
        ];
    }

    /**
     * Update an employee.
     *
     * @param Request $request A request object
     *
     * @return string A textual response is outputted as is
     */
    public function update(Request $request)
    {
        return __METHOD__;
    }

    /**
     * This method will delete an employee and send a 201 Accepted on success.
     *
     * @param Request $request A request object
     *
     * @return Response A regular symfony response object
     */
    public function delete(Request $request)
    {
        return Response::create('ACCEPTED', 201);
    }

    /**
     * This method will add an employee and send a 201 Accepted on success.
     *
     * @param Request $request A request object
     *
     * @return Response A regular symfony response object
     */
    public function create(Request $request)
    {
        return Response::create('ACCEPTED', 201);
    }
}
