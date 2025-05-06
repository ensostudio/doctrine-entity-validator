<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Attribute;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates whether the URL name is valid according to RFC 2396.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Url implements ColumnValidator
{
    /**
     * @param bool $pathRequired Requires the URL to contain a path part
     * @param bool $queryRequired Requires the URL to contain a query part
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     */
    public function __construct(
        public readonly bool $pathRequired = false,
        public readonly bool $queryRequired = false,
        public readonly string $message = '%s: is invalid URL',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        $options = 0;
        if ($this->pathRequired) {
            $options = $options | FILTER_FLAG_PATH_REQUIRED;
        }
        if ($this->queryRequired) {
            $options = $options | FILTER_FLAG_QUERY_REQUIRED;
        }

        if (filter_var($propertyValue, FILTER_VALIDATE_URL, $options) === false) {
            throw new EntityValidationException([$this->message, $propertyName], $propertyName, $entity);
        }
    }
}