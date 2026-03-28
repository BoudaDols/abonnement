# API Documentation

## Overview

This API manages subscriptions (free and paid). It is designed to be used as an internal service called by an API gateway. Authentication is handled by the API gateway, which injects the `user_id` directly into requests.

All requests and responses use `application/json`.

---

## Base URL

```
http://your-domain/api
```

---

## Response Format

All responses return JSON. Successful responses return the requested data directly. Error responses follow this format:

```json
{
  "error": "Error message"
}
```

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 201  | Created |
| 404  | Not Found |
| 405  | Method Not Allowed |
| 422  | Unprocessable Entity (validation error) |

---

## Plans

### List all plans

Returns all active plans.

```
GET /api/plans
```

**Response 200**
```json
[
  {
    "id": 1,
    "name": "Free",
    "type": "free",
    "price": "0.00",
    "duration_days": 30,
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  {
    "id": 2,
    "name": "Basic",
    "type": "paid",
    "price": "4.99",
    "duration_days": 30,
    "is_active": true,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
]
```

---

### Get a plan

```
GET /api/plans/{id}
```

**Parameters**

| Name | Type | In | Description |
|------|------|----|-------------|
| id   | integer | path | Plan ID |

**Response 200**
```json
{
  "id": 1,
  "name": "Free",
  "type": "free",
  "price": "0.00",
  "duration_days": 30,
  "is_active": true,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

**Response 404**
```json
{
  "error": "Plan not found"
}
```

---

## Subscriptions

### Get a subscription

```
GET /api/subscriptions/{id}
```

**Parameters**

| Name | Type | In | Description |
|------|------|----|-------------|
| id   | integer | path | Subscription ID |

**Response 200**
```json
{
  "id": 1,
  "user_id": 42,
  "plan_id": 1,
  "status": "active",
  "starts_at": "2024-01-01T00:00:00.000000Z",
  "ends_at": "2024-01-31T00:00:00.000000Z",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "plan": {
    "id": 1,
    "name": "Free",
    "type": "free",
    "price": "0.00",
    "duration_days": 30
  }
}
```

**Response 404**
```json
{
  "error": "Subscription not found"
}
```

---

### Create a subscription

Subscribes a user to a plan.

- **Free plan**: subscription is immediately `active`, limited to 30 days, non-renewable
- **Paid plan**: subscription is set to `pending` until payment is processed

```
POST /api/subscriptions
```

**Request body**

| Name    | Type    | Required | Description |
|---------|---------|----------|-------------|
| user_id | integer | yes      | ID of the user (injected by API gateway) |
| plan_id | integer | yes      | ID of the plan to subscribe to |

```json
{
  "user_id": 42,
  "plan_id": 2
}
```

**Response 201**
```json
{
  "id": 1,
  "user_id": 42,
  "plan_id": 2,
  "status": "pending",
  "starts_at": "2024-01-01T00:00:00.000000Z",
  "ends_at": "2024-01-31T00:00:00.000000Z",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "plan": {
    "id": 2,
    "name": "Basic",
    "type": "paid",
    "price": "4.99",
    "duration_days": 30
  }
}
```

**Response 422 - Missing fields**
```json
{
  "error": "user_id and plan_id are required"
}
```

**Response 422 - Free plan already used**
```json
{
  "error": "Free plan is non-renewable"
}
```

**Response 422 - Already subscribed**
```json
{
  "error": "User already has an active subscription to this plan"
}
```

**Response 404**
```json
{
  "error": "Plan not found"
}
```

---

### Cancel a subscription

```
DELETE /api/subscriptions/{id}
```

**Parameters**

| Name | Type | In | Description |
|------|------|----|-------------|
| id   | integer | path | Subscription ID |

**Response 200**
```json
{
  "message": "Subscription canceled"
}
```

**Response 422 - Already canceled**
```json
{
  "error": "Subscription is already canceled"
}
```

**Response 404**
```json
{
  "error": "Subscription not found"
}
```

---

## Payments

### List payment history

Returns all payments for a given user.

```
GET /api/payments?user_id={user_id}
```

**Query parameters**

| Name    | Type    | Required | Description |
|---------|---------|----------|-------------|
| user_id | integer | yes      | ID of the user |

**Response 200**
```json
[
  {
    "id": 1,
    "subscription_id": 1,
    "amount": "4.99",
    "status": "paid",
    "transaction_id": "txn_123456",
    "paid_at": "2024-01-01T00:00:00.000000Z",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "subscription": {
      "id": 1,
      "user_id": 42,
      "plan_id": 2,
      "status": "active"
    }
  }
]
```

**Response 422**
```json
{
  "error": "user_id is required"
}
```

---

### Process a payment

Processes a payment for a pending subscription and activates it.

```
POST /api/payments
```

**Request body**

| Name            | Type    | Required | Description |
|-----------------|---------|----------|-------------|
| subscription_id | integer | yes      | ID of the subscription to pay for |
| amount          | decimal | yes      | Amount paid |
| transaction_id  | string  | yes      | Transaction ID from the payment gateway |

```json
{
  "subscription_id": 1,
  "amount": 4.99,
  "transaction_id": "txn_123456"
}
```

**Response 201**
```json
{
  "id": 1,
  "subscription_id": 1,
  "amount": "4.99",
  "status": "paid",
  "transaction_id": "txn_123456",
  "paid_at": "2024-01-01T00:00:00.000000Z",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

**Response 422 - Missing fields**
```json
{
  "error": "subscription_id, amount and transaction_id are required"
}
```

**Response 404**
```json
{
  "error": "Subscription not found"
}
```

---

## Subscription Status Flow

```
[POST /api/subscriptions]
        |
        |-- free plan  --> status: active  (immediately usable)
        |
        |-- paid plan  --> status: pending (waiting for payment)
                                |
                        [POST /api/payments]
                                |
                                └--> status: active (payment confirmed)
                                
[DELETE /api/subscriptions/{id}]
        |
        └--> status: canceled
```

---

## Predefined Plans

| Name    | Type | Price  | Duration |
|---------|------|--------|----------|
| Free    | free | $0.00  | 30 days  |
| Basic   | paid | $4.99  | 30 days  |
| Pro     | paid | $9.99  | 30 days  |
| Premium | paid | $19.99 | 365 days |
