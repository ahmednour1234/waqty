# Provider Register API - Required Changes

## Endpoint: `POST /api/provider/auth/register`

---

## Current API Body (What exists now)

```json
{
  "name": "Provider Name",
  "email": "provider@example.com",
  "password": "password123",
  "phone": "+201234567890",
  "category_uuid": "<CATEGORY_UUID>",
  "city_uuid": "<CITY_UUID>",
  "code": "PROV001"
}
```

---

## What the Frontend Onboarding Collects

### Step 1 - Account
| Field | Type | Example |
|-------|------|---------|
| `email` | string | `"ahmed@example.com"` |
| `phone` | string | `"01012345678"` (extracted from identifier) |
| `name` | string | `"Ahmed Mohamed"` |
| OTP verification | - | User verifies via 6-digit code before proceeding |

### Step 2 - Business
| Field | Type | Example |
|-------|------|---------|
| `business_type` | string | `"clinic"` / `"salon"` / `"barber"` |
| `business_name` | string | `"Elite Beauty Salon"` |
| `governorate` | string | `"Cairo"` |
| `city` | string | `"Nasr City"` |
| `street` | string | `"Abbas El Akkad St."` |
| `building` | string | `"12"` |
| `floor` | string | `"3"` |
| `latitude` | number / null | `30.0444` (from map pin, optional) |
| `longitude` | number / null | `31.2357` (from map pin, optional) |

### Step 3 - Services
| Field | Type | Example |
|-------|------|---------|
| `services` | array of strings | `["Hair Coloring", "Keratin Treatment", "Bridal Makeup"]` |

---

## Required Changes

### 1. New Fields to ADD

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `business_name` | string | yes | Provider's business/brand name |
| `street` | string | yes | Street address |
| `building` | string | no | Building number |
| `floor` | string | no | Floor / apartment |
| `latitude` | decimal | no | Map pin latitude |
| `longitude` | decimal | no | Map pin longitude |
| `services` | array of strings | yes (min 1) | Initial services list |

### 2. Fields to MODIFY

| Field | Change | Reason |
|-------|--------|--------|
| `password` | Make **optional** or **remove** | Onboarding uses OTP verification, no password field. Provider can set password later via "Set Password" flow |
| `code` | Make **optional** / **auto-generate** | No provider code input in onboarding. Backend should auto-generate if not provided |
| `category_uuid` | Keep, but frontend will resolve it | Frontend will call `GET /api/public/categories` and map `business_type` string to the correct `category_uuid` before sending |
| `city_uuid` | Keep, but frontend will resolve it | Frontend will call `GET /api/public/cities` and map `governorate` + `city` strings to the correct `city_uuid` before sending |

### 3. No Changes Needed

| Field | Status |
|-------|--------|
| `name` | Keep as-is |
| `email` | Keep as-is |
| `phone` | Keep as-is |

---

## Proposed New API Body

```json
{
  "name": "Ahmed Mohamed",
  "email": "ahmed@example.com",
  "phone": "+201012345678",
  "password": null,
  "category_uuid": "actual-uuid-from-categories-api",
  "city_uuid": "actual-uuid-from-cities-api",
  "code": null,
  "business_name": "Elite Beauty Salon",
  "street": "Abbas El Akkad St.",
  "building": "12",
  "floor": "3",
  "latitude": 30.0444,
  "longitude": 31.2357,
  "services": [
    "Hair Coloring",
    "Keratin Treatment",
    "Manicure & Pedicure",
    "Facial Treatment",
    "Bridal Makeup"
  ]
}
```

---

## Validation Rules (Suggested)

```
name          => required, string, min:2, max:255
email         => required, email, unique:providers
phone         => required, string, unique:providers
password      => nullable, string, min:8
category_uuid => required, uuid, exists:categories,uuid
city_uuid     => required, uuid, exists:cities,uuid
code          => nullable, string, unique:providers
business_name => required, string, min:2, max:255
street        => required, string, max:255
building      => nullable, string, max:50
floor         => nullable, string, max:50
latitude      => nullable, numeric, between:-90,90
longitude     => nullable, numeric, between:-180,180
services      => required, array, min:1
services.*    => required, string, max:255
```

---

## Expected Response

### Success (201)
```json
{
  "success": true,
  "message": "Provider registered successfully",
  "data": {
    "token": "jwt-token-here",
    "token_type": "bearer",
    "expires_in": 3600,
    "provider": {
      "uuid": "generated-uuid",
      "name": "Ahmed Mohamed",
      "email": "ahmed@example.com",
      "phone": "+201012345678",
      "business_name": "Elite Beauty Salon",
      "code": "AUTO-GENERATED",
      "active": true,
      "blocked": false,
      "banned": false,
      "created_at": "2026-03-16T...",
      "updated_at": "2026-03-16T..."
    }
  }
}
```

### Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "phone": ["The phone has already been taken."]
  }
}
```

---

## Questions for Backend Team

1. **Password**: Should we add a password field to onboarding, or allow passwordless registration and let providers set a password later?
2. **OTP token**: Should the register request include a `verification_token` from the OTP verification step to prove the email/phone was verified?
3. **Services storage**: Should services be created inline during registration, or should we use a separate `POST /api/provider/services/bulk` endpoint after registration?
4. **Provider code**: Should the backend auto-generate the `code`, or should we add a field for it in the onboarding?

---

## Frontend Integration Plan

Once the API changes are confirmed, the frontend will:

1. **Step 1**: Collect email/phone, verify via OTP, collect name
2. **Before Step 2**: Fetch `GET /api/public/categories` and `GET /api/public/cities` to get UUIDs
3. **Step 2**: Collect business info, map `business_type` -> `category_uuid` and `governorate+city` -> `city_uuid`
4. **Step 3**: Collect services
5. **On submit**: Send single `POST /api/provider/auth/register` with all data
6. **On success**: Store JWT token + provider data, redirect to dashboard
