<?php

declare(strict_types=1);

namespace App\Actions\Chats;

use App\Events\Chats\MessageSent;
use App\Jobs\FetchLinkPreviewJob;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SendMessageAction
{
    public function handle(Conversation $conversation, User $sender, string $body, ?Message $parent = null): Message
    {
        return DB::transaction(function () use ($conversation, $sender, $body, $parent): Message {
            /** @var Message $message */
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $sender->id,
                'parent_id' => $parent?->id,
                'body' => $body,
            ]);

            if ($parent) {
                $parent->increment('replies_count');
            }

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            // Sender automatically marks their own message as read
            $conversation->participants()->updateExistingPivot($sender->id, [
                'last_read_message_id' => $message->id,
            ]);

            // Broadcast real-time message via Reverb
            MessageSent::dispatch($message);

            // Detect URLs and dispatch SSRF-safe link preview scraper job
            if (preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $body, $matches)) {
                $uniqueUrls = array_unique($matches[0]);
                foreach ($uniqueUrls as $url) {
                    FetchLinkPreviewJob::dispatch($url);
                }
            }

            return $message->load(['user', 'parent']);
        });
    }
}
