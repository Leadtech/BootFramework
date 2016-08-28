<?php

namespace Boot\Http\Metadata\Decoder;

/**
 * Interface DecoderInterface
 *
 * @package Boot\Http\Metadata\Decoder
 */
interface DecoderInterface
{
    /**
     * @param string $string
     *
     * @return object
     */
    public function decode($string);
}