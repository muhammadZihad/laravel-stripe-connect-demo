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
        return view('auth.login');
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
