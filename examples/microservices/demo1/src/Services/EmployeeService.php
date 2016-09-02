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
     * @return array Arrays or instances of JsonSerializable are automatically encoded as json
     */
    public function all()
    {
        // For demo purposes only:
        // echo $this->getServiceContainer()->get('blaat');
        // $request = $this->getRequest();
        // $routeMatch = $this->getRouteMatch();

        return [
            ['id' => 1, 'firstName' => 'Jan', 'lastName' => 'Bakker', 'age' => 30],
            ['id' => 2, 'firstName' => 'Ben', 'lastName' => 'Gootmaker', 'age' => 32],
        ];
    }

    /**
     * Update an employee.
     *
     * @return string A textual response is outputted as is
     */
    public function update()
    {
        return __METHOD__;
    }

    /**
     * This method will delete an employee and send a 201 Accepted on success.
     *
     * @return Response A regular symfony response object
     */
    public function delete()
    {
        return Response::create('ACCEPTED', 201);
    }

    /**
     * This method will add an employee and send a 201 Accepted on success.
     *
     * @return Response A regular symfony response object
     */
    public function create()
    {
        return Response::create('ACCEPTED', 201);
    }
}
