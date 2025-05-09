<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Attribute;
use EnsoStudio\Doctrine\ORM\EntityValidationException;
use InvalidArgumentException;

/**
 * Validates column value using {@link https://www.php.net/manual/en/ref.ctype.php Ctype function}.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Type implements ColumnValidator
{
    public const TYPES = [
        'alnum' => 'ctype_alnum',
        'alpha' => 'ctype_alpha',
        'cntrl' => 'ctype_cntrl',
        'digit' => 'ctype_digit',
        'graph' => 'ctype_graph',
        'lower' => 'ctype_lower',
        'print' => 'ctype_print',
        'punct' => 'ctype_punct',
        'space' => 'ctype_space',
        'upper' => 'ctype_upper',
        'xdigit' => 'ctype_xdigit'
    ];

    /**
     * @param string $type The type of tested value, value of `self::TYPES` constant
     * @param bool $inverse If true, inverse validation result i.e. throws exception if is value of given type
     * @param string $message The template of error message in format `sprintf()`
     * @param string $messageInverse The template of error message in format `sprintf()` (uses when `$inverse` is true)
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     * @throws InvalidArgumentException If `$type` is invalid
     * @see self::TYPES Get function name by short name
     */
    public function __construct(
        public readonly string $type,
        public readonly bool $inverse = false,
        public readonly string $message = '%s: invalid type (not %s)',
        public readonly string $messageInverse = '%s: invalid type (%s)',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
        if (!isset(self::TYPES[$this->type])) {
            throw new InvalidArgumentException('Invalid type: ' . $this->type);
        }
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        if ($this->inverse) {
            if (call_user_func(self::TYPES[$this->type], (string) $propertyValue)) {
                throw new EntityValidationException(
                    [$this->messageInverse, $propertyName, $this->type],
                    $propertyName,
                    $entity
                );
            }
        } elseif (!call_user_func(self::TYPES[$this->type], (string) $propertyValue)) {
            throw new EntityValidationException([$this->message, $propertyName, $this->type], $propertyName, $entity);
        }
    }
}