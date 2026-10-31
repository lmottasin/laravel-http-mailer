<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Exceptions;

use InvalidArgumentException;

final class MissingApiKeyException extends InvalidArgumentException
{
    public static function forTransport(string $transport, string $environmentVariable): self
    {
        return new self(
            "No API key configured for the [{$transport}] mail transport. "
            ."Set {$environmentVariable} in your .env file, or a 'key' on the mailer in config/mail.php."
        );
    }
}
