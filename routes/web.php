<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\Dashboard\SuperAdminController;
use App\Http\Controllers\Dashboard\CompanyController;
use App\Http\Controllers\Dashboard\AgentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


Route::get('reset-db', function () {
    Artisan::call('migrate:fresh --seed');
    Artisan::call('db:seed');
    return 'done';
});

Route::get('reset-cache', function () {
    Artisan::call('optimize:clear');
    return 'done';
});


// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('quick-login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Admin-only form routes (for creating companies and agents)
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/company-form', [AuthController::class, 'showCompanyForm'])->name('auth.company-form');
    Route::post('/create-company', [AuthController::class, 'createCompany'])->name('auth.create-company');
    Route::get('/agent-form', [AuthController::class, 'showAgentForm'])->name('auth.agent-form');
    Route::post('/create-agent', [AuthController::class, 'createAgent'])->name('auth.create-agent');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');
});

// Stripe Connect Routes
Route::middleware('auth')->group(function () {
    Route::get('/stripe/onboarding', [StripeController::class, 'startOnboarding'])->name('stripe.onboarding');
    Route::get('/stripe/start-onboarding', [StripeController::class, 'startOnboarding'])->name('stripe.start-onboarding');
    Route::get('/stripe/connect/return', [StripeController::class, 'connectReturn'])->name('stripe.connect.return');
    Route::get('/stripe/connect/refresh', [StripeController::class, 'connectRefresh'])->name('stripe.connect.refresh');
    Route::get('/stripe/status', [StripeController::class, 'getOnboardingStatus'])->name('stripe.status');
    Route::post('/stripe/payment', [StripeController::class, 'processPayment'])->name('stripe.payment');
});

// Stripe Webhook (public endpoint)
Route::match(['get', 'post'], '/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

// Public Invoice Payment Routes (no auth required)
Route::get('/invoice/pay/{token}', [StripeController::class, 'showPublicInvoicePayment'])->name('invoice.public.pay');
Route::post('/invoice/pay/{token}', [StripeController::class, 'processPublicInvoicePayment'])->name('invoice.public.process');
Route::post('/invoice/pay/{token}/attach-payment-method', [StripeController::class, 'attachPaymentMethodToPublicInvoice'])->name('invoice.public.attach-payment-method');
Route::post('/invoice/pay/{token}/create-fc-session', [StripeController::class, 'createFinancialConnectionsSessionForPublicPayment'])->name('invoice.public.create-fc-session');
Route::post('/invoice/pay/{token}/check-fc-status', [StripeController::class, 'checkPublicInvoiceFinancialConnectionsStatus'])->name('invoice.public.check-fc-status');

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super_admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    
    // Companies Management
    Route::get('/companies', [SuperAdminController::class, 'companies'])->name('companies');
    Route::get('/companies/{company}', [SuperAdminController::class, 'showCompany'])->name('companies.show');
    Route::get('/companies/{company}/edit', [SuperAdminController::class, 'editCompany'])->name('companies.edit');
    Route::put('/companies/{company}', [SuperAdminController::class, 'updateCompany'])->name('companies.update');
    Route::get('/companies/{company}/agents', [SuperAdminController::class, 'getCompanyAgents'])->name('companies.agents');
    
    // Company Creation
    Route::get('/create-company', [AuthController::class, 'showCompanyForm'])->name('companies.create');
    Route::post('/create-company', [AuthController::class, 'createCompany'])->name('companies.store');
    
    // Agents Management
    Route::get('/agents', [SuperAdminController::class, 'agents'])->name('agents');
    Route::get('/agents/{agent}', [SuperAdminController::class, 'showAgent'])->name('agents.show');
    Route::get('/agents/{agent}/edit', [SuperAdminController::class, 'editAgent'])->name('agents.edit');
    Route::put('/agents/{agent}', [SuperAdminController::class, 'updateAgent'])->name('agents.update');
    
    // Invoices Management
    Route::get('/invoices', [SuperAdminController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/create', [SuperAdminController::class, 'createInvoice'])->name('invoices.create');
    Route::post('/invoices', [SuperAdminController::class, 'storeInvoice'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [SuperAdminController::class, 'showInvoice'])->name('invoices.show');
    Route::get('/invoices/{invoice}/process-payment', [SuperAdminController::class, 'processInvoicePayment'])->name('invoices.process-payment');
    Route::post('/invoices/{invoice}/payment', [SuperAdminController::class, 'processInvoicePayment'])->name('invoices.payment');
    Route::post('/invoices/{invoice}/send', [SuperAdminController::class, 'sendInvoice'])->name('invoices.send');
    
    // Transactions
    Route::get('/transactions', [SuperAdminController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{transaction}', [SuperAdminController::class, 'showTransaction'])->name('transactions.show');
    
    // Payment Methods
    Route::post('/payment-methods', [SuperAdminController::class, 'addPaymentMethodToEntity'])->name('payment-methods.store');
    Route::post('/stripe/onboarding-link', [StripeController::class, 'createOnboardingLinkForEntity'])->name('stripe.onboarding-link');
    
    // Analytics & Reports
    Route::get('/analytics', [SuperAdminController::class, 'analytics'])->name('analytics');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
});

/*
|--------------------------------------------------------------------------
| Company Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'company'])->prefix('company')->name('company.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [CompanyController::class, 'dashboard'])->name('dashboard');
    
    // Agents Management
    Route::get('/agents', [CompanyController::class, 'agents'])->name('agents');
    Route::get('/agents/create', [AuthController::class, 'showAgentForm'])->name('agents.create');
    Route::post('/agents', [AuthController::class, 'createAgent'])->name('agents.store');
    Route::get('/agents/{agent}', [CompanyController::class, 'showAgent'])->name('agents.show');
    Route::get('/agents/{agent}/edit', [CompanyController::class, 'editAgent'])->name('agents.edit');
    Route::put('/agents/{agent}', [CompanyController::class, 'updateAgent'])->name('agents.update');
    
    // Invoices Management
    Route::get('/invoices', [CompanyController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/create', [CompanyController::class, 'createInvoice'])->name('invoices.create');
    Route::post('/invoices', [CompanyController::class, 'storeInvoice'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [CompanyController::class, 'showInvoice'])->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', [CompanyController::class, 'editInvoice'])->name('invoices.edit');
    Route::put('/invoices/{invoice}', [CompanyController::class, 'updateInvoice'])->name('invoices.update');
    
    // Invoice Payment
    Route::get('/invoices/{invoice}/pay', [CompanyController::class, 'showPayInvoice'])->name('invoices.pay');
    Route::post('/invoices/{invoice}/pay', [CompanyController::class, 'payInvoice'])->name('invoices.pay.process');
    
    // Send Invoice via Email
    Route::post('/invoices/{invoice}/send', [CompanyController::class, 'sendInvoice'])->name('invoices.send');
    
    // Transactions
    Route::get('/transactions', [CompanyController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{transaction}', [CompanyController::class, 'showTransaction'])->name('transactions.show');
    
    // Payment Methods
    Route::get('/payment-methods', [CompanyController::class, 'paymentMethods'])->name('payment-methods');
    Route::get('/payment-methods/create', [CompanyController::class, 'addPaymentMethod'])->name('payment-methods.create');
    Route::post('/payment-methods', [CompanyController::class, 'storePaymentMethod'])->name('payment-methods.store');
    Route::post('/payment-methods/{paymentMethod}/set-default', [CompanyController::class, 'setDefaultPaymentMethod'])->name('payment-methods.set-default');
    Route::delete('/payment-methods/{paymentMethod}', [CompanyController::class, 'deletePaymentMethod'])->name('payment-methods.delete');
    
    // Financial Connections for adding bank accounts
    Route::post('/payment-methods/create-fc-session', [CompanyController::class, 'createFinancialConnectionsSession'])->name('payment-methods.create-fc-session');
    Route::post('/payment-methods/check-addition-status', [CompanyController::class, 'checkFinancialConnectionsAdditionStatus'])->name('payment-methods.check-addition-status');
    Route::get('/payment-methods/add-complete', [CompanyController::class, 'completeFinancialConnectionsAddition'])->name('payment-methods.add-complete');
    
    // Payment Method Verification
    Route::post('/payment-methods/{paymentMethod}/initiate-verification', [CompanyController::class, 'initiateVerification'])->name('payment-methods.initiate-verification');
    Route::get('/payment-methods/{paymentMethod}/verify', [CompanyController::class, 'showVerifyForm'])->name('payment-methods.verify');
    Route::post('/payment-methods/{paymentMethod}/verify', [CompanyController::class, 'verifyMicroDeposits'])->name('payment-methods.verify.submit');
    Route::get('/payment-methods/verify-complete/{paymentMethod}', [CompanyController::class, 'completeFinancialConnectionsVerification'])->name('payment-methods.verify-complete');
    Route::get('/payment-methods/{paymentMethod}/check-status', [CompanyController::class, 'checkFinancialConnectionsStatus'])->name('payment-methods.check-status');
    
    // Profile & Settings
    Route::get('/profile', [CompanyController::class, 'profile'])->name('profile');
    Route::put('/profile', [CompanyController::class, 'updateProfile'])->name('profile.update');
    
    // Reports
    Route::get('/reports', [CompanyController::class, 'reports'])->name('reports');
});

/*
|--------------------------------------------------------------------------
| Agent Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'agent'])->prefix('agent')->name('agent.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AgentController::class, 'dashboard'])->name('dashboard');
    
    // Invoices (View Only)
    Route::get('/invoices', [AgentController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}', [AgentController::class, 'showInvoice'])->name('invoices.show');
    
    // Transactions (View Only)
    Route::get('/transactions', [AgentController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{transaction}', [AgentController::class, 'showTransaction'])->name('transactions.show');
    
    // Payment Methods
    Route::get('/payment-methods', [AgentController::class, 'paymentMethods'])->name('payment-methods');
    Route::get('/payment-methods/create', [AgentController::class, 'addPaymentMethod'])->name('payment-methods.create');
    Route::post('/payment-methods', [AgentController::class, 'storePaymentMethod'])->name('payment-methods.store');
    Route::post('/payment-methods/{paymentMethod}/set-default', [AgentController::class, 'setDefaultPaymentMethod'])->name('payment-methods.set-default');
    Route::delete('/payment-methods/{paymentMethod}', [AgentController::class, 'deletePaymentMethod'])->name('payment-methods.delete');
    
    // Financial Connections for adding bank accounts
    Route::post('/payment-methods/create-fc-session', [AgentController::class, 'createFinancialConnectionsSession'])->name('payment-methods.create-fc-session');
    Route::post('/payment-methods/check-addition-status', [AgentController::class, 'checkFinancialConnectionsAdditionStatus'])->name('payment-methods.check-addition-status');
    Route::get('/payment-methods/add-complete', [AgentController::class, 'completeFinancialConnectionsAddition'])->name('payment-methods.add-complete');
    
    // Payment Method Verification
    Route::post('/payment-methods/{paymentMethod}/initiate-verification', [AgentController::class, 'initiateVerification'])->name('payment-methods.initiate-verification');
    Route::get('/payment-methods/{paymentMethod}/verify', [AgentController::class, 'showVerifyForm'])->name('payment-methods.verify');
    Route::post('/payment-methods/{paymentMethod}/verify', [AgentController::class, 'verifyMicroDeposits'])->name('payment-methods.verify.submit');
    Route::get('/payment-methods/verify-complete/{paymentMethod}', [AgentController::class, 'completeFinancialConnectionsVerification'])->name('payment-methods.verify-complete');
    Route::get('/payment-methods/{paymentMethod}/check-status', [AgentController::class, 'checkFinancialConnectionsStatus'])->name('payment-methods.check-status');
    
    // Profile & Settings
    Route::get('/profile', [AgentController::class, 'profile'])->name('profile');
    Route::put('/profile', [AgentController::class, 'updateProfile'])->name('profile.update');
    
    // Performance & Earnings
    Route::get('/performance', [AgentController::class, 'performance'])->name('performance');
    Route::get('/earnings', [AgentController::class, 'earnings'])->name('earnings');
    Route::get('/company', [AgentController::class, 'company'])->name('company');
});
