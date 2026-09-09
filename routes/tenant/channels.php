<?php

declare(strict_types=1);

use App\User;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes();
Broadcast::channel('messages.{thread_id}.{tenant_key_value}', fn (User $user, $thread_id) => Participant::where('thread_id', $thread_id)->where('user_id', $user->id)->exists());
