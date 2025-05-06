<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Attribute;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates whether the column value is greater than (or equal to) range value.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Greater implements ColumnValidator
{
    /**
     * @param float|int $minRange The minimum value
     * @param bool $strict If true, exclude `$minRange` value at check
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     */
    public function __construct(
        public readonly float|int $minRange,
        public readonly bool $strict = false,
        public readonly string $message = '%s: is less than %d',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        if ($this->strict ? $this->minRange >= $propertyValue : $this->minRange > $propertyValue) {
            throw new EntityValidationException(
                [$this->message, $propertyName, $this->minRange],
                $propertyName,
                $entity
            );
        }
    }
}