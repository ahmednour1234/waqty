# Waqty — Admin API Documentation

**Base URL:** `{APP_URL}/api`  
**Authentication:** JWT Bearer Token — `Authorization: Bearer {token}`  
**Language:** `Accept-Language: ar | en`

---

## Common Response Format

All responses follow this JSON envelope:

```json
{
  "success": true,
  "message": "Optional translated message",
  "data": { } | [ ] | null,
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7
    }
  }
}
```

| HTTP Status | Meaning |
|-------------|---------|
| `200 OK` | Request succeeded |
| `201 Created` | Resource created |
| `401 Unauthorized` | Missing or invalid token |
| `403 Forbidden` | Account inactive / insufficient permissions |
| `404 Not Found` | Resource not found |
| `422 Unprocessable Entity` | Validation errors |
| `429 Too Many Requests` | Rate limit exceeded |
| `500 Server Error` | Unexpected server-side error |

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Admins](#2-admins)
3. [Categories](#3-categories)
4. [Subcategories](#4-subcategories)
5. [Countries](#5-countries)
6. [Cities](#6-cities)
7. [Governorates](#7-governorates)
8. [Providers](#8-providers)
9. [Provider Branches](#9-provider-branches)
10. [Employees](#10-employees)
11. [Services](#11-services)
12. [Shifts](#12-shifts)
13. [Service Pricing](#13-service-pricing)
14. [Bookings](#14-bookings)
15. [Payments](#15-payments)
16. [Users](#16-users)
17. [Ratings](#17-ratings)

---

## 1. Authentication

### `POST /admin/auth/send-verification-otp`

Send a verification OTP to the admin email.

> 🔓 Public — No authentication required  
> ⏱️ Throttle: 5 requests/minute

**Headers**

| Header | Value |
|--------|-------|
| `Accept-Language` | `ar` \| `en` |

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Admin email address |

**Responses**

| Code | Description |
|------|-------------|
| `200` | OTP sent (generic response to prevent enumeration) |
| `422` | Validation error |
| `429` | Too many requests |

---

### `POST /admin/auth/verify-email`

Verify the OTP and receive a JWT token.

> 🔓 Public  
> ⏱️ Throttle: 5 requests/minute

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Admin email address |
| `otp` | string | ✅ | One-time password received by email |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Verified — returns JWT token + admin object |
| `422` | Invalid OTP or validation error |
| `429` | Too many requests |

```json
{
  "success": true,
  "data": {
    "token": "<JWT_TOKEN>",
    "token_type": "Bearer",
    "expires_in": 3600,
    "admin": {
      "id": 1,
      "name": "Admin Name",
      "email": "admin@example.com",
      "active": true
    }
  }
}
```

---

### `POST /admin/auth/resend-verification-otp`

Resend the verification OTP.

> 🔓 Public  
> ⏱️ Throttle: 5 requests/minute

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Admin email address |

**Responses**

| Code | Description |
|------|-------------|
| `200` | OTP resent (generic) |
| `422` | Validation error |
| `429` | Too many requests |

---

### `POST /admin/auth/login`

Login with email and password — returns a JWT token.

> 🔓 Public

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Admin email (e.g. `admin@example.com`) |
| `password` | string | ✅ | Admin password |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Login successful — returns token + admin object |
| `401` | Invalid credentials |
| `403` | Account inactive |

```json
{
  "success": true,
  "data": {
    "token": "<JWT_TOKEN>",
    "token_type": "Bearer",
    "expires_in": 3600,
    "admin": { "id": 1, "name": "...", "email": "...", "active": true }
  }
}
```

---

### `POST /admin/auth/logout`

Invalidate the current JWT token.

> 🔒 Requires Authentication

**Responses**

| Code | Description |
|------|-------------|
| `200` | Logged out successfully |
| `401` | Unauthorized |

---

### `GET /admin/auth/me`

Get the currently authenticated admin.

> 🔒 Requires Authentication

**Responses**

| Code | Description |
|------|-------------|
| `200` | Returns admin object |
| `401` | Unauthorized |
| `403` | Account inactive |

---

## 2. Admins

> 🔒 All endpoints require authentication.

### `GET /admin/admins`

List all admin accounts with pagination.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search by name or email |
| `active` | boolean | No | Filter by active status |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Paginated list of admins |
| `401` | Unauthorized |
| `403` | Account inactive |

---

### `POST /admin/admins`

Create a new admin account.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Admin full name |
| `email` | string | ✅ | Unique email address |
| `password` | string | ✅ | Password (min 8 characters) |
| `active` | boolean | No | Active status (default `true`) |

**Responses**

| Code | Description |
|------|-------------|
| `201` | Admin created |
| `422` | Validation error |
| `401` | Unauthorized |

---

### `GET /admin/admins/{id}`

Get a single admin by ID.

**Responses**

| Code | Description |
|------|-------------|
| `200` | Admin object |
| `404` | Not found |
| `401` | Unauthorized |

---

### `PUT /admin/admins/{id}`

Update an admin account.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Admin full name |
| `email` | string | ✅ | Unique email address |
| `password` | string | No | New password (min 8 characters) |
| `active` | boolean | No | Active status |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Updated admin object |
| `422` | Validation error |
| `404` | Not found |
| `401` | Unauthorized |

---

### `PATCH /admin/admins/{id}/active`

Toggle the active status of an admin.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | ✅ | New active status |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Updated admin object |
| `422` | Validation error |
| `404` | Not found |

---

## 3. Categories

> 🔒 All endpoints require authentication.

### `GET /admin/categories`

List all categories.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search term |
| `active` | boolean | No | Filter by active status |
| `trashed` | string | No | Include soft-deleted: `only` \| `with` |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Paginated list of categories |
| `401` | Unauthorized |
| `403` | Account inactive |

---

### `POST /admin/categories`

Create a new category. Supports image upload.

> ℹ️ Use `multipart/form-data` when uploading an image. Allowed: jpeg/png/webp, max 2 MB.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name.ar` | string | ✅ | Category name in Arabic |
| `name.en` | string | ✅ | Category name in English |
| `active` | boolean | No | Active status |
| `sort_order` | integer | No | Sort order |
| `image` | file | No | Category image (jpeg/png/webp, max 2 MB) |

**Responses**

| Code | Description |
|------|-------------|
| `201` | Created — returns category UUID |
| `422` | Validation error |
| `401` | Unauthorized |

---

### `GET /admin/categories/{uuid}`

Get a single category with its subcategories.

**Responses**

| Code | Description |
|------|-------------|
| `200` | Category object with subcategories |
| `404` | Not found |

---

### `PUT /admin/categories/{uuid}`

Update a category.

> ℹ️ Use `multipart/form-data` when uploading a new image.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name.ar` | string | No | Category name in Arabic |
| `name.en` | string | No | Category name in English |
| `active` | boolean | No | Active status |
| `sort_order` | integer | No | Sort order |
| `image` | file | No | New category image |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Updated category |
| `422` | Validation error |
| `404` | Not found |

---

### `DELETE /admin/categories/{uuid}`

Soft-delete a category.

**Responses:** `200` Deleted | `404` Not found

---

### `PATCH /admin/categories/{uuid}/active`

Toggle category active status.

**Body:** `active` (boolean, required)

**Responses:** `200` Updated | `422` Validation error | `404` Not found

---

### `POST /admin/categories/{uuid}/restore`

Restore a soft-deleted category.

**Responses:** `200` Restored | `404` Not found

---

### `DELETE /admin/categories/{uuid}/force`

Permanently delete a category (irreversible).

**Responses:** `200` Permanently deleted | `404` Not found

---

## 4. Subcategories

> 🔒 All endpoints require authentication.  
> Same pattern as [Categories](#3-categories). Replace `/admin/categories` with `/admin/subcategories`.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/subcategories` | List subcategories (filters: `search`, `active`, `trashed`, `per_page`) |
| `POST` | `/admin/subcategories` | Create — body: `name.ar`, `name.en`, `category_uuid` ✅, `active`, `sort_order`, `image` |
| `GET` | `/admin/subcategories/{uuid}` | Get subcategory by UUID |
| `PUT` | `/admin/subcategories/{uuid}` | Update subcategory |
| `DELETE` | `/admin/subcategories/{uuid}` | Soft-delete |
| `PATCH` | `/admin/subcategories/{uuid}/active` | Toggle active — body: `active` (boolean, required) |
| `POST` | `/admin/subcategories/{uuid}/restore` | Restore soft-deleted |
| `DELETE` | `/admin/subcategories/{uuid}/force` | Permanently delete |

---

## 5. Countries

> 🔒 All endpoints require authentication.

### `GET /admin/countries`

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search term |
| `active` | boolean | No | Filter by active status |
| `trashed` | string | No | `only` \| `with` |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `POST /admin/countries`

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name.ar` | string | ✅ | Country name in Arabic |
| `name.en` | string | ✅ | Country name in English |
| `iso2` | string | No | ISO2 code (2 chars) |
| `phone_code` | string | No | Phone dialing code |
| `active` | boolean | No | Active status |
| `sort_order` | integer | No | Sort order |

**Responses:** `201` Created | `422` Validation error

---

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/countries/{uuid}` | Get country with its cities |
| `PUT` | `/admin/countries/{uuid}` | Update (same fields as POST, all optional) |
| `DELETE` | `/admin/countries/{uuid}` | Soft-delete |
| `PATCH` | `/admin/countries/{uuid}/active` | Toggle active — body: `active` (boolean, required) |
| `POST` | `/admin/countries/{uuid}/restore` | Restore soft-deleted |
| `DELETE` | `/admin/countries/{uuid}/force` | Permanently delete |

---

## 6. Cities

> 🔒 All endpoints require authentication.  
> Same pattern as Countries. Replace `/admin/countries` with `/admin/cities`.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/cities` | List cities (filters: `search`, `active`, `country_uuid`, `trashed`, `per_page`) |
| `POST` | `/admin/cities` | Create — body: `name.ar`, `name.en`, `country_uuid` ✅, `active`, `sort_order` |
| `GET` | `/admin/cities/{uuid}` | Get city by UUID |
| `PUT` | `/admin/cities/{uuid}` | Update city |
| `DELETE` | `/admin/cities/{uuid}` | Soft-delete |
| `PATCH` | `/admin/cities/{uuid}/active` | Toggle active |
| `POST` | `/admin/cities/{uuid}/restore` | Restore |
| `DELETE` | `/admin/cities/{uuid}/force` | Permanently delete |

---

## 7. Governorates

> 🔒 All endpoints require authentication.  
> Same pattern as Cities. Replace `/admin/cities` with `/admin/governorates`.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/governorates` | List governorates (filters: `search`, `active`, `country_uuid`, `trashed`, `per_page`) |
| `POST` | `/admin/governorates` | Create — body: `name.ar`, `name.en`, `country_uuid` ✅, `active`, `sort_order` |
| `GET` | `/admin/governorates/{uuid}` | Get governorate by UUID |
| `PUT` | `/admin/governorates/{uuid}` | Update |
| `DELETE` | `/admin/governorates/{uuid}` | Soft-delete |
| `PATCH` | `/admin/governorates/{uuid}/active` | Toggle active |
| `POST` | `/admin/governorates/{uuid}/restore` | Restore |
| `DELETE` | `/admin/governorates/{uuid}/force` | Permanently delete |

---

## 8. Providers

> 🔒 All endpoints require authentication.

### `GET /admin/providers`

List all providers.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search term |
| `active` | boolean | No | Filter by active status |
| `blocked` | boolean | No | Filter blocked providers |
| `banned` | boolean | No | Filter banned providers |
| `country_id` | integer | No | Filter by country ID |
| `city_id` | integer | No | Filter by city ID |
| `category_id` | integer | No | Filter by category ID |
| `trashed` | boolean | No | Include soft-deleted |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/providers/{uuid}`

Get a single provider.

**Responses:** `200` Provider object | `404` Not found

---

### `PATCH /admin/providers/{uuid}/active`

Toggle provider active status.

**Body:** `active` (boolean, required)

**Responses:** `200` Updated

---

### `PATCH /admin/providers/{uuid}/block`

Block or unblock a provider.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `blocked` | boolean | ✅ | Block status |
| `reason` | string | No | Reason for blocking |

**Responses:** `200` Updated

---

### `PATCH /admin/providers/{uuid}/ban`

Ban or unban a provider.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `banned` | boolean | ✅ | Ban status |
| `reason` | string | No | Reason for banning |

**Responses:** `200` Updated

---

| Method | Endpoint | Description |
|--------|----------|-------------|
| `DELETE` | `/admin/providers/{uuid}` | Soft-delete a provider |
| `POST` | `/admin/providers/{uuid}/restore` | Restore soft-deleted provider |
| `DELETE` | `/admin/providers/{uuid}/force` | Permanently delete provider |

---

## 9. Provider Branches

> 🔒 All endpoints require authentication.

### `GET /admin/provider-branches`

List all provider branches.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `provider_uuid` | string | No | Filter by provider UUID |
| `country_uuid` | string | No | Filter by country UUID |
| `city_uuid` | string | No | Filter by city UUID |
| `category_uuid` | string | No | Filter by category UUID |
| `active` | boolean | No | Filter by active status |
| `blocked` | boolean | No | Filter blocked branches |
| `banned` | boolean | No | Filter banned branches |
| `is_main` | boolean | No | Filter main branches |
| `search` | string | No | Search term |
| `trashed` | string | No | `only` \| `with` |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/provider-branches/{uuid}`

Get a single provider branch.

**Responses:** `200` Branch object | `404` Not found

---

### `PATCH /admin/provider-branches/{uuid}/status`

Update branch status.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | No | Active status |
| `blocked` | boolean | No | Blocked status |
| `banned` | boolean | No | Banned status |

**Responses:** `200` Updated branch

---

| Method | Endpoint | Description |
|--------|----------|-------------|
| `DELETE` | `/admin/provider-branches/{uuid}` | Soft-delete branch |
| `POST` | `/admin/provider-branches/{uuid}/restore` | Restore soft-deleted branch |

---

## 10. Employees

> 🔒 All endpoints require authentication.

### `GET /admin/employees`

List all employees.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `provider_uuid` | string | No | Filter by provider UUID |
| `branch_uuid` | string | No | Filter by branch UUID |
| `active` | boolean | No | Filter by active status |
| `blocked` | boolean | No | Filter blocked employees |
| `search` | string | No | Search term |
| `trashed` | string | No | `only` \| `with` |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/employees/{uuid}`

Get a single employee (includes provider and branch info).

**Responses:** `200` Employee object | `404` Not found

---

### `PATCH /admin/employees/{uuid}/status`

Update employee status.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | No | Active status |
| `blocked` | boolean | No | Blocked status |

**Responses:** `200` Updated employee

---

| Method | Endpoint | Description |
|--------|----------|-------------|
| `DELETE` | `/admin/employees/{uuid}` | Soft-delete employee |
| `POST` | `/admin/employees/{uuid}/restore` | Restore soft-deleted employee |

---

## 11. Services

> 🔒 All endpoints require authentication.

### `GET /admin/services`

List all services across providers.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `provider_uuid` | string | No | Filter by provider UUID |
| `sub_category_uuid` | string | No | Filter by subcategory UUID |
| `category` | string | No | Filter by category name (e.g. `Hair`, `Skin`) |
| `active` | boolean | No | Filter by active status |
| `trashed` | string | No | `only` \| `with` |
| `search` | string | No | Search in name/description (ar/en) |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/services/{uuid}`

Get a single service.

**Responses:** `200` Service object | `404` Not found

---

### `PATCH /admin/services/{uuid}/status`

Update service status.

**Body:** `active` (boolean)

**Responses:** `200` Updated service

---

| Method | Endpoint | Description |
|--------|----------|-------------|
| `DELETE` | `/admin/services/{uuid}` | Soft-delete service |
| `POST` | `/admin/services/{uuid}/restore` | Restore soft-deleted service |

---

## 12. Shifts

> 🔒 All endpoints are read-only for admin.

### `GET /admin/shifts`

List all shifts.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `provider_uuid` | string | No | Filter by provider UUID |
| `branch_uuid` | string | No | Filter by branch UUID |
| `employee_uuid` | string | No | Filter by employee UUID |
| `date` | string | No | Filter by date (`YYYY-MM-DD`) |
| `shift_template_uuid` | string | No | Filter by shift template UUID |
| `active` | boolean | No | Filter by active status |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/shifts/{uuid}`

Get a single shift.

**Responses:** `200` Shift object | `404` Not found

---

### Shift Templates

### `GET /admin/shift-templates`

List all shift templates.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `provider_uuid` | string | No | Filter by provider UUID |
| `active` | boolean | No | Filter by active status |
| `search` | string | No | Search by name |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/shift-templates/{uuid}`

Get a single shift template.

**Responses:** `200` Shift template object | `404` Not found

---

## 13. Service Pricing

> 🔒 All endpoints are read-only for admin.

### `GET /admin/service-prices`

List all service pricing rules.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `provider_uuid` | string | No | Filter by provider UUID |
| `service_uuid` | string | No | Filter by service UUID |
| `sub_category_uuid` | string | No | Filter by subcategory UUID |
| `scope_type` | string | No | Filter by scope: `branch` \| `employee` |
| `branch_uuid` | string | No | Filter by branch UUID |
| `employee_uuid` | string | No | Filter by employee UUID |
| `pricing_group_uuid` | string | No | Filter by pricing group UUID |
| `active` | boolean | No | Filter by active status |
| `trashed` | string | No | `only` \| `with` |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/service-prices/{uuid}`

Get a single pricing rule (includes provider, service, branch, employee, group).

**Responses:** `200` Pricing rule object | `404` Not found

---

### Pricing Groups

### `GET /admin/pricing-groups`

List all pricing groups.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `provider_uuid` | string | No | Filter by provider UUID |
| `active` | boolean | No | Filter by active status |
| `trashed` | string | No | `only` \| `with` |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/pricing-groups/{uuid}`

Get a single pricing group with its employees.

**Responses:** `200` Pricing group object | `404` Not found

---

## 14. Bookings

> 🔒 All endpoints require authentication.

### `GET /admin/bookings`

List all bookings.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | `pending` \| `confirmed` \| `completed` \| `cancelled` \| `no_show` |
| `user_uuid` | string | No | Filter by user UUID |
| `provider_uuid` | string | No | Filter by provider UUID |
| `branch_uuid` | string | No | Filter by branch UUID |
| `employee_uuid` | string | No | Filter by employee UUID |
| `booking_date` | string | No | Exact date filter (`YYYY-MM-DD`) |
| `from_date` | string | No | Bookings on/after this date (`YYYY-MM-DD`) |
| `to_date` | string | No | Bookings on/before this date (`YYYY-MM-DD`) |
| `trashed` | string | No | `only` \| `with` |
| `per_page` | integer | No | Items per page (1–100, default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/bookings/next-upcoming`

Get the next upcoming confirmed booking.

**Responses:** `200` Booking object or `null` if none upcoming

---

### `GET /admin/bookings/{uuid}`

Get a single booking.

**Responses:** `200` Booking object | `404` Not found

---

### `PATCH /admin/bookings/{uuid}/status`

Update booking status.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `status` | string | ✅ | `pending` \| `confirmed` \| `completed` \| `cancelled` \| `no_show` |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Updated booking |
| `422` | Invalid status value |
| `404` | Not found |

---

### `DELETE /admin/bookings/{uuid}`

Delete a booking.

**Responses:** `200` Deleted | `404` Not found

---

## 15. Payments

> 🔒 All endpoints require authentication.

### `GET /admin/payments`

List all payments.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `payment_method` | string | No | `cash` \| `paymob` |
| `status` | string | No | `pending` \| `completed` \| `failed` \| `refunded` |
| `booking_uuid` | string | No | Filter by booking UUID |
| `provider_uuid` | string | No | Filter by provider UUID |
| `from_date` | string | No | Start date (`YYYY-MM-DD`) |
| `to_date` | string | No | End date (`YYYY-MM-DD`) |
| `trashed` | string | No | `only` \| `with` |
| `per_page` | integer | No | Items per page (1–100, default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /admin/payments/{uuid}`

Get a single payment.

**Responses:** `200` Payment object | `404` Not found

---

### `PUT /admin/payments/{uuid}`

Update a payment record.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `payment_method` | string | No | `cash` \| `paymob` |
| `amount` | number | No | Payment amount (≥ 0) |
| `status` | string | No | `pending` \| `completed` \| `failed` \| `refunded` |
| `transaction_id` | string | No | External transaction reference |
| `notes` | string | No | Optional notes (max 1000 chars) |

**Responses**

| Code | Description |
|------|-------------|
| `200` | Updated payment |
| `422` | Validation error |
| `404` | Not found |

---

### `DELETE /admin/payments/{uuid}`

Delete a payment record.

**Responses:** `200` Deleted | `404` Not found

---

## 16. Users

> 🔒 All endpoints require authentication.

### `GET /admin/users`

List all end-users with optional filtering.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search by name, email, or phone |
| `active` | boolean | No | Filter by active status |
| `blocked` | boolean | No | Filter by blocked status |
| `banned` | boolean | No | Filter by banned status |
| `gender` | string | No | Filter by gender: `male` \| `female` |
| `trashed` | string | No | `only` \| `with` — include soft-deleted records |
| `per_page` | integer | No | Items per page (default `15`) |

**Response `200`**

```json
{
  "success": true,
  "data": [
    {
      "uuid": "<ULID>",
      "name": "User Name",
      "email": "user@example.com",
      "phone": "+966500000000",
      "gender": "male",
      "date_birth": "1995-06-15",
      "active": true,
      "blocked": false,
      "banned": false,
      "email_verified_at": "2024-01-01T00:00:00Z",
      "avatar_url": null,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z",
      "deleted_at": null
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7
    }
  }
}
```

**Responses:** `200` Paginated list | `401` Unauthorized | `403` Forbidden

---

### `GET /admin/users/{uuid}`

Get details for a single end-user.

**Response `200`**

```json
{
  "success": true,
  "data": {
    "uuid": "<ULID>",
    "name": "User Name",
    "email": "user@example.com",
    "phone": "+966500000000",
    "gender": "male",
    "date_birth": "1995-06-15",
    "active": true,
    "blocked": false,
    "banned": false,
    "email_verified_at": "2024-01-01T00:00:00Z",
    "avatar_url": null,
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-01T00:00:00Z",
    "deleted_at": null
  }
}
```

**Responses:** `200` User object | `404` Not found | `401` Unauthorized

---

### `PATCH /admin/users/{uuid}/active`

Activate or deactivate a user account.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | Yes | `true` to activate, `false` to deactivate |

**Responses:** `200` Updated user | `422` Validation error | `404` Not found

---

### `PATCH /admin/users/{uuid}/block`

Block or unblock a user.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `blocked` | boolean | Yes | `true` to block, `false` to unblock |

**Responses:** `200` Updated user | `422` Validation error | `404` Not found

---

### `PATCH /admin/users/{uuid}/ban`

Ban or unban a user.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `banned` | boolean | Yes | `true` to ban, `false` to remove ban |

**Responses:** `200` Updated user | `422` Validation error | `404` Not found

---

### `DELETE /admin/users/{uuid}`

Soft-delete a user account.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

---

### `POST /admin/users/{uuid}/restore`

Restore a previously soft-deleted user account.

**Responses:** `200` Restored user | `404` Not found | `401` Unauthorized

---

## 17. Ratings

> 🔒 All endpoints require authentication.

### `GET /admin/ratings/stats`

Get aggregated statistics for all ratings.

**Response `200`**

```json
{
  "success": true,
  "data": {
    "total": 10,
    "published": 6,
    "hidden": 2,
    "avg_rating": 4.2
  }
}
```

**Responses:** `200` Stats object | `401` Unauthorized | `403` Forbidden

---

### `GET /admin/ratings/analytics`

Full analytics for the Review Analytics dashboard — includes summary stats, per-star rating distribution, and per-provider breakdown.

**Response `200`**

```json
{
  "success": true,
  "data": {
    "summary": {
      "total": 10,
      "avg_rating": 3.4,
      "published": 6,
      "hidden": 2,
      "response_rate": 10.0
    },
    "rating_distribution": [
      { "stars": 1, "count": 1 },
      { "stars": 2, "count": 1 },
      { "stars": 3, "count": 1 },
      { "stars": 4, "count": 2 },
      { "stars": 5, "count": 4 }
    ],
    "by_provider": [
      { "provider_uuid": "<ULID>", "provider_name": "Glamour Studio", "total": 2, "avg_rating": 4.5 },
      { "provider_uuid": "<ULID>", "provider_name": "Elite Barbers",  "total": 2, "avg_rating": 2.0 }
    ]
  }
}
```

| Field | Description |
|-------|-------------|
| `summary.total` | All non-deleted ratings |
| `summary.avg_rating` | Average across all non-deleted ratings |
| `summary.published` | Ratings with `active = true` |
| `summary.hidden` | Ratings with `active = false` |
| `summary.response_rate` | `(total ratings / completed bookings) × 100` |
| `rating_distribution` | Count of ratings for each star value (1–5) |
| `by_provider` | Per-provider total count and average rating, ordered by volume |

**Responses:** `200` Analytics object | `401` Unauthorized | `403` Forbidden

---

### `GET /admin/ratings`

List all ratings with optional filtering.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search in comment, user name, or email |
| `active` | boolean | No | `true` = published, `false` = hidden |
| `rating` | integer | No | Filter by star rating `1`–`5` |
| `provider_uuid` | string | No | Filter by provider UUID |
| `trashed` | string | No | Pass `only` to list soft-deleted ratings |
| `per_page` | integer | No | Items per page (default `15`) |

**Response `200`**

```json
{
  "success": true,
  "data": [
    {
      "uuid": "<ULID>",
      "rating": 5,
      "comment": "Amazing experience!",
      "active": true,
      "user": {
        "uuid": "<ULID>",
        "name": "Ahmed Hassan",
        "email": "ahmed@example.com",
        "phone": "+201000000001"
      },
      "booking": {
        "uuid": "<ULID>",
        "booking_date": "2026-04-12",
        "provider": { "uuid": "<ULID>", "name": "Test Provider" },
        "branch": { "uuid": "<ULID>", "name": "Main Branch" }
      },
      "created_at": "2026-04-12T10:30:00Z",
      "updated_at": "2026-04-12T10:30:00Z",
      "deleted_at": null
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 10,
      "last_page": 1
    }
  }
}
```

**Responses:** `200` Paginated list | `401` Unauthorized | `403` Forbidden

---

### `GET /admin/ratings/{uuid}`

Get a single rating with full details.

**Responses:** `200` Rating object | `404` Not found | `401` Unauthorized

---

### `PATCH /admin/ratings/{uuid}/active`

Publish or hide a rating.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | Yes | `true` = publish, `false` = hide |

**Responses:** `200` Updated rating | `400` Missing field | `404` Not found | `401` Unauthorized

---

### `DELETE /admin/ratings/{uuid}`

Soft-delete a rating.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

---

## 18. Content Pages

Static content pages (Terms & Conditions, Privacy Policy, FAQ, About). Admin creates pages once and updates them as needed.

> 🔒 All endpoints require authentication.

### `GET /admin/pages`

List all content pages (no pagination — fixed set of pages).

**Response `200`**

```json
{
  "success": true,
  "data": [
    {
      "uuid": "<ULID>",
      "slug": "terms-conditions",
      "title_en": "Terms & Conditions",
      "title_ar": "الشروط والأحكام",
      "content_en": null,
      "content_ar": null,
      "active": true,
      "updated_by": { "uuid": "<ULID>", "name": "Platform Admin" },
      "created_at": "2026-01-01T00:00:00Z",
      "updated_at": "2026-04-01T00:00:00Z"
    }
  ]
}
```

**Responses:** `200` Page list | `401` Unauthorized | `403` Forbidden

---

### `GET /admin/pages/{uuid}`

Get a single content page with full content.

**Responses:** `200` Page object | `404` Not found | `401` Unauthorized

---

### `POST /admin/pages`

Create a new content page.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `slug` | string | ✅ | URL-friendly slug — lowercase letters, numbers, hyphens only (e.g. `terms-conditions`) |
| `title_en` | string | ✅ | Page title in English |
| `title_ar` | string | ✅ | Page title in Arabic |
| `content_en` | string | No | Full page content in English |
| `content_ar` | string | No | Full page content in Arabic |
| `active` | boolean | No | Publish immediately (default `true`) |

**Responses**

| Code | Description |
|------|-------------|
| `201` | Created page object |
| `422` | Validation error / slug already exists |
| `401` | Unauthorized |

---

### `PUT /admin/pages/{uuid}`

Update an existing content page. All fields are optional — only provided fields are updated.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `slug` | string | No | New slug (must be unique) |
| `title_en` | string | No | Page title in English |
| `title_ar` | string | No | Page title in Arabic |
| `content_en` | string | No | Full page content in English |
| `content_ar` | string | No | Full page content in Arabic |

**Responses:** `200` Updated page | `422` Slug conflict | `404` Not found | `401` Unauthorized

---

### `PATCH /admin/pages/{uuid}/active`

Publish or hide a content page.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | ✅ | `true` = published, `false` = hidden |

**Responses:** `200` Updated page | `400` Missing field | `404` Not found | `401` Unauthorized

---

## 19. Announcements

Platform-wide announcements sent to specific audiences. Admin creates and manages them; they expire automatically after `ends_at`.

> 🔒 All endpoints require authentication.

### `GET /admin/announcements`

List all announcements with optional filtering.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search in title or message (EN/AR) |
| `active` | boolean | No | Filter by active status |
| `target` | string | No | `all` \| `users` \| `providers` \| `employees` \| `branches` |
| `priority` | string | No | `low` \| `normal` \| `high` \| `urgent` |
| `trashed` | string | No | Pass `only` to list soft-deleted |
| `per_page` | integer | No | Items per page (default `15`) |

**Response `200`**

```json
{
  "success": true,
  "data": [
    {
      "uuid": "<ULID>",
      "title_en": "Scheduled Maintenance",
      "title_ar": "صيانة مجدولة",
      "message_en": "We will be performing maintenance on April 30 from 2–4 AM.",
      "message_ar": "سيتم إجراء صيانة في 30 أبريل من الساعة 2 إلى 4 صباحاً.",
      "target": "all",
      "priority": "high",
      "active": true,
      "ends_at": "2026-04-30T00:00:00Z",
      "created_by": { "uuid": "<ULID>", "name": "Platform Admin" },
      "created_at": "2026-04-01T00:00:00Z",
      "updated_at": "2026-04-01T00:00:00Z",
      "deleted_at": null
    }
  ],
  "meta": {
    "pagination": { "current_page": 1, "per_page": 15, "total": 3, "last_page": 1 }
  }
}
```

**Responses:** `200` Paginated list | `401` Unauthorized | `403` Forbidden

---

### `GET /admin/announcements/{uuid}`

Get a single announcement.

**Responses:** `200` Announcement object | `404` Not found | `401` Unauthorized

---

### `POST /admin/announcements`

Create a new announcement and publish it immediately (or keep it hidden).

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title_en` | string | ✅ | Title in English |
| `title_ar` | string | ✅ | Title in Arabic |
| `message_en` | string | ✅ | Full message in English |
| `message_ar` | string | ✅ | Full message in Arabic |
| `target` | string | No | `all` \| `users` \| `providers` \| `employees` \| `branches` (default: `all`) |
| `priority` | string | No | `low` \| `normal` \| `high` \| `urgent` (default: `normal`) |
| `active` | boolean | No | Publish immediately (default: `true`) |
| `ends_at` | string | No | Expiry date/time ISO 8601 (must be in the future) |

**Responses**

| Code | Description |
|------|-------------|
| `201` | Created announcement object |
| `422` | Validation error |
| `401` | Unauthorized |

---

### `PUT /admin/announcements/{uuid}`

Update an existing announcement. All fields are optional.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title_en` | string | No | Title in English |
| `title_ar` | string | No | Title in Arabic |
| `message_en` | string | No | Full message in English |
| `message_ar` | string | No | Full message in Arabic |
| `target` | string | No | `all` \| `users` \| `providers` \| `employees` \| `branches` |
| `priority` | string | No | `low` \| `normal` \| `high` \| `urgent` |
| `ends_at` | string | No | New expiry date/time, or `null` to clear |

**Responses:** `200` Updated announcement | `422` Validation error | `404` Not found | `401` Unauthorized

---

### `PATCH /admin/announcements/{uuid}/active`

Publish or hide an announcement.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | ✅ | `true` = publish, `false` = hide |

**Responses:** `200` Updated announcement | `400` Missing field | `404` Not found | `401` Unauthorized

---

### `DELETE /admin/announcements/{uuid}`

Soft-delete an announcement.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

