<?php

namespace Boot\Http\Metadata;

use Boot\Http\Metadata\Input\PathParam;
use Boot\Http\Metadata\Input\PostField;
use Boot\Http\Metadata\Input\QueryParam;
use Boot\Http\Metadata\Input\RequestHeader;
use Boot\Http\Metadata\Input\RequestMessage;
use Boot\Http\Metadata\Schema\Definition\ObjectDefinition;
use Boot\Http\Metadata\Schema\Definition\TypeDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RouteSpecs
{
    /** @var  string */
    protected $description;

    /** @var  string */
    protected $longDescription;

    /** @var  string */
    protected $operationId;

    /** @var RequestHeader[]  */
    protected $requestHeaders = [];

    /** @var QueryParam[]  */
    protected $queryParams = [];

    /** @var PathParam[] */
    protected $pathParams = [];

    /** @var PostField[] name => value pairs  */
    protected $postFields = [];

    /** @var  ObjectDefinition */
    protected $requestBody;

    /** @var array  */
    protected $authScopes = [];

    /** @var Response[]  */
    protected $responses = [];

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return RouteSpecs
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * @param string $longDescription
     * @return RouteSpecs
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperationId()
    {
        return $this->operationId;
    }

    /**
     * @param string $operationId
     * @return RouteSpecs
     */
    public function setOperationId($operationId)
    {
        $this->operationId = $operationId;
        return $this;
    }

    /**
     * @param string         $name
     * @param TypeDefinition $type
     *
     * @return $this
     */
    public function requestHeader($name, TypeDefinition $type)
    {
        $this->requestHeaders[$name] = $type;

        return $this;
    }

    /**
     * @return Input\RequestHeader[]
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * @param string         $name
     * @param TypeDefinition $type
     *
     * @return $this
     */
    public function queryParam($name, TypeDefinition $type)
    {
        $this->queryParams[$name] = $type;

        return $this;
    }

    /**
     * @return Input\QueryParam[]
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param string         $name
     * @param TypeDefinition $type
     *
     * @return $this
     */
    public function pathParam($name, TypeDefinition $type)
    {
        $this->pathParams[$name] = $type;

        return $this;
    }

    /**
     * @return Input\PathParam[]
     */
    public function getPathParams()
    {
        return $this->pathParams;
    }

    /**
     * @return ObjectDefinition
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * @param ObjectDefinition $message
     * @return RouteSpecs
     */
    public function requestBody($message)
    {
        $this->requestBody = $message;
        return $this;
    }

    /**
     * @return array
     */
    public function getAuthScopes()
    {
        return $this->authScopes;
    }

    /**
     * @param array $scopes
     * @return RouteSpecs
     */
    public function authScopes($scopes)
    {
        $this->authScopes = $scopes;

        return $this;
    }

    /**
     * @return Input\PostField[]
     */
    public function getPostFields()
    {
        return $this->postFields;
    }

    /**
     * @param Input\PostField[] $postFields
     *
     * @return $this
     */
    public function postFields(array $postFields)
    {
        $this->postFields = $postFields;

        return $this;
    }


    /**
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param int    $statusCode  e.g. 200
     * @param string $statusText  e.g. SUCCESS
     * @param string $contentType e.g. application/json
     *
     * @return $this
     */
    public function addResponse($statusCode, $statusText, $contentType = null)
    {
        $response = new Response();
        $response->setStatusCode($statusCode, $statusText);
        if ($contentType) {
            $response->headers->set('Content-Type', $contentType);
        }

        $this->responses[] = $response;

        return $this;
    }


}