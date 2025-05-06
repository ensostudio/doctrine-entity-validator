<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates column value against the regular expression.
 */
class Regexp implements ColumnValidator
{
    /**
     * @param string $pattern The regexp pattern to match
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     */
    public function __construct(
        public readonly string $pattern,
        public readonly string $message = '%s: invalid format of value',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        if (preg_match($this->pattern, $propertyValue) !== 1) {
            throw new EntityValidationException([$this->message, $propertyName], $propertyName, $entity);
        }
    }
}
