<?php

namespace Boot\Http\Metadata\Input;

use Symfony\Component\HttpFoundation\Request;

interface InputInterface
{
    public function validate(Request $request);
}