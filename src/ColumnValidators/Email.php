<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates whether the column value is a "valid" e-mail address.
 *
 * The validation is performed against the addr-spec syntax in RFC 822. However, comments, whitespace folding, and
 * dotless domain names are not supported, and thus will be rejected.
 */
class Email implements ColumnValidator
{
    /**
     * @param bool $unicode Accepts Unicode characters in the local part
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     */
    public function __construct(
        public readonly bool $unicode = false,
        public readonly string $message = '%s: is invalid e-mail address',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        $options = $this->unicode ? FILTER_FLAG_EMAIL_UNICODE : 0;

        if (filter_var($propertyValue, FILTER_VALIDATE_EMAIL, $options) === false) {
            throw new EntityValidationException([$this->message, $propertyName], $propertyName, $entity);
        }
    }
}