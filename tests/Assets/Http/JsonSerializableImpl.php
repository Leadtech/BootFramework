<?php
namespace Boot\Tests\Assets\Http;
class JsonSerializableImpl implements \JsonSerializable
{
    function jsonSerialize()
    {
        return [
            'foo' => 'bar',
            'bar' => 'foo'
        ];
    }
}