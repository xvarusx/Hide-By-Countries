<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\Utility;

class GeoLocationException extends \RuntimeException
{
    public static function fromApiError(string $message, ?\Throwable $previous = null): self
    {
        return new self('API error: ' . $message, 0, $previous);
    }

    public static function fromInvalidResponse(string $message, ?\Throwable $previous = null): self
    {
        return new self('Invalid response: ' . $message, 0, $previous);
    }
}
