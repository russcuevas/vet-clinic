<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $user = auth()->user();

        // Admin has universal access
        if ($user->role === 'admin') {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            // Redirect to appropriate role dashboard
            return match ($user->role) {
                'cashier' => redirect()->route('cashier.dashboard')->with('error', 'Access restricted to Cashier module.'),
                'veterinarian' => redirect()->route('vet.dashboard')->with('error', 'Access restricted to Veterinarian module.'),
                'manager' => redirect()->route('manager.dashboard')->with('error', 'Access restricted to Manager module.'),
                'inventory_officer' => redirect()->route('inventory_officer.instruments.index')->with('error', 'Access restricted to Inventory Officer module.'),
                'back_office' => redirect()->route('back_office.instruments.index')->with('error', 'Access restricted to Back Office module.'),
                default => redirect()->route('login')->with('error', 'Unauthorized access.'),
            };
        }

        return $next($request);
    }
}
