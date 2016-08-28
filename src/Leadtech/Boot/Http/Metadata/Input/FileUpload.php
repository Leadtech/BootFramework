<?php

namespace Boot\Http\Metadata\Input;

use Symfony\Component\HttpFoundation\Request;

class FileUpload extends AbstractInput implements InputInterface
{
    public function validate(Request $request)
    {
    }
}