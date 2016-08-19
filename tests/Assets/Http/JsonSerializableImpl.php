<?php

namespace Boot\Tests\Assets\Http;

class JsonSerializableImpl implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return [
            'foo' => 'bar',
            'bar' => 'foo',
        ];
    }
}
