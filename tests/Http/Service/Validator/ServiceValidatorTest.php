<?php

namespace Boot\Tests\Http\Service\Validator;

use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Service\Validator\ServiceValidator;
use Boot\Tests\Assets\Http\FooService;

/**
 * Class ServiceValidatorTest.
 */
class ServiceValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function serviceNotFoundValidation()
    {
        $this->setExpectedException(ServiceClassNotFoundException::class);
        $validator = new ServiceValidator();
        $validator->validateService('FooBar123', null);
    }

    /**
     * @test
     */
    public function serviceMethodNotFoundValidation()
    {
        $this->setExpectedException(ServiceMethodNotFoundException::class);
        $validator = new ServiceValidator();
        $validator->validateService(FooService::class, 'someNonExistentMethod');
    }

    /**
     * @test
     */
    public function serviceLogicValidation()
    {
        $this->setExpectedException(ServiceLogicException::class);
        $validator = new ServiceValidator();
        $validator->validateService(get_class($this), null);
    }
}
