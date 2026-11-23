<?php

declare(strict_types=1);

namespace App\Actions\TimeLogs;

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\CarbonInterface;

final class CreateTimeLogAction
{
    public function handle(Task $task, User $user, int $minutes, CarbonInterface|string $loggedOn, ?string $note = null): TimeLog
    {
        /** @var TimeLog $log */
        $log = TimeLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'minutes' => $minutes,
            'logged_on' => $loggedOn,
            'note' => $note,
        ]);

        return $log->load('user');
    }
}
