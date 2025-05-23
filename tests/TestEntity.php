<?php

namespace EnsoStudio\Doctrine\ORM;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class TestEntity
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER, unique: true, insertable: false, updatable: false, options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 10, options: ['fixed' => true])]
    private string $barcode;

    #[ORM\Column(type: Types::STRING, length: 15)]
    private string $name;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['unsigned' => true])]
    private float $price;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['default' => null])]
    private ?string $description = null;

    #[ORM\Column(type: Types::ENUM, enumType: TestEnum::class)]
    private $enumValue;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, length: 20)]
    private array $arrayValue;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getBarcode(): float
    {
        return $this->barcode;
    }

    public function setBarcode(float $barcode): void
    {
        $this->barcode = $barcode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getEnumValue(): TestEnum
    {
        return $this->enumValue;
    }

    public function setEnumValue($enumValue): void
    {
        $this->enumValue = $enumValue;
    }

    public function getArrayValue(): array
    {
        return $this->arrayValue;
    }

    public function setArrayValue(array $arrayValue): void
    {
        $this->arrayValue = $arrayValue;
    }
}