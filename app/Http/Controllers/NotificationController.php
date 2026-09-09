<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Helpers\Usr;
use App\Notifications\NotificationMarkedAsRead;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use NotificationChannels\Fcm\FcmChannel;

class NotificationController extends Controller
{
    protected UserRepo $user;

    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }

    /**
     * Display notification preferences and channels.
     */
    public function index(): View
    {
        $channels = Usr::getActiveNotificationChannels(auth()->user(), false, true, true, true);

        $data['notification_preferences'] = auth()->user()->notification_preferences['status'] ?? Usr::getDefaultNotificationPreferences()['status'];
        $data['email_channel_on'] = \in_array('mail', $channels);
        $data['sms_channel_on'] = \in_array('vonage', $channels);
        $data['push_channel_on'] = \in_array(FcmChannel::class, $channels);

        return view('pages.notifications.index', $data);
    }

    /**
     * Update user notification preferences.
     */
    public function update_notification_preferences(Request $request): RedirectResponse
    {
        auth()->user()->update([
            'notification_preferences' => [
                'status' => $request->boolean('status'),
                'on_email_channel' => $request->boolean('on_email_channel'),
                'on_sms_channel' => $request->boolean('on_sms_channel'),
                'on_push_channel' => $request->boolean('on_push_channel'),
            ],
        ]);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): RedirectResponse
    {
        $this->user->deleteNotification($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Mark a single notification as read.
     *
     * @param  bool  $flash  If false, returns true instead of a redirect (used internally)
     */
    public function mark_as_read(string $id, bool $flash = true): RedirectResponse|bool
    {
        $notification = auth()->user()->unreadNotifications()->where('id', $id)->first();

        if ($notification === null) {
            return back()->with('flash_error', 'Notification not found.');
        }

        $notification->markAsRead();
        auth()->user()->notify(new NotificationMarkedAsRead($id));

        return $flash ? back()->with('flash_success', __('msg.update_ok')) : true;
    }

    /**
     * Mark all unread notifications as read.
     */
    public function mark_all_read(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->each->markAsRead();

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Update or delete a Firebase device token for push notifications.
     */
    #[SkipPermission]
    public function update_firebase_device_token(Request $request): JsonResponse
    {
        $user = auth()->user();

        // DELETE request: remove token(s)
        if ($request->isMethod('DELETE')) {
            $this->deleteDeviceToken([$request->token], $user);

            return Usr::jsonUpdateOk();
        }

        $incoming_token = $request->token;
        $current_tokens = $user->firebase_device_tokens ?? [];

        if (! \in_array($incoming_token, $current_tokens, true)) {
            $current_tokens[] = $incoming_token;
            $user->update(['firebase_device_tokens' => $current_tokens]);
        }

        return Usr::jsonUpdateOk();
    }

    /**
     * Delete specific device tokens from the authenticated user.
     */
    #[SkipPermission] // Skip permission generation for this method
    public function deleteDeviceToken(array $tokens_to_remove, $user): int
    {
        $current_tokens = $user->firebase_device_tokens ?? [];
        $new_tokens = array_values(array_diff($current_tokens, $tokens_to_remove));

        return $user->update(['firebase_device_tokens' => $new_tokens]);
    }
}
