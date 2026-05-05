# Waqty — Provider API Documentation

**Base URL:** `{APP_URL}/api`  
**Authentication:** JWT Bearer Token — `Authorization: Bearer {token}`  
**Language:** `Accept-Language: ar | en`  
**Provider guard:** `auth:provider` + middleware `provider.active`

---

## Common Response Format

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
| `400 Bad Request` | Invalid argument / file error |
| `401 Unauthorized` | Missing or invalid token |
| `403 Forbidden` | Account inactive / blocked / banned |
| `404 Not Found` | Resource not found |
| `422 Unprocessable Entity` | Validation errors |
| `429 Too Many Requests` | Rate limit exceeded |
| `500 Server Error` | Unexpected server-side error |

---

## Table of Contents

1. [Authentication](#1-authentication)
2. [Profile](#2-profile)
3. [Branches](#3-branches)
4. [Employees](#4-employees)
5. [Services](#5-services)
6. [Service Pricing](#6-service-pricing)
7. [Pricing Groups](#7-pricing-groups)
8. [Shift Templates](#8-shift-templates)
9. [Shifts](#9-shifts)
10. [Bookings](#10-bookings)
11. [Payments](#11-payments)
12. [Attendance](#12-attendance)
13. [Ratings](#13-ratings)
14. [Revenue](#14-revenue)
15. [Availability](#15-availability)

---

## 1. Authentication

### `POST /provider/auth/register`

Register a new provider account. A verification OTP is sent to the provided email.

> 🔓 Public

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Provider full name |
| `email` | string | ✅ | Unique email address |
| `password` | string | ✅ | Password (min 8 characters) |
| `phone` | string | ✅ | Phone number (e.g. `+201234567890`) |
| `category_uuid` | string | ✅ | Business category UUID |
| `city_uuid` | string | ✅ | City UUID |

**Response `201`**

```json
{
  "success": true,
  "message": "تم التسجيل بنجاح",
  "data": {
    "message": "Registration successful",
    "email": "provider@example.com"
  }
}
```

**Responses:** `201` Registered | `422` Validation error

---

### `POST /provider/auth/verify-email`

Verify the email using the OTP sent at registration. Returns a JWT token on success.

> 🔓 Public

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Provider email address |
| `otp` | string | ✅ | 6-digit OTP received by email |

**Response `200`**

```json
{
  "success": true,
  "data": {
    "token": "<JWT_TOKEN>",
    "token_type": "Bearer",
    "expires_in": 3600,
    "provider": { "uuid": "<ULID>", "name": "Provider Name" }
  }
}
```

**Responses:** `200` Verified + token | `400` Invalid OTP | `422` Validation error

---

### `POST /provider/auth/resend-verification-otp`

Resend the email verification OTP.

> 🔓 Public  
> ⏱️ Rate limited

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Provider email address |

**Responses:** `200` OTP resent (generic) | `422` Validation error | `429` Rate limited

---

### `POST /provider/auth/login`

Login with email and password.

> 🔓 Public

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Provider email address |
| `password` | string | ✅ | Provider password |

**Response `200`**

```json
{
  "success": true,
  "data": {
    "token": "<JWT_TOKEN>",
    "token_type": "Bearer",
    "expires_in": 3600,
    "provider": { "uuid": "<ULID>", "name": "Provider Name" }
  }
}
```

**Responses:** `200` Token | `401` Invalid credentials | `403` Account inactive / blocked / banned

---

### `POST /provider/auth/logout`

Invalidate the current JWT token.

> 🔒 Requires Authentication

**Responses:** `200` Logged out | `401` Unauthorized

---

### `GET /provider/auth/me`

Get the currently authenticated provider.

> 🔒 Requires Authentication

**Responses:** `200` Provider object | `401` Unauthorized | `403` Account inactive

---

### `POST /provider/auth/send-otp`

Request a password-reset OTP. Always returns a generic success response to prevent email enumeration.

> 🔓 Public  
> ⏱️ Rate limited

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Provider email address |

**Responses:** `200` Generic success | `429` Rate limited

---

### `POST /provider/auth/verify-otp`

Verify the password-reset OTP. Returns a short-lived token for use in the reset step.

> 🔓 Public

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Provider email address |
| `otp` | string | ✅ | 6-digit OTP |

**Response `200`**

```json
{
  "success": true,
  "data": {
    "token": "<JWT_TOKEN>",
    "token_type": "Bearer",
    "expires_in": 3600,
    "provider": { "uuid": "<ULID>", "name": "Provider Name" }
  }
}
```

**Responses:** `200` OTP valid + token | `400` Invalid / expired OTP | `422` Validation error

---

### `POST /provider/auth/reset-password`

Reset the provider's password using the verified OTP.

> 🔓 Public

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | ✅ | Provider email address |
| `otp` | string | ✅ | 6-digit OTP |
| `new_password` | string | ✅ | New password (min 8 characters) |

**Responses:** `200` Password reset | `400` Invalid / expired OTP | `422` Validation error | `429` Rate limited

---

## 2. Profile

### `PUT /provider/profile`

Update provider profile information and/or logo.

> 🔒 Requires Authentication  
> ℹ️ Send as `multipart/form-data` when uploading a logo.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Provider display name |
| `phone` | string | No | Phone number |
| `category_id` | integer | No | Business category ID |
| `city_id` | integer | ✅ | City ID |
| `logo` | file | No | Logo image (jpeg/png/webp, max 2 MB — SVG rejected) |

**Response `200`**

```json
{
  "success": true,
  "message": "تم التحديث بنجاح",
  "data": { "uuid": "<ULID>", "name": "Provider Name", "logo_url": "https://..." }
}
```

**Responses:** `200` Updated profile | `400` Invalid file type / size | `422` Validation error | `401` Unauthorized | `403` Forbidden

---

## 3. Branches

> 🔒 All endpoints require authentication.

### `GET /provider/branches`

List all branches belonging to the authenticated provider.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search by branch name or address |
| `active` | boolean | No | Filter by active status |
| `is_main` | boolean | No | Filter main branch only |
| `city_uuid` | string | No | Filter by city UUID |
| `country_uuid` | string | No | Filter by country UUID |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `POST /provider/branches`

Create a new branch.

> ℹ️ Send as `multipart/form-data` when uploading a logo.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Branch name |
| `address` | string | ✅ | Branch address |
| `city_uuid` | string | ✅ | City UUID |
| `country_uuid` | string | ✅ | Country UUID |
| `phone` | string | No | Branch phone number |
| `active` | boolean | No | Active status (default `true`) |
| `logo` | file | No | Branch logo (jpeg/png/webp, max 2 MB) |

**Responses:** `201` Created branch | `400` Invalid file | `422` Validation error | `401` Unauthorized

---

### `GET /provider/branches/{uuid}`

Get a single branch.

**Responses:** `200` Branch object | `404` Not found | `401` Unauthorized

---

### `PUT /provider/branches/{uuid}`

Update a branch.

> ℹ️ Send as `multipart/form-data` when replacing the logo.

**Body** — All fields optional (same fields as POST)

**Responses:** `200` Updated branch | `400` Invalid file | `422` Validation error | `404` Not found | `401` Unauthorized

---

### `DELETE /provider/branches/{uuid}`

Delete a branch.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

---

### `PATCH /provider/branches/{uuid}/main`

Set a branch as the main branch. Automatically unsets the previous main branch.

**Responses:** `200` Updated branch | `404` Not found | `401` Unauthorized

---

### `PATCH /provider/branches/{uuid}/active`

Toggle branch active status.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | ✅ | `true` = enable, `false` = disable |

**Responses:** `200` Updated branch | `404` Not found | `401` Unauthorized

---

## 4. Employees

> 🔒 All endpoints require authentication.

### `GET /provider/employees`

List all employees belonging to the authenticated provider.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search by employee name or email |
| `branch_uuid` | string | No | Filter by branch UUID |
| `active` | boolean | No | Filter by active status |
| `blocked` | boolean | No | Filter blocked employees |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `POST /provider/employees`

Create a new employee under the authenticated provider.

> ℹ️ Send as `multipart/form-data` when uploading a photo.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Employee full name |
| `email` | string | ✅ | Unique email address |
| `phone` | string | No | Phone number |
| `branch_uuid` | string | ✅ | Branch UUID the employee belongs to |
| `active` | boolean | No | Active status (default `true`) |
| `logo` | file | No | Employee photo (jpeg/png/webp, max 2 MB) |

**Responses:** `201` Created employee | `400` Invalid file | `422` Validation error | `401` Unauthorized

---

### `GET /provider/employees/{uuid}`

Get a single employee with branch info.

**Responses:** `200` Employee object | `404` Not found | `401` Unauthorized

---

### `PUT /provider/employees/{uuid}`

Update an employee.

> ℹ️ Send as `multipart/form-data` when replacing the photo.

**Body** — All fields optional (same fields as POST)

**Responses:** `200` Updated employee | `400` Invalid file | `422` Validation error | `404` Not found | `401` Unauthorized

---

### `DELETE /provider/employees/{uuid}`

Delete an employee.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

---

### `PATCH /provider/employees/{uuid}/active`

Toggle employee active status.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | ✅ | `true` = activate, `false` = deactivate |

**Responses:** `200` Updated employee | `404` Not found | `401` Unauthorized

---

### `PATCH /provider/employees/{uuid}/block`

Block or unblock an employee.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `blocked` | boolean | ✅ | `true` = block, `false` = unblock |

**Responses:** `200` Updated employee | `404` Not found | `401` Unauthorized

---

### `GET /provider/employees/booking-counts`

Get booking counts per employee with optional date and branch filtering.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `branch_uuid` | string | No | Scope to a specific branch |
| `start_date` | string | No | Start date `YYYY-MM-DD` |
| `end_date` | string | No | End date `YYYY-MM-DD` |

**Responses:** `200` Array of employee booking counts | `401` Unauthorized

---

## 5. Services

> 🔒 All endpoints require authentication.

### `GET /provider/services`

List services belonging to the authenticated provider.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `sub_category_uuid` | string | No | Filter by subcategory UUID |
| `category` | string | No | Filter by category name (e.g. `Hair`, `Skin`) |
| `active` | boolean | No | Filter by active status |
| `search` | string | No | Search in name/description (ar/en) |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `POST /provider/services`

Create a new service.

> ℹ️ Send as `multipart/form-data` when uploading an image.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name.ar` | string | ✅ | Service name in Arabic |
| `name.en` | string | ✅ | Service name in English |
| `description.ar` | string | ✅ | Description in Arabic |
| `description.en` | string | ✅ | Description in English |
| `sub_category_uuid` | string | ✅ | Subcategory UUID |
| `image` | file | No | Service image (jpeg/png/webp, max 2 MB) |
| `active` | boolean | No | Active status (default `true`) |

**Responses:** `201` Created service | `400` Invalid file | `422` Validation error | `401` Unauthorized

---

### `GET /provider/services/{uuid}`

Get a single service.

**Responses:** `200` Service object | `403` Not owned | `404` Not found | `401` Unauthorized

---

### `PUT /provider/services/{uuid}`

Update a service.

**Body** — All fields optional (same fields as POST)

**Responses:** `200` Updated service | `400` Invalid file | `403` Not owned | `404` Not found | `422` Validation error | `401` Unauthorized

---

### `DELETE /provider/services/{uuid}`

Delete a service.

**Responses:** `200` Deleted | `403` Not owned | `404` Not found | `401` Unauthorized

---

### `PATCH /provider/services/{uuid}/active`

Toggle service active status.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | ✅ | `true` = enable, `false` = disable |

**Responses:** `200` Updated service | `403` Not owned | `404` Not found | `401` Unauthorized

---

### `POST /provider/services/{uuid}/assign`

Assign an existing platform service to the provider (without creating a new one).

**Responses:** `201` Assigned service | `404` Not found | `401` Unauthorized

---

### `POST /provider/services/bulk-attach`

Attach multiple services in one request. Each item can reference an existing service by UUID or define a new one by name.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `services` | array | ✅ | List of service items |
| `services[].uuid` | string | No | UUID of an existing service to attach |
| `services[].name_ar` | string | No | Arabic name for a new service (required if no `uuid`) |
| `services[].name_en` | string | No | English name for a new service (required if no `uuid`) |

**Responses:** `201` Attached services | `422` Validation error | `401` Unauthorized

---

## 6. Service Pricing

Pricing rules for services. Multiple scopes are supported with the following priority when resolving a price:
**employee > pricing group > branch > default (global)**

> 🔒 All endpoints require authentication.

### `GET /provider/service-prices`

List own service pricing rules.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `service_uuid` | string | No | Filter by service UUID |
| `sub_category_uuid` | string | No | Filter by subcategory UUID |
| `scope_type` | string | No | `default` \| `branch` \| `employee` \| `group` |
| `branch_uuid` | string | No | Filter by branch UUID |
| `employee_uuid` | string | No | Filter by employee UUID |
| `pricing_group_uuid` | string | No | Filter by pricing group UUID |
| `active` | boolean | No | Filter by active status |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `POST /provider/service-prices`

Create a pricing rule. Provide at most one of `branch_uuid`, `employee_uuid`, or `pricing_group_uuid`. Omitting all three creates the default/global price.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `service_uuid` | string | ✅ | Service UUID |
| `price` | number | ✅ | Price amount (≥ 0) |
| `duration` | integer | No | Duration in minutes |
| `branch_uuid` | string | No | Scope to a branch |
| `employee_uuid` | string | No | Scope to an employee |
| `pricing_group_uuid` | string | No | Scope to a pricing group |
| `active` | boolean | No | Active status (default `true`) |

**Responses:** `201` Created pricing rule | `422` Validation / scope conflict | `401` Unauthorized

---

### `GET /provider/service-prices/{uuid}`

Get a single pricing rule.

**Responses:** `200` Pricing rule object | `404` Not found | `401` Unauthorized

---

### `PUT /provider/service-prices/{uuid}`

Update a pricing rule. All fields optional.

**Body** — same fields as POST, all optional

**Responses:** `200` Updated rule | `422` Validation error | `404` Not found | `401` Unauthorized

---

### `DELETE /provider/service-prices/{uuid}`

Soft-delete a pricing rule.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

---

### `PATCH /provider/service-prices/{uuid}/active`

Toggle active status of a pricing rule.

**Responses:** `200` Updated rule | `404` Not found | `401` Unauthorized

---

## 7. Pricing Groups

Employee pricing groups allow setting a shared price for a set of employees.

> 🔒 All endpoints require authentication.

### `GET /provider/pricing-groups`

List own pricing groups.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `active` | boolean | No | Filter by active status |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `POST /provider/pricing-groups`

Create a pricing group. Optionally assign employees immediately via `employee_uuids`.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Group name |
| `active` | boolean | No | Active status (default `true`) |
| `employee_uuids` | array | No | UUIDs of employees to assign (must belong to this provider) |

**Responses:** `201` Created group | `422` Invalid employee UUID | `401` Unauthorized

---

### `GET /provider/pricing-groups/{uuid}`

Get a single pricing group with employee count.

**Responses:** `200` Group object | `404` Not found | `401` Unauthorized

---

### `PUT /provider/pricing-groups/{uuid}`

Update a pricing group. Providing `employee_uuids` replaces the current member list.

**Body** — all fields optional (same as POST)

**Responses:** `200` Updated group | `422` Validation error | `404` Not found | `401` Unauthorized

---

### `DELETE /provider/pricing-groups/{uuid}`

Soft-delete a pricing group.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

---

### `PATCH /provider/pricing-groups/{uuid}/active`

Toggle active status.

**Responses:** `200` Updated group | `404` Not found | `401` Unauthorized

---

### `POST /provider/pricing-groups/{uuid}/employees/sync`

Replace the entire employee list of a group. Send an empty array to remove all members.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `employee_uuids` | array | ✅ | New list of employee UUIDs |

**Responses:** `200` Synced group | `422` Invalid UUIDs | `404` Not found | `401` Unauthorized

---

### `POST /provider/pricing-groups/{uuid}/employees/add`

Add employees to a group without removing existing members.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `employee_uuids` | array | ✅ | Employee UUIDs to add |

**Responses:** `200` Updated group | `422` Invalid UUIDs | `404` Not found | `401` Unauthorized

---

## 8. Shift Templates

Reusable time templates used when creating shifts.

> 🔒 All endpoints require authentication.

### `GET /provider/shift-templates`

List shift templates.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `active` | boolean | No | Filter by active status |
| `search` | string | No | Search by template name |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `POST /provider/shift-templates`

Create a shift template.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | ✅ | Template name (e.g. `Morning Shift`) |
| `start_time` | string | ✅ | Start time `HH:MM` (24h) |
| `end_time` | string | ✅ | End time `HH:MM` (24h) |
| `active` | boolean | No | Active status (default `true`) |

**Responses:** `201` Created template | `422` Validation error | `401` Unauthorized

---

### `GET /provider/shift-templates/{uuid}`

Get a single shift template.

**Responses:** `200` Template object | `404` Not found | `401` Unauthorized

---

### `PUT /provider/shift-templates/{uuid}`

Update a shift template. All fields optional.

**Responses:** `200` Updated template | `422` Validation error | `404` Not found | `401` Unauthorized

---

### `DELETE /provider/shift-templates/{uuid}`

Delete a shift template.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

---

### `PATCH /provider/shift-templates/{uuid}/active`

Toggle active status.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `active` | boolean | ✅ | `true` = enable, `false` = disable |

**Responses:** `200` Updated template | `404` Not found | `401` Unauthorized

---

## 9. Shifts

Scheduled work shifts. Creating a shift generates date entries for each day in the range and optionally assigns employees.

> 🔒 All endpoints require authentication.

### `GET /provider/shifts`

List shifts.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `branch_uuid` | string | No | Filter by branch UUID |
| `employee_uuid` | string | No | Filter by employee UUID |
| `date` | string | No | Filter by exact date `YYYY-MM-DD` |
| `shift_template_uuid` | string | No | Filter by template UUID |
| `active` | boolean | No | Filter by active status |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `POST /provider/shifts`

Create a shift with automatic date generation and employee assignment.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `shift_template_uuid` | string | ✅ | Template UUID defining start/end time |
| `branch_uuid` | string | ✅ | Branch UUID |
| `start_date` | string | ✅ | First day of the shift range `YYYY-MM-DD` |
| `end_date` | string | ✅ | Last day of the shift range `YYYY-MM-DD` |
| `employee_uuids` | array | No | Employee UUIDs to assign to every date |
| `title` | string | No | Shift title |
| `notes` | string | No | Optional notes |
| `active` | boolean | No | Active status (default `true`) |

**Responses:** `201` Created shift | `404` Template / branch not found | `422` Validation error | `401` Unauthorized

---

### `GET /provider/shifts/{uuid}`

Get a shift with all generated dates and assigned employees.

**Responses:** `200` Shift object | `404` Not found | `401` Unauthorized

---

### `PUT /provider/shifts/{uuid}`

Update shift metadata (title, notes, active, branch). Does not regenerate dates.

**Body** — all fields optional

**Responses:** `200` Updated shift | `404` Not found | `401` Unauthorized

---

### `DELETE /provider/shifts/{uuid}`

Soft-delete a shift and all its shift dates.

**Responses:** `200` Deleted | `404` Not found | `401` Unauthorized

---

## 10. Bookings

> 🔒 All endpoints require authentication.

### `GET /provider/bookings`

List bookings for the authenticated provider.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | `pending` \| `confirmed` \| `completed` \| `cancelled` \| `no_show` |
| `branch_uuid` | string | No | Filter by branch UUID |
| `employee_uuid` | string | No | Filter by employee UUID |
| `booking_date` | string | No | Exact date filter `YYYY-MM-DD` |
| `from_date` | string | No | Bookings on/after this date |
| `to_date` | string | No | Bookings on/before this date |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /provider/bookings/next-upcoming`

Get the next upcoming confirmed booking for the provider.

**Responses:** `200` Booking object or `null` | `401` Unauthorized

---

### `GET /provider/bookings/grid`

Get a schedule grid for a specific date and branch — useful for rendering a visual calendar.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `date` | string | ✅ | Date `YYYY-MM-DD` |
| `branch_uuid` | string | No | Filter by branch UUID |

**Responses:** `200` Grid data | `401` Unauthorized

---

### `GET /provider/bookings/{uuid}`

Get a single booking.

**Responses:** `200` Booking object | `404` Not found | `401` Unauthorized

---

### `POST /provider/bookings`

Create a booking on behalf of a customer (walk-in / staff-created).

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `branch_uuid` | string | ✅ | Branch UUID (must belong to this provider) |
| `employee_uuid` | string | ✅ | Employee UUID |
| `service_uuid` | string | ✅ | Service UUID |
| `booking_date` | string | ✅ | Booking date `YYYY-MM-DD` |
| `booking_time` | string | ✅ | Booking time `HH:MM` |
| `user_name` | string | No | Walk-in customer name |
| `user_phone` | string | No | Walk-in customer phone |
| `notes` | string | No | Optional booking notes |

**Responses:** `201` Created booking | `422` Slot unavailable / validation error | `401` Unauthorized

---

### `PATCH /provider/bookings/{uuid}/status`

Update booking status.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `status` | string | ✅ | `pending` \| `confirmed` \| `completed` \| `cancelled` \| `no_show` |

**Responses:** `200` Updated booking | `422` Invalid status | `401` Unauthorized

---

## 11. Payments

> 🔒 All endpoints require authentication.

### `GET /provider/payments`

List payments for the authenticated provider's bookings.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `payment_method` | string | No | `cash` \| `paymob` |
| `status` | string | No | `pending` \| `completed` \| `failed` \| `refunded` |
| `booking_uuid` | string | No | Filter by booking UUID |
| `from_date` | string | No | Start date `YYYY-MM-DD` |
| `to_date` | string | No | End date `YYYY-MM-DD` |
| `per_page` | integer | No | Items per page (default `15`) |

**Responses:** `200` Paginated list | `401` Unauthorized

---

### `GET /provider/payments/{uuid}`

Get a single payment.

**Responses:** `200` Payment object | `404` Not found | `401` Unauthorized

---

### `POST /provider/payments`

Record a payment for a booking.

**Body**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `booking_uuid` | string | ✅ | Booking UUID |
| `payment_method` | string | ✅ | `cash` \| `paymob` |
| `amount` | number | ✅ | Payment amount (≥ 0) |
| `status` | string | No | `pending` (default) \| `completed` \| `failed` \| `refunded` |
| `transaction_id` | string | No | External transaction reference |
| `notes` | string | No | Optional notes |

**Responses:** `201` Created payment | `422` Validation error | `401` Unauthorized

---

### `PUT /provider/payments/{uuid}`

Update a payment record.

**Body** — all fields optional (same as POST)

**Responses:** `200` Updated payment | `422` Validation error | `404` Not found | `401` Unauthorized

---

## 12. Attendance

> 🔒 All endpoints require authentication.  
> Read-only — attendance is recorded automatically by the system.

### `GET /provider/attendance`

List attendance records for all employees under the authenticated provider.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `employee_uuid` | string | No | Filter by employee UUID (must belong to this provider) |
| `date_from` | string | No | Start date `YYYY-MM-DD` |
| `date_to` | string | No | End date `YYYY-MM-DD` |
| `per_page` | integer | No | Items per page (default `15`) |

**Response `200`**

```json
{
  "success": true,
  "data": [
    {
      "uuid": "<ULID>",
      "employee": { "uuid": "<ULID>", "name": "John Doe" },
      "shift_date": "2026-04-01",
      "check_in": "09:00:00",
      "check_out": "17:00:00",
      "status": "present"
    }
  ],
  "meta": {
    "pagination": { "current_page": 1, "per_page": 15, "total": 30, "last_page": 2 }
  }
}
```

**Responses:** `200` Paginated list | `401` Unauthorized

---

## 13. Ratings

> 🔒 All endpoints require authentication.  
> Read-only — providers can view ratings left on their bookings.

### `GET /provider/ratings`

List ratings for bookings belonging to the authenticated provider.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `booking_uuid` | string | No | Filter by booking UUID |
| `employee_uuid` | string | No | Filter by employee UUID |
| `branch_uuid` | string | No | Filter by branch UUID |
| `from_date` | string | No | Filter from date `YYYY-MM-DD` |
| `to_date` | string | No | Filter to date `YYYY-MM-DD` |
| `rating` | integer | No | Filter by exact star value `1`–`5` |
| `active` | boolean | No | Filter by published status |
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
      "user": { "uuid": "<ULID>", "name": "Ahmed Hassan" },
      "booking": { "uuid": "<ULID>", "booking_date": "2026-04-12" },
      "created_at": "2026-04-12T10:30:00Z"
    }
  ],
  "meta": {
    "pagination": { "current_page": 1, "per_page": 15, "total": 10, "last_page": 1 }
  }
}
```

**Responses:** `200` Paginated list | `401` Unauthorized

---

## 14. Revenue

> 🔒 Requires authentication.

### `GET /provider/revenue`

Get total revenue with per-branch and per-employee breakdown.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `branch_uuid` | string | No | Scope to a specific branch |
| `employee_uuid` | string | No | Scope to a specific employee |
| `start_date` | string | No | Start date `YYYY-MM-DD` |
| `end_date` | string | No | End date `YYYY-MM-DD` |

**Response `200`**

```json
{
  "success": true,
  "data": {
    "total_revenue": 12500.00,
    "by_branch": [
      { "branch_uuid": "<ULID>", "branch_name": "Main Branch", "revenue": 7500.00 }
    ],
    "by_employee": [
      { "employee_uuid": "<ULID>", "employee_name": "John Doe", "revenue": 4000.00 }
    ]
  }
}
```

**Responses:** `200` Revenue data | `401` Unauthorized

---

## 15. Availability

> 🔒 Requires authentication.

### `GET /provider/availability`

Get real-time availability status for all employees across all branches.

**Query Parameters**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `branch_uuid` | string | No | Scope to a specific branch |
| `employee_uuid` | string | No | Scope to a specific employee |

**Response `200`**

```json
{
  "success": true,
  "data": [
    {
      "employee_uuid": "<ULID>",
      "employee_name": "John Doe",
      "branch_uuid": "<ULID>",
      "branch_name": "Main Branch",
      "is_available": true,
      "next_available_at": null
    }
  ]
}
```

**Responses:** `200` Availability data | `401` Unauthorized
