<?php

declare(strict_types=1);

namespace App\Exceptions;

final class TaskReviewerRequiredException extends DomainException
{
    public function __construct(string $message = 'A reviewer must be assigned before moving a task to In Review.')
    {
        parent::__construct($message, 'TASK_REVIEWER_REQUIRED', 422);
    }
}
