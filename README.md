# Abonnement

A lightweight PHP REST API to manage subscriptions (free and paid) without framework. Authentication is delegated to an external service and validated via JWT middleware.

## Description

Abonnement is a standalone microservice built in pure PHP (no framework) designed to handle the full lifecycle of user subscriptions. It is meant to be used behind an API gateway that handles authentication and forwards requests with the `user_id` already injected.

The service covers two types of plans:
- **Free plan**: automatically activated, limited to 30 days, non-renewable
- **Paid plan**: requires a payment to be processed before activation, renewable

Payments are handled through an abstracted gateway layer supporting both **Stripe** and **PayPal**, switchable via a single environment variable. The active provider processes the charge and returns a transaction ID that is stored alongside the payment record.

The project is built with a Laravel-inspired architecture (Eloquent ORM, migrations, seeders) while remaining completely framework-free, making it lightweight and easy to deploy as an internal service.

### Key design decisions
- **No authentication**: delegated entirely to the API gateway
- **No framework**: only essential packages (Eloquent, FastRoute, Monolog, dotenv)
- **Payment abstraction**: swap Stripe/PayPal without touching business logic
- **Auto-discovery**: migrations and seeders are discovered automatically, no manual registration needed

## Project Structure
```
src/
├── Controller/     # HTTP controllers
├── Model/          # Eloquent models
├── Migration/      # Database migrations
└── Service/        # Business logic services
    └── Payment/    # Payment gateway implementations

bin/
├── migrate.php             # Run migrations
├── make-model.php          # Generate model + migration
└── make-controller.php     # Generate controller

config/
├── database.php    # Database configuration
└── routes.php      # Route definitions
```

## API Routes
```
GET    /api/plans               # List all plans
GET    /api/plans/{id}          # Get one plan

POST   /api/subscriptions       # Subscribe to a plan
GET    /api/subscriptions/{id}  # Get subscription details
DELETE /api/subscriptions/{id}  # Cancel subscription

GET    /api/payments            # Payment history
POST   /api/payments            # Process a payment
```

## Build Plan

### Step 1 - Project Foundation ✅
- [x] Project structure (MVC)
- [x] Routing with FastRoute
- [x] Database connection with Eloquent ORM
- [x] Logging with Monolog
- [x] Environment configuration with dotenv
- [x] BaseModel with migration support
- [x] Auto-discovery migration runner
- [x] Model and controller generators

### Step 2 - Data Layer ✅
- [x] Plan model + migration
- [x] Subscription model + migration
- [x] Payment model + migration
- [x] PlanSeeder with predefined plans
- [x] Unit tests for Plan, Subscription and Payment models

### Step 3 - Authentication Middleware ✅
- [x] Authentication delegated to API gateway (no JWT middleware needed)
- [x] user_id injected directly in request by the API gateway

### Step 4 - Controllers ✅
- [x] PlanController (index, show)
- [x] SubscriptionController (create, show, delete)
- [x] PaymentController (index, create)

### Step 5 - Business Logic ✅
- [x] Free plan: 30 days limit, non-renewable
- [x] Paid plan: payment required, activates after payment
- [x] Subscription status management (active, pending, expired, canceled)

### Step 6 - Payment Gateway ✅
- [x] PaymentGatewayInterface for provider abstraction
- [x] Stripe integration via PaymentIntents API
- [x] PayPal integration via Orders API (sandbox + prod)
- [x] PaymentGatewayFactory to switch provider via .env
- [ ] Handle payment webhooks

## Getting Started

### Requirements
- PHP 8.4+
- MySQL
- Composer

### Installation
```bash
composer install
cp .env.example .env  # configure your database credentials and payment gateway
php bin/migrate.php
php bin/seed.php      # insert predefined plans
```

### Generators
```bash
php bin/make-model.php ModelName          # creates model + migration
php bin/make-controller.php ControllerName # creates controller
```

### Tests
```bash
composer test                        # run all tests
php vendor/bin/phpunit tests/ModelTest.php  # run model tests only
```
