<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Attribute;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates column value using `filter_var()`.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Filter implements ColumnValidator
{
    public const FILTERS = [
        'int' => FILTER_VALIDATE_INT,
        'float' => FILTER_VALIDATE_FLOAT,
        'regexp' => FILTER_VALIDATE_REGEXP,
        'url' => FILTER_VALIDATE_URL,
        'domain' => FILTER_VALIDATE_DOMAIN,
        'email' => FILTER_VALIDATE_EMAIL,
        'ip' => FILTER_VALIDATE_IP,
        'mac' => FILTER_VALIDATE_MAC
    ];

    /**
     * @param int $filter The ID of the validation filter to use, `FILTER_VALIDATE_...` constant
     * @param array<string, mixed> $options The options of validation filter
     * @param int $flags The bitwise disjunction of filter flags
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     * @see self::FILTERS Get filter ID by short name
     */
    public function __construct(
        public readonly int $filter,
        public readonly array $options = [],
        public readonly int $flags = 0,
        public readonly string $message = '%s: is invalid value',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        $options = ['options' => $this->options, 'flags' => $this->flags];

        if (filter_var($propertyValue, $this->filter, $options) === false) {
            throw new EntityValidationException([$this->message, $propertyName], $propertyName, $entity);
        }
    }
}