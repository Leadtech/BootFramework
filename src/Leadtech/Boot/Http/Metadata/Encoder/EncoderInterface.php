<?php

namespace Boot\Http\Metadata\Encoder;

/**
 * Interface EncoderInterface
 *
 * @package Boot\Http\Metadata\Encoder
 */
interface EncoderInterface
{
    /**
     * @param object $object
     *
     * @return string
     */
    public function encode($object);
}