<?php

namespace App\Models;

use Cmgmyr\Messenger\Models\Message as OriginalMessage;
use Cmgmyr\Messenger\Models\Models;
use Illuminate\Support\Facades\DB;

class Message extends OriginalMessage
{
    /**
     * Delete a message.
     *
     * @param int $messageId
     *
     * @return mixed
     */
    public function deleteMessage(int $messageId)
    {
        return OriginalMessage::where('id', $messageId)->delete();
    }

    public function deleteMessageForever(int $messageId)
    {
        return DB::table($this->table)->where("id", $messageId)->delete();
    }

    public function restoreDeletedMessage(int $messageId)
    {
        return OriginalMessage::where('id', $messageId)->restore();
    }

    public function updateMessage(int $messageId, array $data)
    {
        return OriginalMessage::where('id', $messageId)->update($data);
    }

    public function deletor()
    {
        return $this->belongsTo(Models::user(), 'deleted_by', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Models::user());
    }
}
