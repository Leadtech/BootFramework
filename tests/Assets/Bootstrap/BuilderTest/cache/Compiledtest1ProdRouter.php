<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * Compiledtest1ProdRouter.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class Compiledtest1ProdRouter extends Symfony\Component\Routing\Matcher\UrlMatcher
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

        if (0 === strpos($pathinfo, '/foo')) {
            // array-test
            if ($pathinfo === '/foo/array') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_arraytest;
                }

                return array('_serviceClass' => 'Boot\\Tests\\Assets\\Http\\FooService',  '_serviceMethod' => 'returnArray',  '_route' => 'array-test');
            }
            not_arraytest:

            if (0 === strpos($pathinfo, '/foo/return-')) {
                // json-test
                if ($pathinfo === '/foo/return-json') {
                    if ($this->context->getMethod() != 'PATCH') {
                        $allow[] = 'PATCH';
                        goto not_jsontest;
                    }

                    return array('_serviceClass' => 'Boot\\Tests\\Assets\\Http\\FooService',  '_serviceMethod' => 'returnJsonSerializable',  '_route' => 'json-test');
                }
                not_jsontest:

                // response-object-test
                if ($pathinfo === '/foo/return-object') {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_responseobjecttest;
                    }

                    return array('_serviceClass' => 'Boot\\Tests\\Assets\\Http\\FooService',  '_serviceMethod' => 'returnResponseObject',  '_route' => 'response-object-test');
                }
                not_responseobjecttest:

                // json-response-object-test
                if ($pathinfo === '/foo/return-json-object') {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_jsonresponseobjecttest;
                    }

                    return array('_serviceClass' => 'Boot\\Tests\\Assets\\Http\\FooService',  '_serviceMethod' => 'returnJsonResponseObject',  '_route' => 'json-response-object-test');
                }
                not_jsonresponseobjecttest:

                // string-test
                if ($pathinfo === '/foo/return-string') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_stringtest;
                    }

                    return array('_serviceClass' => 'Boot\\Tests\\Assets\\Http\\FooService',  '_serviceMethod' => 'returnString',  '_route' => 'string-test');
                }
                not_stringtest:
            }
        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
