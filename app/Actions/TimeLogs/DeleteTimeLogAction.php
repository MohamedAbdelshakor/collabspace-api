<?php

declare(strict_types=1);

namespace App\Actions\TimeLogs;

use App\Models\TimeLog;

final class DeleteTimeLogAction
{
    public function handle(TimeLog $timeLog): void
    {
        $timeLog->delete();
    }
}
