<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;

final class UploadAvatarAction
{
    public function handle(User $user, UploadedFile $file): User
    {
        $user->clearMediaCollection('avatar');
        $media = $user->addMedia($file)->toMediaCollection('avatar');

        $user->update([
            'avatar_path' => $media->getUrl(),
        ]);

        return $user->refresh();
    }
}
