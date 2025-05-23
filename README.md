## Entity validator for Doctrine ORM 3

[![Latest Stable Version](https://img.shields.io/packagist/v/ensostudio/doctrine-entity-validator.svg)](https://packagist.org/packages/ensostudio/doctrine-entity-validator)
[![Total Downloads](https://img.shields.io/packagist/dt/ensostudio/doctrine-entity-validator.svg)](https://packagist.org/packages/ensostudio/doctrine-entity-validator)

By default, entity validation based on attached `\Doctrine\ORM\Mapping\Column` attributes and attributes inherited
[ColumnValidator](src/ColumnValidators/ColumnValidator.php) interface.

Also, you can add custom validators by `EntityValidator::addValidator()` or create instance of [ColumnValidator](src/ColumnValidators/ColumnValidator.php) interface.

Validator skip validation:
- If `Column` attribute declared as not `updatable` and/or `insertable`
- Validation on persist/insert and property have `\Doctrine\ORM\Mapping\Id` attribute

Validator checks by column type:
- If property value is null (or not defined), but `Column` attribute not declared as `nullable` or/and don't have default value (`options: ['default' => '...']`)
- If `Column` attribute have **numeric** type (integer, float, decimal and etc.):
  - If defined `unsigned` option, then property value must be more than zero
  - If type `decimal` and defined `precision`, then check size of value
- If `Column` attribute have **string** type (sting, text and etc.):
  - If defined `fixed` option and `length`, then check string length
  - If defined only `length`, then check string length
- If `Column` attribute have **enum** type:
  - If defined `enumType`, then check is proprerty value is declared in enum class

`ColumnValidator` attributes:
- [MinLength](src/ColumnValidators/MinLength.php)
- [Greater](src/ColumnValidators/Greater.php)
- [Number](src/ColumnValidators/Number.php)
- [Regexp](src/ColumnValidators/Regexp.php)
- [Type](src/ColumnValidators/Type.php)
- [Filter](src/ColumnValidators/Filter.php)
- [Slug](src/ColumnValidators/Slug.php)
- [Ip](src/ColumnValidators/Ip.php)
- [Url](src/ColumnValidators/Url.php)
- [Email](src/ColumnValidators/Email.php)

## Events

The `postValidate`(`EntityValidator::EVENT_POST_VALIDATE`) event triggers after successful validation, the syntax of
listener method: `function (LifecycleEventArgs $args): void`.

## Examples

Validates `Product` entity before insert/update data:

```php
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use \EnsoStudio\Doctrine\ORM\ColumnValidators;
use EnsoStudio\Doctrine\ORM\EntityValidator;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
#[ORM\HasLifecycleCallbacks]
class Product
{
    ...

    #[ORM\Column(type: Types::STRING, length: 200)]
    #[ColumnValidators\MinLength(2)]
    #[ColumnValidators\Slug]
    private string $slug;

    #[ORM\Column(type: Types::STRING, length: 150)]
    #[ColumnValidators\Type('print')]
    private string $name;

    #[ORM\PrePersist]
    public function beforeInsert(): void
    {
        $validator = new EntityValidator($this);
        // Callback same to ColumnValidators\MinLength(3)
        $validator->addValidator(
            'name', 
            static function (string $propertyValue, string $propertyName, object $entity) {
                if (mb_strlen($propertyValue) < 3) {
                    throw new EntityValidationException(
                        ['% less than 3 characters', $propertyName],
                        $propertyName,
                        $entity
                    );
                }
            }
        );
        $validator->validate();
    }

    #[ORM\PreUpdate]
    public function beforeUpdate(): void
    {
        $validator = new EntityValidator($this);
        ...
        $validator->validate(true);
    }
}
```

Or you can use `EntityValidationSubscriber` to validates all entities:

```php
use Doctrine\ORM\EntityManager;
use EnsoStudio\Doctrine\ORM\EntityValidationSubscriber;

...
$entityManager = new EntityManager($connection, $config);
$entityManager->getEventManager()
    ->addEventSubscriber(new EntityValidationSubscriber(true));
```

## Requirements

- PHP >= 8.1 (with `mbstring` extension)
- doctrine/orm >= 3.3

## Installation

If you do not have Composer, you may install it by following the instructions at
[getcomposer.org](https://getcomposer.org/doc/00-intro.md#introduction).

You can then install this library using the following command:

```shell
composer require ensostudio/doctrine-entity-validator
```