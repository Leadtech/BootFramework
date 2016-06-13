<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * CompiledSimpleMicroServiceDevRouter.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class CompiledSimpleMicroServiceDevRouter extends Symfony\Component\Routing\Matcher\UrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        if (0 === strpos($pathinfo, '/employees')) {
            // all-employees
            if (preg_match('#^/employees(?:/(?P<countryCode>US|EN|FR|NL))?$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_allemployees;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'all-employees')), array (  'countryCode' => 'NL',  '_serviceClass' => 'Services\\EmployeeService',  '_serviceMethod' => 'all',));
            }
            not_allemployees:

            // create-employee
            if (preg_match('#^/employees(?:/(?P<countryCode>US|EN|FR|NL))?$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_createemployee;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'create-employee')), array (  'countryCode' => 'NL',  '_serviceClass' => 'Services\\EmployeeService',  '_serviceMethod' => 'create',));
            }
            not_createemployee:

            // update-employee
            if (preg_match('#^/employees(?:/(?P<countryCode>US|EN|FR|NL))?$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_updateemployee;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'update-employee')), array (  'countryCode' => 'NL',  '_serviceClass' => 'Services\\EmployeeService',  '_serviceMethod' => 'update',));
            }
            not_updateemployee:

            // delete-employee
            if (preg_match('#^/employees(?:/(?P<countryCode>US|EN|FR|NL))?$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_deleteemployee;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'delete-employee')), array (  'countryCode' => 'NL',  '_serviceClass' => 'Services\\EmployeeService',  '_serviceMethod' => 'create',));
            }
            not_deleteemployee:

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
