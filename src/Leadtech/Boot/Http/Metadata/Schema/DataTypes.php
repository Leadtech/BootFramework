<?php

namespace Boot\Http\Metadata\Schema;

/**
 * Interface DataTypes
 *
 * Define the available data types along with the actual atomic types as value.
 *
 * @package Boot\Http\Metadata\Schema
 */
interface DataTypes
{
    const UNKNOWN = 'string';
    const BOOLEAN = 'boolean';
    const I32     = 'i32';
    const I64     = 'i64';
    const I16     = 'i16';
    const FLOAT   = 'float';
    const DOUBLE  = 'double';
    const STRING  = 'string';
    const BASE64  = 'string';
}