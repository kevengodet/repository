<?php

namespace Adagio\Repository\Exception;

final class NotFound extends \OutOfBoundsException
{
    /**
     *
     * @param string $identifier
     *
     * @return NotFound
     */
    static public function fromIdentifier($identifier)
    {
        return new self("No entity found for identifier '$identifier'.");
    }

    /**
     *
     * @param string $name
     * @param string $comparator
     * @param mixed $value
     *
     * @return NotFound
     */
    static public function fromProperty($name, $comparator, $value)
    {
        return new self("No entity found where: '$name' $comparator '$value'.");
    }
}
