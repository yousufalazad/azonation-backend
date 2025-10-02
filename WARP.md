# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is the **Azonation Backend** - a Laravel 11 API application serving as the backend for a comprehensive organization management platform. The application supports three main user types: Individual users, Organizations, and Super Administrators, with extensive functionality for membership management, events, meetings, projects, financial operations, and e-commerce.

## Technology Stack

- **Framework**: Laravel 11 (PHP 8.2+)
- **Authentication**: Laravel Sanctum
- **Database**: SQLite (default), with support for MySQL/PostgreSQL
- **Frontend Assets**: Vite.js for asset building
- **Testing**: PHPUnit
- **Code Formatting**: Laravel Pint
- **Payment Processing**: Stripe integration
- **Social Authentication**: Laravel Socialite (Google OAuth)

## Architecture Overview

The application follows a **multi-tenant architecture** with clear separation between user types:

### Controller Organization
- **Auth/**: Authentication and user management
- **Common/**: Shared functionality across user types
- **Individual/**: Individual user-specific features
- **Org/**: Organization management features (largest section)
- **SuperAdmin/**: Platform administration and financial management
- **Ecommerce/**: Product catalog and order management

### Key Domain Areas
1. **Membership Management**: Complete lifecycle of organization memberships
2. **Event & Meeting Management**: Scheduling, attendance tracking, summaries
3. **Project Management**: Project tracking with attendance and summaries
4. **Asset Management**: Organization asset tracking with lifecycle status
5. **Financial Management**: Billing, invoicing, payment processing
6. **Committee Management**: Committee structure and member assignments
7. **E-commerce**: Product catalog, orders, and payment integration

### Authentication & Authorization
- Uses Laravel Sanctum for API token authentication
- Google OAuth integration for social login
- Role-based access with three main user types
- Most routes protected by `auth:sanctum` middleware

## Development Commands

### Setup & Installation
```powershell
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
Copy-Item .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed database (if seeders exist)
php artisan db:seed
```

### Daily Development
```powershell
# Start development server
php artisan serve

# Run asset compilation (watch mode)
npm run dev

# Build assets for production
npm run build

# Clear application caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate optimized autoloader
composer dump-autoload
```

### Database Operations
```powershell
# Create new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback last migration batch
php artisan migrate:rollback

# Reset and re-run all migrations
php artisan migrate:fresh

# Check migration status
php artisan migrate:status
```

### Code Quality & Testing
```powershell
# Run Laravel Pint (code formatting)
./vendor/bin/pint

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run tests with coverage
php artisan test --coverage

# Run single test file
php artisan test tests/Feature/ExampleTest.php

# Run specific test method
php artisan test --filter testBasicTest
```

### Artisan Commands
```powershell
# Create new controller
php artisan make:controller ControllerName

# Create new model with migration
php artisan make:model ModelName -m

# Create new request class
php artisan make:request RequestName

# Create new job
php artisan make:job JobName

# Create new mail class
php artisan make:mail MailName

# Create new console command
php artisan make:command CommandName

# List all routes
php artisan route:list

# Run queue workers (if using queues)
php artisan queue:work

# Process scheduled tasks
php artisan schedule:work
```

### Custom Artisan Commands
The application includes several custom billing commands:
```powershell
# Generate daily management bills
php artisan generate:everyday-management-bill

# Generate daily storage bills
php artisan generate:everyday-storage-bill

# Generate billing orders
php artisan generate:management-storage-billing-order

# Generate invoices
php artisan generate:management-storage-invoice

# Generate monthly bills
php artisan generate:monthly-management-storage-bill
```

## Coding Patterns & Standards

### API Response Pattern
All API endpoints return JSON responses. Follow the established pattern in existing controllers for consistency.

### Model Relationships
The application uses extensive Eloquent relationships. When working with models, pay attention to:
- Organization-centric relationships (most entities belong to organizations)
- User type differentiation (Individual vs Organization members)
- Attendance tracking patterns across events/meetings/projects

### Authentication Flow
- User registration creates different user types
- Email verification required for account activation
- Social authentication integration with profile completion flow
- API authentication uses Sanctum tokens

### File Upload Handling
Many entities support file/image uploads. Follow existing patterns in controllers like:
- Event images/files
- Asset images/files
- Profile images
- Account transaction files

### Naming Conventions
- Controllers: Use descriptive names with proper namespacing
- Routes: Follow RESTful conventions with resource grouping
- Database: Use singular model names, plural table names
- API endpoints: Use kebab-case for URL segments

## Environment-Specific Notes

### Windows Development (XAMPP)
This project is configured for Windows development with XAMPP:
- Uses SQLite as default database (simpler setup)
- Vite configuration suitable for Windows environments
- PowerShell commands provided for Windows compatibility

### Production Considerations
- Switch to MySQL/PostgreSQL for production
- Configure proper mail driver (currently set to log)
- Set up queue worker processes for background jobs
- Configure Stripe webhook endpoints
- Set up proper file storage (S3, etc.)

## Key Features to Understand

### Multi-Level Organization Structure
Organizations can have complex hierarchical structures with:
- Primary administrators
- Committee structures with members
- Multiple membership types and renewal cycles
- Asset management with lifecycle tracking

### Financial Management
Comprehensive billing system including:
- Daily billing calculations
- Management and storage pricing
- Regional tax rates
- Invoice generation
- Receipt management

### Event/Meeting Management
Rich event management with:
- Attendance tracking (members + guests)
- Event summaries and minutes
- File attachments
- Dignitary management for events

### E-commerce Integration
Full product catalog with:
- Hierarchical categories (Category > SubCategory > SubSubCategory)
- Brand management
- Order processing
- Shopping cart functionality

## Important Files & Locations

- **Routes**: `routes/api.php` (extensive API routes), `routes/web.php` (minimal web routes)
- **Models**: `app/Models/` (extensive model collection)
- **Controllers**: `app/Http/Controllers/` (organized by domain)
- **Migrations**: `database/migrations/` (database schema)
- **Configuration**: `config/` directory for app settings
- **Environment**: `.env` file for local configuration

## Testing Strategy

- Feature tests for API endpoints
- Unit tests for business logic
- Authentication tests for protected routes
- Database testing with migrations
- Use `RefreshDatabase` trait for clean test state
