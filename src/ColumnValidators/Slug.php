<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Attribute;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates column value against {@see https://en.wikipedia.org/wiki/Clean_URL#Slug SLUG} regular expression.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Slug implements ColumnValidator
{
    /**
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     */
    public function __construct(
        public readonly string $message = '%s: is invalid SLUG (must contains only a-z, 0-9, _ and -)',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        if (preg_match('/^[a-z\d][-_a-z\d]*[a-z\d]$/i', $propertyValue) !== 1) {
            throw new EntityValidationException([$this->message, $propertyName], $propertyName, $entity);
        }
    }
}