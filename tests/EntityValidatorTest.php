<?php

namespace EnsoStudio\Doctrine\ORM;

use PHPUnit\Framework\TestCase;

class EntityValidatorTest extends TestCase
{
    private TestEntity $entity;
    private EntityValidator $validator;

    protected function setUp(): void
    {
        $this->entity = new TestEntity();
        $this->entity->setBarcode('1234567890');
        $this->entity->setName('Test product');
        $this->entity->setPrice(12.50);
        $this->entity->setEnumValue(TestEnum::Second);

        $this->validator = new EntityValidator($this->entity);
    }

    public function testValidate(): void
    {
        $this->assertNull($this->validator->validate());

        $this->entity->setId(1);
        $this->assertNull($this->validator->validate(true));
    }

    public function testInvalidEntity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ArrayObject is not entity class');

        $invalidEntity = new \ArrayObject();
        $validator = new EntityValidator($invalidEntity);
    }

    public function testAddValidator(): void
    {
        $this->assertIsObject(
            $this->validator->addValidator('price', function ($propertyValue, $propertyName, $entity) {})
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined property invalidProperty in class EnsoStudio\Doctrine\ORM\TestEntity');
        $this->validator->addValidator('invalidProperty', function () {});
    }

    public function testValidateNumericLessZero(): void
    {
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage('price is less than zero');

        $this->entity->setPrice(-10);
        $this->validator->validate();
    }

    public function testValidateNumericVeryBig(): void
    {
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage('price is very big (max: 10 digits)');

        $this->entity->setPrice(100000000000.12);
        $this->validator->validate();
    }

    public function testValidateStringWithWrongFixedLength(): void
    {
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage('barcode has wrong length (must be 10 chars)');

        $this->entity->setBarcode('123');
        $this->validator->validate();
    }

    public function testValidateStringWithWrongLength(): void
    {
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage('name is too long (max: 15 chars)');

        $this->entity->setName('Very long product name');
        $this->validator->validate();
    }

    public function testValidateEnumInvalidValue(): void
    {
        $this->expectException(EntityValidationException::class);
        $this->expectExceptionMessage('enumValue have invalid enum value');

        $this->entity->setEnumValue('x');
        $this->validator->validate();
    }
}
