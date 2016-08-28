<?php

namespace Boot\Http\Validator;

use Symfony\Component\HttpFoundation\Request;

interface RequestValidatorInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function validate(Request $request);
}