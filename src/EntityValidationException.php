<?php

namespace EnsoStudio\Doctrine\ORM;

use RuntimeException;
use Throwable;

class EntityValidationException extends RuntimeException
{
    /**
     * @var scalar[]
     */
    private array $sourceMessage;

    private string $targetProperty;

    private object $targetEntity;

    /**
     * @param scalar[] $message An array where first element is message template in format `sprintf()` and other
     *     elements are values for template placeholders
     * @param string $targetProperty The name of target property (column)
     * @param object $targetEntity The target entity
     * @param int $code The exception code
     * @param Throwable|null $previous The previous throwable used for the exception chaining
     */
    public function __construct(
        array $message,
        string $targetProperty,
        object $targetEntity,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->sourceMessage = $message;
        $this->targetProperty = $targetProperty;
        $this->targetEntity = $targetEntity;

        parent::__construct(sprintf(...$message), $code, $previous);
    }

    /**
     * Returns the source message.
     *
     * Useful for translates error message.
     *
     * @return scalar[]
     */
    public function getSourceMessage(): array
    {
        return $this->sourceMessage;
    }

    public function getTargetProperty(): string
    {
        return $this->targetProperty;
    }

    public function getTargetEntity(): object
    {
        return $this->targetEntity;
    }
}
