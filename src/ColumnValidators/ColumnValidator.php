<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Attribute;

/**
 * Attribute to validate property/column value of entity.
 *
 * @property-read bool $onPersist If true, validates column on persist/insert entity
 * @property-read bool $onUpdate If true, validates column on update entity
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
interface ColumnValidator
{
    /**
     * Validates property value.
     *
     * @param mixed $propertyValue The property value
     * @param string $propertyName The property name
     * @param object $entity The target entity
     * @throws \EnsoStudio\Doctrine\ORM\EntityValidationException If validation failed
     */
    public function validate(mixed $propertyValue, string $propertyName, object $entity): void;
}
