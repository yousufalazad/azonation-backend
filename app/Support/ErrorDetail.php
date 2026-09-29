<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * What an API response may say about an exception.
 *
 * Exception messages can contain SQL, file paths or data from other
 * records, so they are only shown when APP_DEBUG is on (local
 * development). In production the client gets a generic sentence and the
 * full exception goes to the log.
 *
 *   'error' => ErrorDetail::for($e)
 */
class ErrorDetail
{
    public const GENERIC = 'Something went wrong. Please try again.';

    public static function for(Throwable $e): string
    {
        Log::error($e->getMessage(), ['exception' => $e]);

        return config('app.debug') ? $e->getMessage() : self::GENERIC;
    }
}
