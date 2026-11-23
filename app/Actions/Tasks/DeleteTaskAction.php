<?php

declare(strict_types=1);

namespace App\Actions\Tasks;

use App\Models\Task;

final class DeleteTaskAction
{
    public function handle(Task $task): void
    {
        $task->delete();
    }
}
