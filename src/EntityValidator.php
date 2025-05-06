<?php

namespace EnsoStudio\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;
use ReflectionObject;

/**
 * The validator for Doctrine ORM entities.
 *
 * By default, entity validation based on attached `\Doctrine\ORM\Mapping\Column` attributes.
 * Also, you can {@see self::addValidator() add custom validators}.
 */
class EntityValidator
{
    private object $entity;

    private ReflectionObject $reflection;

    /**
     * @var array<string, callable[]> The custom property validators
     */
    private array $validators = [];

    /**
     * @param object $entity The entity instance
     * @throws InvalidArgumentException If `$entity` class don't have `\Doctrine\ORM\Mapping\Entity` attribute
     */
    public function __construct(object $entity)
    {
        $reflection = new ReflectionObject($entity);
        if (!$reflection->getAttributes(ORM\Entity::class)) {
            throw new InvalidArgumentException(get_class($entity) . ' is not entity class');
        }

        $this->entity = $entity;
        $this->reflection = $reflection;
    }

    /**
     * Adds custom validator for property (column).
     *
     * @param string $propertyName The name of entity property
     * @param callable $validator The callback to validate property value, callback syntax:
     *     `function (mixed $propertyValue, string $propertyName, object $entity): void`.
     *     If value is invalid, then callback MUST throw `EntityValidationException`.
     * @return $this
     * @throws InvalidArgumentException If property not defined
     */
    public function addValidator(string $propertyName, callable $validator): self
    {
        if (!$this->reflection->hasProperty($propertyName)) {
            throw new InvalidArgumentException(
                'Undefined property ' . $propertyName . ' in class ' . get_class($this->entity)
            );
        }
        $this->validators[$propertyName][] = $validator;

        return $this;
    }

    /**
     * @param bool $onUpdate If true, then is entity update, else is entity persist/insert
     * @throws EntityValidationException If property value is invalid
     */
    public function validate(bool $onUpdate = false): void
    {
        $properties = $this->reflection->getProperties(
            \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE
        );
        foreach ($properties as $property) {
            if ($property->isStatic() || $property->isReadOnly() || !$property->isDefault()) {
                continue;
            }

            $attributes = $property->getAttributes(ORM\Column::class);
            if (!$attributes) {
                continue;
            }
            /** @var ORM\Column $attribute */
            $attribute = $attributes[0]->newInstance();
            if (($onUpdate && !$attribute->updatable) || (!$onUpdate && !$attribute->insertable)) {
                continue;
            }

            $value = $property->isInitialized($this->entity) ? $property->getValue($this->entity) : null;

            // Ignore ID columns on persis/insert
            if (!$onUpdate && $value === null && $property->getAttributes(ORM\Id::class)) {
                continue;
            }

            $this->validateColumn($value, $property->getName(), $attribute);
        }
    }

    private function validateColumn(mixed $value, string $propertyName, ORM\Column $attribute): void
    {
        if ($value === null) {
            if (!$attribute->nullable && !isset($attribute->options['default'])) {
                throw new EntityValidationException(['%s is empty', $propertyName], $propertyName, $this->entity);
            }
            // Skip validation if column is empty and not required
            return;
        }

        switch ($attribute->type) {
            case Types::BIGINT:
            case Types::DECIMAL:
            case Types::FLOAT:
            case Types::INTEGER:
            case Types::SMALLFLOAT:
            case Types::SMALLINT:
                $this->validateNumericColumn($value, $propertyName, $attribute);
                break;

            case Types::ASCII_STRING:
            case Types::STRING:
            case Types::TEXT:
                $this->validateStringColumn($value, $propertyName, $attribute);
                break;

            case Types::ENUM:
                $this->validateEnumColumn($value, $propertyName, $attribute);
        }

        foreach ($this->validators[$propertyName] ?? [] as $validator) {
            $validator($value, $propertyName, $this->entity);
        }
    }

    private function validateNumericColumn(mixed $value, string $propertyName, ORM\Column $attribute): void
    {
        if (!empty($attribute->options['unsigned']) && $value < 0) {
            throw new EntityValidationException(['%s is less than zero', $propertyName], $propertyName, $this->entity);
        }

        if ($attribute->type == Types::DECIMAL && !empty($attribute->precision)) {
            $value = !empty($attribute->scale)
                ? number_format($value, $attribute->scale, '.', '')
                : (string) $value;
            if (strlen($value) > $attribute->precision) {
                throw new EntityValidationException(
                    ['%s is very big (max: %d digits)', $propertyName, $attribute->precision],
                    $propertyName,
                    $this->entity
                );
            }
        }
    }

    private function validateStringColumn(mixed $value, string $propertyName, ORM\Column $attribute): void
    {
        if (empty($attribute->length)) {
            return;
        }

        $isInvalid = false;

        if (!empty($attribute->options['fixed'])) {
            if ($attribute->type == Types::ASCII_STRING) {
                if (strlen($value) != $attribute->length) {
                    $isInvalid = true;
                }
            } elseif (mb_strlen($value, $attribute->options['charset'] ?? null) != $attribute->length) {
                $isInvalid = true;
            }
            if ($isInvalid) {
                throw new EntityValidationException(
                    ['%s has wrong length (must be %d chars)', $propertyName, $attribute->length],
                    $propertyName,
                    $this->entity
                );
            }
        }

        if ($attribute->type == Types::ASCII_STRING) {
            if (strlen($value) > $attribute->length) {
                $isInvalid = true;
            }
        } elseif (mb_strlen($value, $attribute->options['charset'] ?? null) > $attribute->length) {
            $isInvalid = true;
        }
        if ($isInvalid) {
            throw new EntityValidationException(
                ['%s is too long (max: %d chars)', $propertyName, $attribute->length],
                $propertyName,
                $this->entity
            );
        }
    }

    private function validateEnumColumn(mixed $value, string $propertyName, ORM\Column $attribute): void
    {
        if (empty($attribute->enumType)) {
            return;
        }

        $isInvalid = false;
        if (is_object($value)) {
            if (!is_a($value, $attribute->enumType)) {
                $isInvalid = true;
            }
        } else {
            $isEnumCase = in_array($value, array_column($attribute->enumType::cases(), 'name'));
            if (is_a($attribute->enumType, \BackedEnum::class, true)) {
                if ($attribute->enumType::tryFrom($value) === null && !$isEnumCase) {
                    $isInvalid = true;
                }
            } elseif (!$isEnumCase) {
                $isInvalid = true;
            }
        }
        if ($isInvalid) {
            throw new EntityValidationException(
                ['%s have invalid enum value', $propertyName],
                $propertyName,
                $this->entity
            );
        }
    }
}
