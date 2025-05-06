## Entity validator for Doctrine ORM 3

By default, entity validation based on attached `\Doctrine\ORM\Mapping\Column` attributes.

Also, you can add custom validators by `EntityValidator::addValidator()`.

Validator skip validation:
- If `Column` attribute declared as not `updatable` and/or `insertable
- Validation on persist/insert and property have `\Doctrine\ORM\Mapping\Id` attribute

Validator checks:
- If property value is null (or not defined), but `Column` attribute not declared as `nullable` or don't have default value (`options: ['default' => '...']`)
- If `Column` attribute have **numeric** type (integer, float, decimal and etc.):
  - If defined `unsigned` option, then property value must be more than zero
  - If type `decimal` and defined `precision`, then check size of value
- If `Column` attribute have **string** type (sting, text and etc.):
  - If defined `fixed` option and `length`, then check string length
  - If defined only `length`, then check string length
- If `Column` attribute have **enum** type:
  - If defined `enumType`, then check is proprerty value is declared in enum class

## Example

Validates `Product` entity before insert/update data:

```php
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EnsoStudio\Doctrine\ORM\EntityValidator;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
#[ORM\HasLifecycleCallbacks]
class Product
{
    ...

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\PrePersist]
    public function beforeInsert(): void
    {
        $validator = new EntityValidator($this);
        $validator->addValidator(
            'name', 
            static function (mixed $propertyValue, string $propertyName, object $entity) {
                if (mb_strlen($propertyValue) < 3) {
                    throw new EntityValidationException(
                        ['% less than 3 chars', $propertyName],
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