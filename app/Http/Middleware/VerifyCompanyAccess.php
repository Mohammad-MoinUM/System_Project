<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Company;

class VerifyCompanyAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $companyId = $request->route('companyId') ?? $request->route('company_id');

        if (!$user || !$companyId) {
            abort(403);
        }

        $company = Company::find($companyId);

        if (!$company || !$user->isPartOfCompany($companyId)) {
            abort(403, 'You do not have access to this company');
        }

        return $next($request);
    }
}
