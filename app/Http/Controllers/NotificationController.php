<?php

namespace App\Http\Controllers;

use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Notifications\NotificationMarkedAsRead;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;
use NotificationChannels\Fcm\FcmChannel;

class NotificationController extends Controller
{
    protected $user;

    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $channels = Usr::getActiveNotificationChannels(auth()->user(), false, true, true, true);

        $data['is_notifiable'] = auth()->user()->is_notifiable['status'] ?? Usr::getDefaultIsNotifiableUserColumnValues()['status'];
        $data['email_channel_on'] = \in_array('mail', $channels);
        $data['sms_channel_on'] = \in_array('vonage', $channels);
        $data['push_channel_on'] = \in_array(FcmChannel::class, $channels);

        return view('pages.notifications.index', $data);
    }

    /**
     * Update user notifications status & channels
     */
    public function update_is_notifiable(Request $request)
    {
        $user = auth()->user();

        $data = [
            'is_notifiable' => [
                'status' => $request->boolean('status'),
                'on_email_channel' => $request->boolean('on_email_channel'),
                'on_sms_channel' => $request->boolean('on_sms_channel'),
                'on_push_channel' => $request->boolean('on_push_channel'),
            ],
        ];

        $user->update($data);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->user->deleteNotification($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Mark the notification as read.
     */
    public function mark_as_read(string $id, $flash = true)
    {
        $notification = auth()->user()->unreadNotifications()->where('id', $id)->first();

        if ($notification === null)
            return back()->with('flash_error', 'Notification not found.');

        $notification->markAsRead();
        auth()->user()->notify(new NotificationMarkedAsRead($id));

        return $flash ? back()->with('flash_success', __('msg.update_ok')) : true;
    }

    /**
     * Mark all user's notifications as read.
     */
    public function mark_all_read(Request $request)
    {
        $request->user()->unreadNotifications()->get()->each(function ($n) {
            $n->markAsRead();
        });

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Update the user's web push subscription/device token.
     */
    public function update_firebase_device_token(Request $request)
    {
        $user = auth()->user();

        // DELETE request; remove token
        if ($request->isMethod('delete')) {
            $this->delete_device_token([$request->token]);
            return Usr::jsonUpdateOk();
        }

        $incoming_token = $request->token;
        $current_tokens = $user->firebase_device_tokens ?? [];

        if (!\in_array($incoming_token, $current_tokens, true)) {
            $current_tokens[] = $incoming_token;

            $user->update([
                'firebase_device_tokens' => $current_tokens,
            ]);
        }

        return Usr::jsonUpdateOk();
    }

    /**
     * Delete the user's web push subscription/device token.
     */
    public function delete_device_token(array $tokensToRemove)
    {
        $user = auth()->user();

        $currentTokens = $user->firebase_device_tokens ?? [];
        $newTokens = array_values(array_diff($currentTokens, $tokensToRemove));

        return $user->update([
            'firebase_device_tokens' => $newTokens,
        ]);
    }
}