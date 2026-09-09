<?php

namespace App\Listeners;

use App\Http\Controllers\NotificationController;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Arr;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\QuotaExceeded;
use Kreait\Firebase\Exception\Messaging\ServerError;

class HandleFailedFcmNotifications
{
    /**
     * Handle the FCM notification failure event.
     *
     * Attempts to extract the invalid token from the SendReport and remove it
     * from the notifiable's stored tokens. All other failures are logged for
     * monitoring without disrupting the notification queue.
     */
    public function handle(NotificationFailed $event): void
    {
        $report = Arr::get($event->data, 'report');

        if (! $report) {
            $this->logMissingReport($event);

            return;
        }

        // Build consistent context for all log entries
        $token = $this->extractToken($report);
        $error = $this->extractError($report);
        $context = $this->buildContext($event, $token, $error);

        // Invalid / expired token → remove it
        if ($this->isInvalidTokenError($error)) {
            logger()->info('FCM token expired or invalid – removing', $context);
            $this->removeToken($token, $event->notifiable);

            return;
        }

        // Server‑side errors → retry later (no token cleanup)
        if ($error instanceof ServerError) {
            logger()->warning('FCM server error – retry', $context);

            return;
        }

        // Rate limiting
        if ($error instanceof QuotaExceeded) {
            logger()->warning('FCM quota exceeded', $context);

            return;
        }

        // SSL / network issues – transient, no cleanup
        if ($this->isTransientNetworkError($error)) {
            logger()->warning('FCM transient network issue', $context);

            return;
        }

        // Unknown failure – log as error for investigation
        logger()->error('FCM notification failed unexpectedly', $context);
    }

    // ------------------------------------------------------------------
    // Helper methods
    // ------------------------------------------------------------------

    /**
     * Extract the FCM token string from a SendReport.
     */
    protected function extractToken($report): ?string
    {
        $target = method_exists($report, 'target') ? $report->target() : null;

        if ($target && method_exists($target, 'value')) {
            $token = $target->value();

            return \is_string($token) && $token !== '' ? $token : null;
        }

        return null;
    }

    /**
     * Extract a Throwable from a SendReport (may be null).
     */
    protected function extractError($report): ?\Throwable
    {
        if (method_exists($report, 'error')) {
            $error = $report->error();

            return $error instanceof \Throwable ? $error : null;
        }

        return null;
    }

    /**
     * Build a consistent log context array.
     */
    protected function buildContext(
        NotificationFailed $event,
        ?string $token,
        ?\Throwable $error
    ): array {
        return [
            'notification' => \get_class($event->notification),
            'notifiable' => \get_class($event->notifiable),
            'notifiable_id' => method_exists($event->notifiable, 'getKey')
                ? $event->notifiable->getKey()
                : null,
            'token' => $token,
            'error_class' => $error ? \get_class($error) : null,
            'error_message' => $error?->getMessage(),
            'channel' => $event->channel,
        ];
    }

    /**
     * Determine if the error means the token is permanently invalid.
     */
    protected function isInvalidTokenError(?\Throwable $error): bool
    {
        if (! $error) {
            return false;
        }

        // Kreait's dedicated exception for unregistered tokens
        if ($error instanceof NotFound) {
            return true;
        }

        // InvalidMessage with a token‑format complaint is also a dead token
        if ($error instanceof InvalidMessage) {
            $message = strtolower($error->getMessage());

            return str_contains($message, 'registration token')
                || str_contains($message, 'registration-token-not-registered');
        }

        // Fallback string‑based check for any other exception
        $message = strtolower($error->getMessage());

        return str_contains($message, 'not found')
            || str_contains($message, 'registration-token-not-registered')
            || str_contains($message, 'unregistered');
    }

    /**
     * Determine if the error is a transient network / SSL issue.
     */
    protected function isTransientNetworkError(?\Throwable $error): bool
    {
        if (! $error) {
            return false;
        }

        $message = strtolower($error->getMessage());

        return str_contains($message, 'timed out')
            || str_contains($message, 'could not resolve host')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'ssl certificate');
    }

    /**
     * Remove the invalid token from the notifiable.
     */
    protected function removeToken(?string $token, $notifiable): void
    {
        if (! $token) {
            return;
        }

        app(NotificationController::class)->deleteDeviceToken([$token], $notifiable);
    }

    /**
     * Log a warning when no report is attached to the event.
     */
    protected function logMissingReport(NotificationFailed $event): void
    {
        logger()->warning('FCM notification failed without SendReport', [
            'channel' => $event->channel,
            'notifiable' => \get_class($event->notifiable),
            'notification' => \get_class($event->notification),
            'data_keys' => array_keys($event->data),
        ]);
    }
}
