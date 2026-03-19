# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer Bearer <JWT_TOKEN>"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Use JWT Bearer token authentication. Include the token in the Authorization header as: <code>Authorization: Bearer {token}</code>. Tokens are obtained via login endpoints for admin, provider, or employee guards.
