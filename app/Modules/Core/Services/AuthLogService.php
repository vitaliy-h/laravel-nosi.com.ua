<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogResultEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\Core\Models\AuthLog;
use App\Modules\Core\Support\AuthLogSourceResolver;
use App\Modules\Core\Support\UserAgentParser;
use App\Modules\User\Models\User;

/**
 * Writes the authentication journal (log_auth).
 *
 * Separate from LogService: that one audits model changes, this one records
 * who signed in, when, from where and with what result.
 */
class AuthLogService
{
    public function success(AuthLogActionEnum $action, string $login, ?User $user = null, ?AuthLogSourceEnum $source = null): void
    {
        $this->log($action, AuthLogResultEnum::SUCCESS, $login, $user, $source);
    }

    public function failure(AuthLogActionEnum $action, string $login, ?User $user = null, ?AuthLogSourceEnum $source = null): void
    {
        $this->log($action, AuthLogResultEnum::FAILURE, $login, $user, $source);
    }

    public function blocked(AuthLogActionEnum $action, string $login, ?User $user = null, ?AuthLogSourceEnum $source = null): void
    {
        $this->log($action, AuthLogResultEnum::BLOCKED, $login, $user, $source);
    }

    /**
     * $source is only passed where the caller knows it better than the
     * resolver — the Filament login page, which Livewire drives outside a
     * panel request.
     */
    public function log(
        AuthLogActionEnum $action,
        AuthLogResultEnum $result,
        string $login,
        ?User $user = null,
        ?AuthLogSourceEnum $source = null,
    ): void {
        if (!config('auth_log.enabled')) {
            return;
        }

        $userAgent = request()?->userAgent();
        $parsed = UserAgentParser::parse($userAgent);

        AuthLog::query()->create([
            'user_id'    => $user?->id,
            'login'      => $login,
            'source'     => $source ?? AuthLogSourceResolver::resolve(),
            'action'     => $action,
            'result'     => $result,
            'ip_address' => request()?->ip(),
            'user_agent' => $userAgent,
            'browser'    => $parsed['browser'],
            'platform'   => $parsed['platform'],
        ]);
    }
}
