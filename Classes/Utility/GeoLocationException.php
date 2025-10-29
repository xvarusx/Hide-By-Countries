<?php
declare(strict_types=1);

namespace Oussema\HideByCountries\Utility;

class GeoLocationException extends \RuntimeException
{
    public static function fromApiError(string $message, int $code = 0, \Throwable $previous = null): self
    {
        return new self('GeoLocation API Error: ' . $message, $code, $previous);
    }

    public static function fromInvalidResponse(string $message, int $code = 0, \Throwable $previous = null): self
    {
        return new self('Invalid API Response: ' . $message, $code, $previous);
    }
}