---
name: skywards-benefits
description: "Use when working on /skywards/benefits, Skywards 權益頁, Skywards Benefits, skywards_benefits admin CRUD, title/image/content display, or the CodeIgniter API backing this page."
argument-hint: "Describe the /skywards/benefits change or issue"
---

# Skywards Benefits Page

## Purpose
This skill covers the `/skywards/benefits` member-facing page and its matching admin management flow. Use it when changing the Skywards benefits page layout, title/image/text display, tier-specific benefit content, admin CRUD behavior, or the backend API/data contract for `skywards_benefits`.

## Current Behavior
- Frontend route: `/skywards/benefits`, route name `SkywardsBenefits`.
- Member page component: `frontend/src/views/SkywardsBenefits.vue`.
- Admin page component: `frontend/src/views/admin/AdminSkywardsBenefits.vue`, mounted under admin route `skywards-benefits`.
- Member API: `GET /api/v1/skywards/benefits`, JWT required.
- Admin API: `GET|POST /api/v1/admin-panel/skywards-benefits`, `PUT|DELETE /api/v1/admin-panel/skywards-benefits/{id}`.
- Database table: `skywards_benefits`.
- A member receives the active `rule` item for their current tier. `blue` is normalized to `regular`.

## Data Contract
`skywards_benefits` rows use these fields:
- `id`: primary key.
- `type`: only `rule` is used for this page; old `hint` and `note` rows are not displayed.
- `tier`: `regular`, `silver`, `gold`, or `platinum`.
- `label`: the page title. Treat this as the title field unless a separate `title` column is explicitly requested.
- `image_url`: the full-width top image URL.
- `content`: rich HTML shown in the white content card below the image.
- `sort_order`: ordering field; active tier lookup returns the first active row by this order.
- `is_active`: `1` displays the row; `0` hides it.

Do not add a new `title` database column for normal title display work. The existing `label` field already flows through model, service, API responses, admin payloads, and frontend rendering.

## Member Page Requirements
The `/skywards/benefits` page should match the reference card layout:
- Render the existing top navigation/header with `PageHeader` using title `我的權益` and `back-to="/skywards#tier"`.
- Keep the page body as a standalone full-bleed content area; do not wrap the body in `PageLayout` unless the design changes again.
- Use a light gray page background behind the content card.
- Render `image_url` directly below the top header with no extra gap between the header and image.
- Render `image_url` as a full-width hero image from left edge to right edge of the page content.
- Use `object-fit: cover` for the hero image so it fills the width cleanly.
- Render the text area below the image as a centered white card with subtle shadow, attached directly below the image.
- Render the title above rich content. The title comes from `label`; fallback should be the tier label plus `權益`.
- Preserve rich-text HTML formatting from `content`, including paragraphs, lists, headings, links, inline formatting, blockquotes, images, and horizontal rules.
- Loading, error, and empty states must remain readable.

## Admin Requirements
The admin Skywards benefits page must let operators manage the same fields that the member page renders:
- Show a thumbnail, tier, title, content summary, active state, and actions in the list.
- Provide a `標題` input mapped to `label`.
- Provide an `上方圖片` URL input and upload button mapped to `image_url`.
- Provide a rich-text editor mapped to `content`.
- Keep the tier selector and active/hidden state selector.
- Preview must mirror the member page structure: top header/navigation, full-width top image, white card with shadow, title, then rich content.
- When saving, trim `label`; send `null` when it is empty.
- Keep the existing upload path through `fileService.upload(file, 'general')` unless the file service contract changes.

## Backend Flow
Relevant backend files:
- `backend/app/Config/Routes.php`: member and admin routes.
- `backend/app/Controllers/Api/SkywardsBenefitController.php`: reads the authenticated user tier and returns the active item.
- `backend/app/Controllers/AdminPanelController.php`: admin CRUD endpoints.
- `backend/app/Services/SkywardsBenefitService.php`: allowed fields, tier normalization, and `type = rule` enforcement.
- `backend/app/Repositories/SkywardsBenefitRepository.php`: model access.
- `backend/app/Models/SkywardsBenefitModel.php`: table, allowed fields, active/all queries.
- `backend/app/Database/Migrations/2024-01-01-000013_CreateSkywardsBenefitsTable.php`: base table with `label` and `content`.
- `backend/app/Database/Migrations/2026-05-03-000001_AddImageUrlToSkywardsBenefits.php`: adds `tier` and `image_url`, normalizes old rows.

When changing backend fields:
1. Update the migration or add a new migration only if the data cannot fit existing fields.
2. Update `SkywardsBenefitModel::$allowedFields`.
3. Update `SkywardsBenefitService` allowed field lists in both `create()` and `update()`.
4. Confirm admin payloads and member rendering still match.
5. Update this skill after the data contract changes.

## Implementation Checklist
1. Inspect both member and admin components before editing.
2. Confirm whether the request needs only display changes or also data contract changes.
3. Prefer existing fields: use `label` for title, `image_url` for image, and `content` for rich text.
4. Keep edits scoped to the Skywards benefits page, admin page, and directly related backend files.
5. If frontend layout changes, update admin preview so operators see the same structure.
6. If backend/API changes, update service allowed fields and model allowed fields together.
7. Run `npm run build` inside `frontend` after Vue changes.
8. If backend PHP changes are made, run the relevant CodeIgniter/PHP checks available in the project.
9. Re-read this skill before finishing; update it if behavior, routes, fields, or verification steps changed.

## Verification
Minimum verification for frontend-only changes:
- `cd frontend && npm run build`
- Confirm `/skywards/benefits` still fetches `/api/v1/skywards/benefits`.
- Confirm title fallback works when `label` is empty.
- Confirm a long title and rich text do not overflow the white card on mobile widths.
- Confirm admin create/edit form sends `label`, `image_url`, `content`, `tier`, `sort_order`, and `is_active`.

Recommended API checks when backend is touched:
- Admin list returns rows with `label`, `image_url`, and `content`.
- Member endpoint returns only the authenticated user's active tier item.
- Hidden rows (`is_active = 0`) are not returned to the member page.
- `blue` tier users resolve to `regular` benefits.

## Common Pitfalls
- Do not display title from rich-text content; title belongs in `label`.
- Do not leave admin without a title input when the member page displays a title.
- Do not use `object-fit: contain` for the main hero image when the requested layout needs left/right full width.
- Do not add a separate title column unless the product explicitly requires independent `label` and `title` semantics.
- Do not bypass the service layer; it enforces allowed fields and `type = rule`.
- Do not forget to update this skill after future changes to fields, routes, or visual requirements.
