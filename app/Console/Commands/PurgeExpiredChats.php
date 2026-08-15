<?php

namespace App\Console\Commands;

use App\Services\StudyGroupChat;
use Illuminate\Console\Command;

class PurgeExpiredChats extends Command
{
    protected $signature = 'stuhive:purge-expired-chats';

    protected $description = 'Delete study group chat messages whose chat window has closed';

    public function handle(StudyGroupChat $chat): int
    {
        $deleted = $chat->purgeExpired();

        $this->info("Purged {$deleted} expired study group ".str('message')->plural($deleted).'.');

        return self::SUCCESS;
    }
}
