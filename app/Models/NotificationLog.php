<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationLog extends Model
{
    protected $table = 'notification_logs';

    protected $fillable = [
        'idempotency_key',
        'event_type',
        'notifiable_type',
        'notifiable_id',
        'channel',
        'recipient_email',
        'recipient_mobile',
        'subject',
        'message',
        'status',
        'provider_response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    /**
     * The owning notifiable entity (polymorphic).
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    public function scopeForEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sent', 'logged']);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Determine if a Throwable represents a genuine database unique constraint collision.
     */
    public static function isUniqueConstraintViolation(\Throwable $e): bool
    {
        if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
            return true;
        }

        $previous = $e->getPrevious();
        $message = $e->getMessage() . ($previous ? ' ' . $previous->getMessage() : '');

        if ($e instanceof \Illuminate\Database\QueryException || $e instanceof \PDOException) {
            $errorCode = (int)($e->errorInfo[1] ?? ($previous instanceof \PDOException ? ($previous->errorInfo[1] ?? $previous->getCode()) : $e->getCode()));
            $sqlState = (string)($e->errorInfo[0] ?? ($previous instanceof \PDOException ? ($previous->errorInfo[0] ?? '') : ''));

            // 1. MySQL / MariaDB Duplicate Entry (strictly error code 1062)
            if ($errorCode === 1062) {
                return true;
            }

            // 2. PostgreSQL Unique Violation (SQLSTATE 23505)
            if ($sqlState === '23505') {
                return true;
            }

            // 3. SQL Server Unique Violation (Error 2601 or 2627)
            if (in_array($errorCode, [2601, 2627], true)) {
                return true;
            }

            // 4. SQLite Unique / Primary Key Constraint Violation
            if (str_contains($message, 'UNIQUE constraint failed') ||
                str_contains($message, 'PRIMARY KEY must be unique') ||
                str_contains($message, 'columns are not unique')) {
                return true;
            }

            // 5. Unmistakable Duplicate Entry text pattern fallback
            if (str_contains($message, 'Duplicate entry')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Atomically claim a notification slot for sending.
     *
     * Rules:
     * 1. If no record exists for $idempotencyKey, create a claim row with status 'pending'.
     * 2. If a record already exists with status 'sent' or 'logged', return null (already completed).
     * 3. If a record already exists with status 'failed', atomically reclaim it by setting status back to 'pending'.
     * 4. If a record already exists with status 'pending' (and is recent < 5 min), return null (another process is currently sending).
     *
     * @return static|null The claimed NotificationLog record, or null if claim was not acquired.
     */
    public static function claim(
        string $idempotencyKey,
        string $eventType,
        string $notifiableType,
        $notifiableId,
        string $channel = 'email',
        ?string $recipientEmail = null,
        ?string $recipientMobile = null,
        ?string $subject = null,
        ?string $message = null
    ): ?static {
        // Attempt to insert a new pending claim
        try {
            return static::create([
                'idempotency_key'  => $idempotencyKey,
                'event_type'       => $eventType,
                'notifiable_type'  => $notifiableType,
                'notifiable_id'    => (int)$notifiableId,
                'channel'          => $channel,
                'recipient_email'  => $recipientEmail,
                'recipient_mobile' => $recipientMobile,
                'subject'          => $subject,
                'message'          => $message,
                'status'           => 'pending',
            ]);
        } catch (\Throwable $e) {
            // Strictly check if this exception is a genuine unique-constraint collision
            if (!static::isUniqueConstraintViolation($e)) {
                Log::error('NotificationLog: Unexpected database error during claim creation', [
                    'key'             => $idempotencyKey,
                    'event_type'      => $eventType,
                    'notifiable_type' => $notifiableType,
                    'notifiable_id'   => $notifiableId,
                    'error'           => $e->getMessage(),
                ]);
                return null;
            }

            // A record with this idempotency key already exists
            $existing = static::where('idempotency_key', $idempotencyKey)->first();

            if (!$existing) {
                // Fallback check by legacy composite lookup if idempotency_key was null
                $existing = static::where('event_type', $eventType)
                    ->where('notifiable_type', $notifiableType)
                    ->where('notifiable_id', (int)$notifiableId)
                    ->where('channel', $channel)
                    ->first();
            }

            if (!$existing) {
                Log::warning('NotificationLog: Claim collision but record not found', [
                    'key'             => $idempotencyKey,
                    'event_type'      => $eventType,
                    'notifiable_type' => $notifiableType,
                    'notifiable_id'   => $notifiableId,
                ]);
                return null;
            }

            // If already successfully sent or logged, do not send again
            if (in_array($existing->status, ['sent', 'logged'], true)) {
                return null;
            }

            // If previous attempt failed, atomically reclaim for retry
            if ($existing->status === 'failed') {
                $affected = static::where('id', $existing->id)
                    ->where('status', 'failed')
                    ->update([
                        'idempotency_key'  => $idempotencyKey,
                        'status'           => 'pending',
                        'recipient_email'  => $recipientEmail ?: $existing->recipient_email,
                        'recipient_mobile' => $recipientMobile ?: $existing->recipient_mobile,
                        'subject'          => $subject ?: $existing->subject,
                        'message'          => $message ?: $existing->message,
                        'error_message'    => null,
                        'updated_at'       => now(),
                    ]);

                if ($affected > 0) {
                    return $existing->fresh();
                }
                return null;
            }

            // If pending and timed out (> 5 minutes ago), atomically reclaim with timestamp condition in UPDATE
            if ($existing->status === 'pending') {
                $cutoff = now()->subMinutes(5);

                $affected = static::where('id', $existing->id)
                    ->where('status', 'pending')
                    ->where('updated_at', '<=', $cutoff)
                    ->update([
                        'idempotency_key'  => $idempotencyKey,
                        'status'           => 'pending',
                        'recipient_email'  => $recipientEmail ?: $existing->recipient_email,
                        'recipient_mobile' => $recipientMobile ?: $existing->recipient_mobile,
                        'subject'          => $subject ?: $existing->subject,
                        'message'          => $message ?: $existing->message,
                        'error_message'    => null,
                        'updated_at'       => now(),
                    ]);

                if ($affected > 0) {
                    return $existing->fresh();
                }
                return null;
            }

            return null;
        }
    }

    /**
     * Mark a claimed notification as successfully sent / logged.
     */
    public function markSent(?string $subject = null, ?string $message = null, ?string $providerResponse = null): void
    {
        $status = config('mail.default') === 'log' ? 'logged' : 'sent';

        $this->update([
            'status'            => $status,
            'subject'           => $subject ?? $this->subject,
            'message'           => $message ?? $this->message,
            'provider_response' => $providerResponse ?? $this->provider_response,
            'error_message'     => null,
            'sent_at'           => now(),
        ]);
    }

    /**
     * Mark a claimed notification as failed, allowing future retry.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status'        => 'failed',
            'error_message' => substr($errorMessage, 0, 5000),
            'sent_at'       => null,
        ]);
    }

    /**
     * Check if a specific idempotency key has already been successfully sent.
     */
    public static function isKeySent(string $idempotencyKey): bool
    {
        return static::where('idempotency_key', $idempotencyKey)
            ->whereIn('status', ['sent', 'logged'])
            ->exists();
    }

    /**
     * Check if a log entry was already SUCCESSFULLY sent for this notifiable + channel + event.
     * Note: 'failed' entries do NOT count as already sent, so they remain eligible for retry.
     */
    public static function alreadySent(
        string $notifiableType,
        int    $notifiableId,
        string $channel,
        string $eventType
    ): bool {
        return static::where('event_type',      $eventType)
                     ->where('notifiable_type', $notifiableType)
                     ->where('notifiable_id',   $notifiableId)
                     ->where('channel',         $channel)
                     ->whereIn('status',        ['sent', 'logged', 'created', 'not_configured'])
                     ->exists();
    }

    /**
     * Record a notification attempt.
     */
    public static function record(array $data): static
    {
        return static::create($data);
    }
}
