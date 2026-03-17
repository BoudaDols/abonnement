# Abonnement

A lightweight PHP REST API to manage subscriptions (free and paid). Authentication is delegated to an external service and validated via JWT middleware.

## Description

This API allows to:
- Manage subscription plans (free and paid)
- Subscribe users to plans
- Handle payments for paid plans
- Track subscription status and history

**Free plan**: limited to 30 days, no payment required, non-renewable  
**Paid plan**: requires payment, renewable, custom duration

## Project Structure
```
src/
├── Controller/     # HTTP controllers
├── Model/          # Eloquent models
├── Migration/      # Database migrations
└── Service/        # Business logic services

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

### Step 3 - Authentication Middleware 🔲
- [ ] JWT middleware to validate tokens from external auth service
- [ ] Extract user ID from token and inject into request

### Step 4 - Controllers 🔲
- [ ] PlanController (index, show)
- [ ] SubscriptionController (create, show, delete)
- [ ] PaymentController (index, create)

### Step 5 - Business Logic 🔲
- [ ] Free plan: 30 days limit, non-renewable
- [ ] Paid plan: payment required, renewable
- [ ] Subscription status management (active, expired, canceled)

### Step 6 - Payment Gateway 🔲
- [ ] Integrate Stripe or PayPal
- [ ] Handle payment webhooks

## Getting Started

### Requirements
- PHP 8.4+
- MySQL
- Composer

### Installation
```bash
composer install
cp .env.example .env  # configure your database credentials
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
