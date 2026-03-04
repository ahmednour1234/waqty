# Remaining Scribe Annotations

Due to token constraints, the following controllers still need Scribe annotations:

## Provider Controllers (Partially Complete)
- ✅ ProviderAuthController - DONE
- ✅ ProviderProfileController - DONE (with file upload docs)
- ⏳ ProviderBranchController - Needs annotations (similar to AdminProviderBranchController pattern)
- ⏳ ProviderEmployeeController - Needs annotations (similar to AdminEmployeeController pattern)

## Employee Controllers
- ⏳ EmployeeAuthController - Needs annotations (similar to ProviderAuthController, but with OTP reset)
- ⏳ EmployeeProfileController - Needs annotations (with file upload and change password)

## Public Controllers
- ⏳ PublicCategoryController
- ⏳ PublicSubcategoryController
- ⏳ PublicCountryController
- ⏳ PublicCityController
- ⏳ PublicProviderController
- ⏳ PublicProviderBranchController

## Pattern to Follow

All controllers should have:
1. `#[Group('Provider APIs')]` or `#[Group('Employee APIs')]` or `#[Group('Public APIs')]`
2. `#[Header('Accept-Language', 'ar|en', required: false)]` on all methods
3. `#[Header('Authorization', 'Bearer {token}', required: true)]` on protected methods
4. `#[QueryParam]` for list endpoints with filters
5. `#[BodyParam]` for request body fields
6. `#[Response]` for success and error examples
7. Error responses: 401, 403, 422, 404, 429 as applicable

For file uploads:
- `#[Header('Content-Type', 'multipart/form-data', required: true)]`
- `#[BodyParam('logo', 'file', '...', required: false)]`
- Document: jpeg/png/webp, max 2MB, no SVG, MIME validation

For OTP endpoints (Employee):
- Document generic responses to prevent enumeration
- Use placeholder OTP: "123456" (noted as example only)
