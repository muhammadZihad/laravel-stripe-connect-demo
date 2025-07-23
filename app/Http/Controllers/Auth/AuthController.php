<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        // Get all companies with their related data
        $companies = Company::with([
            'user', 
            'invoices' => function($query) {
                $query->latest()->limit(3);
            }
        ])->get()->map(function($company) {
            return [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'user' => $company->user,
                'invoices_count' => $company->invoices()->count(),
                'recent_invoices' => $company->invoices,
                'stripe_onboarding_complete' => $company->user->stripe_onboarding_complete,
                'stripe_connect_account_id' => $company->user->stripe_connect_account_id,
            ];
        });

        // Get all agents with their related data
        $agents = Agent::with([
            'user', 
            'company',
            'invoices' => function($query) {
                $query->latest()->limit(3);
            }
        ])->get()->map(function($agent) {
            return [
                'id' => $agent->id,
                'name' => $agent->user->name,
                'agent_code' => $agent->agent_code,
                'user' => $agent->user,
                'company' => $agent->company,
                'invoices_count' => $agent->invoices()->count(),
                'recent_invoices' => $agent->invoices,
                'stripe_onboarding_complete' => $agent->user->stripe_onboarding_complete,
                'stripe_connect_account_id' => $agent->user->stripe_connect_account_id,
            ];
        });

        return view('auth.login', compact('companies', 'agents'));
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirect based on role
            switch ($user->role) {
                case 'super_admin':
                    return redirect()->route('super_admin.dashboard');
                case 'company':
                    return redirect()->route('company.dashboard');
                case 'agent':
                    return redirect()->route('agent.dashboard');
                default:
                    Auth::logout();
                    return back()->withErrors(['email' => 'Invalid user role.']);
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Quick login for demo purposes
     */
    public function quickLogin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        Auth::login($user);
        $request->session()->regenerate();
        
        // Redirect based on role
        switch ($user->role) {
            case 'super_admin':
                return redirect()->route('super_admin.dashboard')
                    ->with('success', 'Quick login successful!');
            case 'company':
                return redirect()->route('company.dashboard')
                    ->with('success', 'Quick login successful!');
            case 'agent':
                return redirect()->route('agent.dashboard')
                    ->with('success', 'Quick login successful!');
            default:
                Auth::logout();
                return back()->withErrors(['error' => 'Invalid user role.']);
        }
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle company registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'tax_id' => 'nullable|string|max:50',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'company',
        ]);

        // Create company profile
        Company::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'business_type' => $request->business_type,
            'address' => $request->address,
            'phone' => $request->phone,
            'website' => $request->website,
            'tax_id' => $request->tax_id,
        ]);

        Auth::login($user);

        return redirect()->route('company.dashboard')
            ->with('success', 'Registration successful! Please complete your Stripe Connect onboarding.');
    }

    /**
     * Create agent (for companies)
     */
    public function createAgent(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCompany() && !$user->isSuperAdmin()) {
            abort(403, 'Only companies or super admins can create agents.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'company_id' => $user->isSuperAdmin() ? 'required|exists:companies,id' : 'nullable',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'department' => 'nullable|string|max:255',
            'hire_date' => 'nullable|date',
        ]);

        // Create user
        $agentUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'agent',
        ]);

        // Determine company ID
        $companyId = $user->isSuperAdmin() 
            ? $request->company_id 
            : $user->company->id;

        $company = Company::find($companyId);

        // Create agent profile
        $agent = Agent::create([
            'user_id' => $agentUser->id,
            'company_id' => $companyId,
            'agent_code' => Agent::generateAgentCode($company),
            'commission_rate' => $request->commission_rate ?? 0,
            'department' => $request->department,
            'hire_date' => $request->hire_date ?? now(),
        ]);

        // Redirect based on user role
        $redirectRoute = $user->isSuperAdmin() 
            ? 'super_admin.agents' 
            : 'company.agents';

        return redirect()->route($redirectRoute)
            ->with('success', 'Agent created successfully! Agent code: ' . $agent->agent_code);
    }

    /**
     * Create company (for super admin)
     */
    public function createCompany(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only super admins can create companies.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'tax_id' => 'nullable|string|max:50',
        ]);

        // Create user
        $companyUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'company',
        ]);

        // Create company profile
        Company::create([
            'user_id' => $companyUser->id,
            'company_name' => $request->company_name,
            'business_type' => $request->business_type,
            'address' => $request->address,
            'phone' => $request->phone,
            'website' => $request->website,
            'tax_id' => $request->tax_id,
        ]);

        return redirect()->route('super_admin.companies')
            ->with('success', 'Company created successfully!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show agent creation form (for companies)
     */
    public function showAgentForm()
    {
        $user = Auth::user();
        
        if (!$user->isCompany() && !$user->isSuperAdmin()) {
            abort(403, 'Only companies or super admins can create agents.');
        }

        // If it's a super admin, they can assign agent to any company
        if ($user->isSuperAdmin()) {
            $companies = \App\Models\Company::all();
        } else {
            // If it's a company, they can only assign to their own company
            $companies = [\App\Models\Company::where('user_id', $user->id)->first()];
        }

        return view('auth.agent-form', compact('companies'));
    }

    /**
     * Show company creation form (for super admin)
     */
    public function showCompanyForm()
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only super admins can create companies.');
        }

        return view('auth.company-form');
    }

    /**
     * Get current user info
     */
    public function user(Request $request)
    {
        $user = Auth::user();
        $profile = null;

        if ($user->isCompany()) {
            $profile = $user->company;
        } elseif ($user->isAgent()) {
            $profile = $user->agent;
        }

        return response()->json([
            'user' => $user,
            'profile' => $profile,
            'role' => $user->role,
        ]);
    }
}
