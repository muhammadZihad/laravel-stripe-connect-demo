# 🧾 Stripe Invoicing System

A comprehensive invoicing platform built with Laravel and Stripe Connect that enables companies to manage agents, create invoices, and process payments with automated commission distribution.

## 🚀 Features

### 🏢 **Multi-Role System**
- **Super Admin**: System-wide management and oversight
- **Company**: Manage agents, create invoices, handle payments
- **Agent**: View assigned invoices, manage earnings, track performance

### 💳 **Stripe Integration**
- **Stripe Connect**: Onboarding for companies and agents
- **Payment Processing**: Credit cards and ACH bank transfers
- **Micro-deposit Verification**: Secure bank account verification
- **Automatic Commission Distribution**: 10% platform fee (capped at $1-$4)
- **Real-time Webhooks**: Payment status updates

### 📊 **Invoice Management**
- **Dynamic Invoice Creation**: Itemized billing with tax calculations
- **Payment Processing**: Secure Stripe-powered payments
- **Status Tracking**: Pending, paid, overdue invoice statuses
- **Commission Calculation**: Automatic fee distribution

### 🔒 **Security & Authentication**
- **Role-based Access Control**: Middleware-protected routes
- **CSRF Protection**: Secure form handling (webhooks exempted)
- **User Authentication**: Laravel Sanctum integration
- **Data Validation**: Comprehensive input validation

## 🛠️ Technologies Used

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
- **Database**: MySQL/PostgreSQL
- **Payment Processing**: Stripe Connect API
- **Build Tools**: Vite
- **Authentication**: Laravel Sanctum

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL or PostgreSQL
- Stripe Account (for Connect functionality)

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd stripe-invoicing
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stripe_invoicing
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate:fresh --seed
```

### 5. Stripe Configuration
```bash
# Add Stripe keys to .env
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# Configure Cashier settings
CASHIER_KEY="${STRIPE_KEY}"
CASHIER_SECRET="${STRIPE_SECRET}"
CASHIER_WEBHOOK_SECRET="${STRIPE_WEBHOOK_SECRET}"
```

### 6. Build Assets
```bash
# Build frontend assets
npm run build

# Or for development
npm run dev
```

### 7. Start the Application
```bash
# Start Laravel development server
php artisan serve

# Application will be available at http://localhost:8000
```

## 🧪 Testing Features

### Quick Demo Login
The application includes a comprehensive demo login page that shows all available accounts:

1. **Visit**: `http://localhost:8000`
2. **Choose Account Type**: Companies, Agents, or Super Admin
3. **One-Click Login**: Click any "Login as..." button
4. **Default Password**: `password` (for traditional login)

### Demo Accounts

#### 🔑 **Super Admin**
- **Email**: admin@stripeinvoicing.com
- **Access**: Full system administration

#### 🏢 **Companies**
- **TechCorp Solutions**: john@techcorp.com
- **MarketingPro Agency**: sarah@marketingpro.com  
- **Consultant Group LLC**: michael@consultgroup.com

#### 👥 **Agents**
- **Alice Cooper**: alice@techcorp.com (TechCorp)
- **Bob Wilson**: bob@techcorp.com (TechCorp)
- **Carol Davis**: carol@marketingpro.com (MarketingPro)
- **David Miller**: david@marketingpro.com (MarketingPro)
- **Emma Garcia**: emma@consultgroup.com (Consultant Group)
- **Frank Martinez**: frank@consultgroup.com (Consultant Group)

## 🧪 Feature Testing Guide

### 1. **Stripe Connect Onboarding**
```bash
# Test as Company or Agent
1. Login to any company/agent account
2. Go to dashboard
3. Click "Complete Stripe Connect Setup"
4. Use Stripe test data for onboarding
```

### 2. **Invoice Creation & Payment**
```bash
# Test as Company
1. Login as company (e.g., john@techcorp.com)
2. Navigate to "Invoices" → "Create Invoice"
3. Select an agent and fill invoice details
4. Save invoice
5. Go to "Pay Invoice" and test payment flow
```

### 3. **Payment Methods Management**
```bash
# Test Payment Method Addition
1. Go to "Payment Methods" section
2. Click "Add Payment Method"
3. Use Stripe test cards:
   - Visa: 4242424242424242
   - Mastercard: 5555555555554444
   - Amex: 378282246310005
```

### 4. **Bank Account Verification**
```bash
# Test ACH Payments
1. Add a US bank account payment method
2. Use test routing numbers:
   - Chase: 021000021
   - Bank of America: 031000503
3. Test micro-deposit verification flow
```

### 5. **Webhook Testing**
```bash
# Setup Stripe CLI for local webhook testing
stripe listen --forward-to localhost:8000/stripe/webhook

# Test webhook events
stripe trigger payment_intent.succeeded
```

## 🔍 Key Areas to Test

### **Dashboard Analytics**
- **Company Dashboard**: Revenue metrics, agent performance
- **Agent Dashboard**: Earnings, commission tracking
- **Super Admin Dashboard**: System-wide analytics

### **Payment Processing**
- **Invoice Payments**: End-to-end payment flow
- **Commission Distribution**: Automatic fee calculation
- **Payment Method Management**: Add, verify, delete methods

### **User Management**
- **Role Permissions**: Access control testing
- **Agent Creation**: Company creating new agents
- **Profile Management**: User information updates

### **Stripe Integration**
- **Connect Onboarding**: Account verification flow
- **Payment Intent Creation**: Charge processing
- **Webhook Handling**: Real-time payment updates

## 🌐 API Endpoints

### **Authentication**
- `POST /login` - User authentication
- `POST /quick-login` - Demo quick login
- `POST /logout` - User logout

### **Stripe Integration**
- `GET /stripe/onboarding` - Start Connect onboarding
- `GET /stripe/connect/return` - Handle onboarding return
- `POST /stripe/webhook` - Webhook handler (CSRF exempt)
- `POST /stripe/payment` - Process payments

### **Dashboard Routes**
- `/super-admin/*` - Super admin routes
- `/company/*` - Company routes  
- `/agent/*` - Agent routes

## 🗃️ Database Schema

### **Core Tables**
- `users` - User authentication and roles
- `companies` - Company profiles and Stripe data
- `agents` - Agent profiles and commission rates
- `invoices` - Invoice details and status
- `transactions` - Payment records and commissions
- `payment_methods` - Stored payment methods

## 🐛 Troubleshooting

### **Common Issues**

#### **Styles Not Loading**
```bash
# Rebuild assets
npm run build
# or for development
npm run dev
```

#### **Database Issues**
```bash
# Reset database
php artisan migrate:fresh --seed
```

#### **Stripe Webhook Issues**
```bash
# Check webhook URL is accessible
curl -X POST http://localhost:8000/stripe/webhook

# Verify webhook secret in .env
```

#### **Permission Denied**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

## 📱 Mobile Responsiveness

The application is fully responsive and works on:
- **Desktop**: Full feature access
- **Tablet**: Optimized layouts
- **Mobile**: Touch-friendly interface

## 🔮 Future Enhancements

- **Multi-currency Support**: International payment processing
- **Advanced Reporting**: Enhanced analytics and insights
- **API Development**: RESTful API for third-party integrations
- **Mobile App**: Native mobile applications
- **Subscription Billing**: Recurring payment support

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new features
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support and questions:
- **Documentation**: Review this README
- **Issues**: Create GitHub issues for bugs
- **Testing**: Use the demo accounts provided

---

**Built with ❤️ using Laravel and Stripe Connect**
