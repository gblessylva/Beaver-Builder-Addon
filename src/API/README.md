# HealthyJoint Goals API

## TODO

### Endpoint: GET /wp-json/healthyjoint/v1/goals

**Issue**: The endpoint with query parameters `status=in-progress&orderby=date&order=desc&per_page=1&app_token` needs attention.

**Used by**:
- [Dashboard module](../../beaver-builder/modules/dashboard/js/frontend.js#L78)
- [Progress module](../../beaver-builder/modules/progress/js/progress.js#L113)

**Current Status**: Requires review and potential optimization or refactoring.

**Notes**: 
- Both modules use `X-HJ-API-KEY` header for authentication
- Fetches the latest in-progress goal
- Limited to 1 result per request
