<?php

namespace Boot\Http\Metadata\Schema\Definition;

/**
 * Class Base64Type
 *
 * @package Boot\Http\Metadata\Schema\Definition
 */
class Base64Definition extends StringDefinition
{
    // Currently only here to make base 64 a distinctive type. This code was inspired and partially based on the data
    // model of both swagger and google api discovery. Both build on json schema drafts that list base64 as a data type.
    // Because PHP handles base64 very different from for example java I have yet to determine if base64 should have
    // options at all in PHP, and which ones would be worth to implement.
    // Theoretically there could be some, I found some hacky solutions to modify padding, or to mimic url safe base64
    // encoding. But than again. It is questionable if its a good thing to do support.
    // It certainly is not on my agenda right now.
}