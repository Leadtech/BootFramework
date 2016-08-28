<?php

namespace Boot\Http\Metadata\Schema\Definition;

use Boot\Http\Metadata\Schema\DataTypes;

/**
 * Class StringType
 *
 * @package Boot\Http\Metadata\Schema\Definition
 */
class StringDefinition extends AtomicTypeDefinition
{
    const ATOMIC_TYPE = DataTypes::STRING;

    /** @var bool  */
    protected $password = false;

    /** @var bool  */
    protected $date = false;

    /** @var bool  */
    protected $datetime = false;

    /** @var int|null */
    protected $minLength = null;

    /** @var int|null */
    protected $maxLength = null;

    /**
     * Whether the string is a password. Is meant to hint the client to hide the text contents.
     *
     * @return $this;
     */
    public function asPassword()
    {
        $this->password = true;

        return $this;
    }

    /**
     * Whether the string contains a date string as specified in RFC3999.
     *
     * @see http://php.net/manual/en/class.datetime.php#datetime.constants.rfc3339
     *
     * @return $this;
     */
    public function asDate()
    {
        $this->date = true;
        $this->datetime = false;

        return $this;
    }

    /**
     * Whether the string contains a date time string as specified in RFC3999.
     *
     * @see http://php.net/manual/en/class.datetime.php#datetime.constants.rfc3339
     *
     * @return $this;
     */
    public function asDateTime()
    {
        $this->datetime = true;
        $this->date = false;

        return $this;
    }

    /**
     * @param int $numChars
     *
     * @return $this
     */
    public function minLength($numChars)
    {
        $this->minLength = $numChars;

        return $this;
    }

    /**
     * @param int $numChars
     *
     * @return $this
     */
    public function maxLength($numChars)
    {
        $this->maxLength = $numChars;

        return $this;
    }
}