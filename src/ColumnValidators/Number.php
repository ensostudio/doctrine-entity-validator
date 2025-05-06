<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Doctrine\ORM\Mapping\Column;
use Doctrine\DBAL\Types\Types;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates whether the column value is an integer or a float/decimal.
 */
class Number implements ColumnValidator
{
    /**
     * @param float|int|null $minRange Value is only valid if it is greater than or equal to provided value
     * @param float|int|null $maxRange Value is only valid if it is less than or equal to provided value
     * @param bool $allowOctal Allow integers in octal notation (`0[0-7]+`), only for integer values
     * @param bool $allowHex Allow integers in hexadecimal notation (`0x[0-9a-fA-F]+`), only for integer values
     * @param bool $allowThousand Accept commas (`,`), which usually represent thousand separator, only for float values
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     */
    public function __construct(
        public readonly float|int|null $minRange = null,
        public readonly float|int|null $maxRange = null,
        public readonly bool $allowOctal = false,
        public readonly bool $allowHex = false,
        public readonly bool $allowThousand = false,
        public readonly string $message = '%s: is invalid value',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        $filter = null;
        $options = ['options' => [], 'flags' => 0];
        if ($this->minRange !== null) {
            $options['options']['min_range'] = $this->minRange;
        }
        if ($this->maxRange !== null) {
            $options['options']['max_range'] = $this->maxRange;
        }
        if ($this->allowOctal) {
            $options['flags'] = $options['flags'] | FILTER_FLAG_ALLOW_OCTAL;
            $filter = FILTER_VALIDATE_INT;
        }
        if ($this->allowHex) {
            $options['flags'] = $options['flags'] | FILTER_FLAG_ALLOW_HEX;
            $filter = FILTER_VALIDATE_INT;
        }
        if ($this->allowThousand) {
            $options['flags'] = $options['flags'] | FILTER_FLAG_ALLOW_THOUSAND;
            $filter = FILTER_VALIDATE_FLOAT;
        }

        if ($filter === null) {
            // Detects filter by column type
            $attributes = (new \ReflectionProperty($entity, $propertyName))->getAttributes(Column::class);
            if (!$attributes) {
                return;
            }
            /** @var Column $attribute */
            $attribute = $attributes[0]->newInstance();
            $filter = in_array($attribute->type, [Types::BIGINT, Types::INTEGER, Types::SMALLINT])
                ? FILTER_VALIDATE_INT
                : FILTER_VALIDATE_FLOAT;
        }

        if (filter_var($propertyValue, $filter, $options) === false) {
            throw new EntityValidationException([$this->message, $propertyName], $propertyName, $entity);
        }
    }
}