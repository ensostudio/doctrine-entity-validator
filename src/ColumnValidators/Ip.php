<?php

namespace EnsoStudio\Doctrine\ORM\ColumnValidators;

use Attribute;
use EnsoStudio\Doctrine\ORM\EntityValidationException;

/**
 * Validates column value as IP address.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Ip implements ColumnValidator
{
    /**
     * @param bool $ipv4 Allow IPv4 address
     * @param bool $ipv6 Allow IPv6 address
     * @param bool $noResRange Deny reserved addresses. These are the ranges that are marked as Reserved-By-Protocol in
     *     RFC 6890. Which for IPv4 corresponds to the following ranges: `0.0.0.0/8`, `169.254.0.0/16`, `127.0.0.0/8`,
     *     `240.0.0.0/4`. And for IPv6 corresponds to the following ranges: `::1/128`, `::/128`, `::FFFF:0:0/96`,
     *     `FE80::/10`.
     * @param bool $noPrivRange Deny private addresses. These are IPv4 addresses which are in the following ranges:
     *     `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`. These are IPv6 addresses starting with FD or FC.
     * @param string $message The template of error message in format `sprintf()`
     * @param bool $onPersist If true, validates column on persist entity
     * @param bool $onUpdate If true, validates column on update entity
     */
    public function __construct(
        public readonly bool $ipv4 = false,
        public readonly bool $ipv6 = false,
        public readonly bool $noResRange = false,
        public readonly bool $noPrivRange = false,
        public readonly string $message = '%s: is invalid IP address',
        public readonly bool $onPersist = true,
        public readonly bool $onUpdate = true
    ) {
    }

    public function validate(mixed $propertyValue, string $propertyName, object $entity): void
    {
        $options = 0;
        if ($this->ipv4) {
            $options = $options| FILTER_FLAG_IPV4;
        }
        if ($this->ipv6) {
            $options = $options | FILTER_FLAG_IPV6;
        }
        if ($this->noResRange) {
            $options = $options | FILTER_FLAG_NO_RES_RANGE;
        }
        if ($this->noPrivRange) {
            $options = $options | FILTER_FLAG_NO_PRIV_RANGE;
        }

        if (filter_var($propertyValue, FILTER_VALIDATE_IP, $options) === false) {
            throw new EntityValidationException([$this->message, $propertyName], $propertyName, $entity);
        }
    }
}