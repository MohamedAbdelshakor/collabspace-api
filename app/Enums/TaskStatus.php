<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To-do',
            self::InProgress => 'In Progress',
            self::InReview => 'In Review',
            self::Completed => 'Completed',
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }
}
