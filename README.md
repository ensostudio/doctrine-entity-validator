## Entity validator for Doctrine ORM 3

By default, entity validation based on attached `\Doctrine\ORM\Mapping\Column` attributes.

Also, you can add custom validators by `EntityValidator::addValidator()`.

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