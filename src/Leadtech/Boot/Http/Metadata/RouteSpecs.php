<?php

namespace Boot\Http\Metadata;

use Boot\Http\Metadata\Schema\Definition\ObjectDefinition;
use Boot\Http\Metadata\Schema\Definition\TypeDefinition;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RouteSpecs
 *
 * @package Boot\Http\Metadata
 */
class RouteSpecs
{
    /** @var  string */
    protected $description;

    /** @var  string */
    protected $longDescription;

    /** @var  string */
    protected $operationId;

    /** @var TypeDefinition[]   name => type pairs  */
    protected $requestHeaders = [];

    /** @var TypeDefinition[]   name => type pairs  */
    protected $queryParams = [];

    /** @var TypeDefinition[]   name => type pairs */
    protected $pathParams = [];

    /** @var TypeDefinition[]   name => type pairs  */
    protected $postFields = [];

    /** @var  ObjectDefinition */
    protected $requestBody;

    /** @var string[]  */
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
     * @return TypeDefinition[]   name => type pairs
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
     * @return TypeDefinition[]   name => type pairs
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
     * @return TypeDefinition[]   name => type pairs
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
     * @return string[] name => value pairs
     */
    public function getAuthScopes()
    {
        return $this->authScopes;
    }

    /**
     * @param string[] $scopes  name => value pairs
     *
     * @return RouteSpecs
     */
    public function authScopes($scopes)
    {
        $this->authScopes = $scopes;

        return $this;
    }

    /**
     * @return TypeDefinition[]   name => type pairs
     */
    public function getPostFields()
    {
        return $this->postFields;
    }

    /**
     * @param TypeDefinition[] $postFields  name => type pairs
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