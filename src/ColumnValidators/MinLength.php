<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Attribute;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates column value is long enough.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MinLength implements ColumnValidator
{
    /**
     * @param int $length The minimum length required
     * @param string|null $encoding The character encoding. If it's null, the internal character encoding value will be
     *     used.
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     */
    public function __construct(
        public readonly int $length,
        public readonly ?string $encoding = null,
        public readonly string $message = '%s: is less than %d characters',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        if ($this->length > mb_strlen($propertyValue, $this->encoding)) {
            throw new EntityValidationException([$this->message, $propertyName, $this->length], $propertyName, $entity);
        }
    }
}
