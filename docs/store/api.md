---
title: MNBT Store API 文档
description: MNBT 插件商店与主题商店的完整 REST API 文档（英文原文）
---

# MNBT Store API Documentation

**Base URL:** `http://localhost:3000/api`  
**Authentication:** Session-based (Cookie-based)  
**Content-Type:** `application/json` (except file uploads: `multipart/form-data`)

---

## Authentication

### Register

```
POST /auth/register
```

```json
{
  "username": "string (2-30 chars)",
  "password": "string (min 6 chars)",
  "email": "string (email format)",
  "captcha": "string (4 chars from captcha)"
}
```

### Login

```
POST /auth/login
```

```json
{
  "username": "string",
  "password": "string",
  "captcha": "string (4 chars from captcha)"
}
```

**Session:** Sets `req.session.userId`, `req.session.username`, `req.session.role`

### Logout

```
POST /auth/logout
```

### Get Current User

```
GET /auth/me
```

**Auth Required:** Yes (Developer or Admin)

### Change Password

```
PUT /auth/password
```

```json
{
  "oldPassword": "string",
  "newPassword": "string (min 6 chars)"
}
```

---

## Public Item APIs (No Auth)

### List Items

```
GET /items
```

| Query | Type | Default | Description |
|-------|------|---------|-------------|
| type | string | - | `plugin` or `theme` |
| keyword | string | - | Search name, description, slug |
| category | string | - | Filter by category |
| author_id | number | - | Author filter |
| min_price | number | - | Minimum price |
| max_price | number | - | Maximum price |
| sort | string | newest | `newest`, `downloads`, `price` |
| page | number | 1 | Page number |
| page_size | number | 12 | Items per page |

### Get Categories

```
GET /items/categories
```

### Get Item Detail

```
GET /items/:id
```

### Download Item

```
GET /items/:id/download
```

**Response:** File download (ZIP), logs download with userId if logged in.

### Get Item Versions

```
GET /items/:id/versions
```

---

## Developer APIs

**Auth:** Developer or Admin role required.

### List My Items

```
GET /developer/items
```

| Query | Type | Default |
|-------|------|---------|
| type | string | - |
| page | number | 1 |
| page_size | number | 20 |

### Submit New Item

```
POST /developer/items
```

**Content-Type:** `multipart/form-data`

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| type | string | Yes | `plugin` or `theme` |
| slug | string | Yes | Unique identifier |
| name | string | Yes | Display name |
| version | string | Yes | Version string |
| price | number | No | Default: 0 |
| description | string | Yes | HTML (sanitized) |
| category | string | No | Category |
| tags | string | No | JSON array `["tag1", "tag2"]` |
| homepage | string | No | Project URL |
| zipfile | file | Yes | ZIP (max 50MB) |

**ZIP Validation:** Root folder must match slug, max 500 files, max 10MB per file, no path traversal.

### Update Item Info

```
PUT /developer/items/:id
```

Changes create edit requests for admin review.

### Add New Version

```
POST /developer/items/:id/versions
```

**Content-Type:** `multipart/form-data`

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| version | string | Yes | New version number |
| changelog | string | No | Changelog |
| zipfile | file | Yes | ZIP file |

---

## Upload APIs

### Upload Image

```
POST /upload/image
```

**Auth:** Any logged-in user  
**Fields:** `image` (file, max 5MB, jpg/png/gif/webp)  
**Response:** `{ "url": "/uploads/images/uuid.png" }`

---

## Admin APIs

**Auth:** Admin role required.

### Get Statistics

```
GET /admin/stats
```

### List All Items

```
GET /admin/items
```

| Query | Type | Default |
|-------|------|---------|
| type | string | - |
| status | string | - |
| keyword | string | - |

### Approve / Reject / Suspend Item

```
PUT /admin/items/:id/approve
PUT /admin/items/:id/reject
PUT /admin/items/:id/suspend
```

### Delete Item

```
DELETE /admin/items/:id
```

### List / Approve / Reject Edit Requests

```
GET /admin/edit-requests
PUT /admin/edit-requests/:id/approve
PUT /admin/edit-requests/:id/reject
```

### List / Update Users

```
GET /admin/users
PUT /admin/users/:id
```

---

## Error Response

```json
{
  "code": 400,
  "msg": "Error message",
  "data": null
}
```

| HTTP | Meaning |
|------|---------|
| 200 | Success |
| 400 | Validation error |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |

---

## Database Schema

**Users:** `id, username, password(bcrypt), email, role(admin/developer), avatar, bio, status(active/banned)`  
**Items:** `id, type(plugin/theme), slug, name, version, author_id, price, description, zip_path, downloads, status(pending/approved/rejected/suspended)` — UNIQUE `(type, slug)`  
**Item Versions:** `id, item_id, version, zip_path, changelog, status`  
**Download Logs:** `id, item_id, user_id(nullable), ip`  
**Edit Requests:** `id, item_id, field, old_value, new_value, status`

---

## Default Admin

- **Username:** `admin`
- **Password:** `admin123`

---

## Development

```bash
npm install
npm run dev      # Development server
npm run build    # Build production
npm start        # Start production
```

**Server:** `http://localhost:3000` (or `PORT` env var)
