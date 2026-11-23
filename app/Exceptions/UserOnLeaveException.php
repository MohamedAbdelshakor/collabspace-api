<?php

declare(strict_types=1);

namespace App\Exceptions;

final class UserOnLeaveException extends DomainException
{
    public function __construct(string $message = 'The specified user is currently on leave.')
    {
        parent::__construct($message, 'USER_ON_LEAVE', 422);
    }
}
