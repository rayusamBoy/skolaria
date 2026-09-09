<?php

use App\User;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;

return [

    'user_model' => User::class,

    'message_model' => Message::class,

    'participant_model' => Participant::class,

    'thread_model' => Thread::class,

    /**
     * Define custom database table names - without prefixes.
     */
    'messages_table' => null,

    'participants_table' => null,

    'threads_table' => null,
];
