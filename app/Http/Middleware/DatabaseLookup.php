<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use App\Models\LookupContact;
use App\Models\LookupInvitation;
use App\Models\LookupAccountToken;

class DatabaseLookup
{
    public function handle(Request $request, Closure $next, $guard = 'user')
    {
        if (! env('MULTI_DB_ENABLED')) {
            return $next($request);
        }

        if ($guard == 'user') {
            // user's value is set when logging in
            if (! session(SESSION_DB_SERVER)) {
                return redirect('/logout');
            }
        } elseif ($guard == 'api') {
            if ($token = $request->header('X-Ninja-Token')) {
                LookupAccountToken::setServerByField('token', $token);
            }
        } elseif ($guard == 'contact') {
            if (request()->invitation_key) {
                LookupInvitation::setServerByField('invitation_key', request()->invitation_key);
            } elseif (request()->contact_key) {
                LookupContact::setServerByField('contact_key', request()->contact_key);
            }
        } elseif ($guard == 'postmark') {
            LookupInvitation::setServerByField('message_id', request()->MessageID);
        }

        return $next($request);
    }
}