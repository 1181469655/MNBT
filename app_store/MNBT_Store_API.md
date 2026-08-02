# MNBT Store API Documentation

**Base URL:** `http://localhost:3000/api`  
**Authentication:** Session-based (Cookie-based)  
**Content-Type:** `application/json` (except file uploads: `multipart/form-data`)

---

## Authentication

All protected endpoints require a valid session cookie. The server uses `express-session` with cookie-based sessions.

### Authentication Endpoints

#### Get Captcha
```
GET /auth/captcha
```
**Response:** SVG image (CAPTCHA)
**Session:** Sets `req.session.captchaText`

#### Register
```
POST /auth/register
```
**Body:**
```json
{
  "username": "string (2-30 chars)",
  "password": "string (min 6 chars)",
  "email": "string (email format)",
  "captcha": "string (4 chars from captcha)"
}
```
**Response:**
```json
{
  "code": 0,
  "msg": "注册成功",
  "data": {
    "id": 1,
    "username": "user",
    "email": "user@example.com",
    "role": "developer",
    "avatar": "",
    "bio": "",
    "status": "active"
  }
}
```

#### Login
```
POST /auth/login
```
**Body:**
```json
{
  "username": "string",
  "password": "string",
  "captcha": "string (4 chars from captcha)"
}
```
**Response:**
```json
{
  "code": 0,
  "msg": "登录成功",
  "data": {
    "id": 1,
    "username": "user",
    "email": "user@example.com",
    "role": "developer",
    "avatar": "",
    "bio": "",
    "status": "active"
  }
}
```
**Session:** Sets `req.session.userId`, `req.session.username`, `req.session.role`

#### Logout
```
POST /auth/logout
```
**Response:**
```json
{
  "code": 0,
  "msg": "已退出登录"
}
```

#### Get Current User
```
GET /auth/me
```
**Auth Required:** Yes (Developer or Admin)
**Response:**
```json
{
  "code": 0,
  "data": {
    "id": 1,
    "username": "user",
    "email": "user@example.com",
    "role": "developer",
    "avatar": "",
    "bio": "",
    "status": "active"
  }
}
```

#### Change Password
```
PUT /auth/password
```
**Auth Required:** Yes
**Body:**
```json
{
  "oldPassword": "string",
  "newPassword": "string (min 6 chars)"
}
```
**Response:**
```json
{
  "code": 0,
  "msg": "密码修改成功"
}
```

---

## Public Item APIs (No Auth Required)

### List Items (Public Store)
```
GET /items
```
**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| type | string | - | `plugin` or `theme` |
| keyword | string | - | Search in name, description, slug |
| category | string | - | Filter by category |
| author_id | number | - | Filter by author ID |
| min_price | number | - | Minimum price |
| max_price | number | - | Maximum price |
| sort | string | newest | `newest`, `downloads`, `price` |
| page | number | 1 | Page number |
| page_size | number | 12 | Items per page |

**Response:**
```json
{
  "code": 0,
  "data": {
    "items": [
      {
        "id": 1,
        "type": "plugin",
        "slug": "my-plugin",
        "name": "My Plugin",
        "version": "1.0.0",
        "author_id": 1,
        "author_name": "dev1",
        "price": 0,
        "description": "Description...",
        "main_image": "",
        "screenshots": "[]",
        "zip_path": "uuid.zip",
        "zip_size": 1024,
        "downloads": 100,
        "status": "approved",
        "review_msg": "",
        "requires_mnbt": "1.0.0",
        "category": "管理",
        "tags": "[\"tag1\", \"tag2\"]",
        "homepage": "",
        "created_at": "2025-01-01T00:00:00.000Z",
        "updated_at": "2025-01-01T00:00:00.000Z"
      }
    ],
    "total": 100
  }
}
```

### Get Categories
```
GET /items/categories
```
**Response:**
```json
{
  "code": 0,
  "data": ["支付", "通知", "管理", "界面", "安全", "SEO", "备份", "监控", "其他"]
}
```

### Get Item Detail
```
GET /items/:id
```
**Path Parameter:** `id` (number)
**Auth:** Optional (required to view own pending items)
**Response:**
```json
{
  "code": 0,
  "data": { ...item object... }
}
```

### Download Item
```
GET /items/:id/download
```
**Path Parameter:** `id` (number)
**Auth:** Optional (logs download with userId if logged in)
**Response:** File download (ZIP file)
**Headers:** `Content-Disposition: attachment; filename="slug-v1.0.0.zip"`

### Get Item Versions
```
GET /items/:id/versions
```
**Path Parameter:** `id` (number)
**Response:**
```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "item_id": 1,
      "version": "1.0.0",
      "zip_path": "uuid.zip",
      "zip_size": 1024,
      "changelog": "Initial release",
      "status": "approved",
      "created_at": "2025-01-01T00:00:00.000Z"
    }
  ]
}
```

---

## Developer APIs (Requires Developer Role)

**Auth Required:** Yes (Role: `developer` or `admin`)

### List My Items
```
GET /developer/items
```
**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| type | string | - | `plugin` or `theme` |
| page | number | 1 | Page number |
| page_size | number | 20 | Items per page |

**Response:**
```json
{
  "code": 0,
  "data": {
    "items": [...],
    "total": 50
  }
}
```

### Submit New Item (Plugin/Theme)
```
POST /developer/items
```
**Content-Type:** `multipart/form-data`
**Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| type | string | Yes | `plugin` or `theme` |
| slug | string | Yes | Unique identifier (alphanumeric, underscore, hyphen, max 63 chars) |
| name | string | Yes | Display name |
| version | string | Yes | Version string (e.g., `1.0.0`) |
| price | number | No | Default: 0 |
| description | string | Yes | HTML allowed (sanitized) |
| category | string | No | Category name |
| tags | string | No | JSON array string `["tag1", "tag2"]` |
| homepage | string | No | Project homepage URL |
| requires_mnbt | string | No | Required MNBT version |
| main_image | string | No | Image URL from upload |
| zipfile | file | Yes | ZIP file (max 50MB) |

**ZIP Validation Rules:**
- Must contain exactly one root folder
- Root folder name must match `slug`
- Max 500 files
- Max 10MB per file
- Max 100MB total uncompressed
- No path traversal (`..`)
- No absolute paths

**Response:**
```json
{
  "code": 0,
  "msg": "提交成功，等待审核",
  "data": { ...item object... }
}
```

### Update Item Info
```
PUT /developer/items/:id
```
**Path Parameter:** `id` (number)
**Auth Required:** Yes (must be author)
**Body:**
```json
{
  "price": 10.00,
  "description": "Updated description",
  "category": "管理",
  "tags": "[\"tag1\", \"tag2\"]",
  "homepage": "https://example.com",
  "main_image": "/uploads/images/uuid.png"
}
```
**Note:** Changes create edit requests for admin review. Item status changes to `pending`.

**Response:**
```json
{
  "code": 0,
  "msg": "已提交 3 项修改，等待管理员审核"
}
```

### Add New Version
```
POST /developer/items/:id/versions
```
**Path Parameter:** `id` (number)
**Content-Type:** `multipart/form-data`
**Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| version | string | Yes | New version number |
| changelog | string | No | Changelog text |
| zipfile | file | Yes | ZIP file (same validation as submit) |

**Response:**
```json
{
  "code": 0,
  "msg": "版本添加成功"
}
```

### Get My Item Detail
```
GET /developer/items/:id
```
**Path Parameter:** `id` (number)
**Auth Required:** Yes (must be author)

### Get My Item Versions
```
GET /developer/items/:id/versions
```
**Path Parameter:** `id` (number)
**Auth Required:** Yes (must be author)

---

## Upload APIs (Requires Login)

### Upload Image
```
POST /upload/image
```
**Auth Required:** Yes (any logged-in user)
**Content-Type:** `multipart/form-data`
**Fields:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| image | file | Yes | Image file (jpg, jpeg, png, gif, webp, max 5MB) |

**Response:**
```json
{
  "code": 0,
  "msg": "上传成功",
  "data": {
    "url": "/uploads/images/uuid.png",
    "filename": "uuid.png"
  }
}
```

---

## Admin APIs (Requires Admin Role)

**Auth Required:** Yes (Role: `admin`)

### Get Statistics
```
GET /admin/stats
```
**Response:**
```json
{
  "code": 0,
  "data": {
    "totalUsers": 100,
    "totalItems": 50,
    "pendingItems": 5,
    "totalDownloads": 1000,
    "totalDevelopers": 30
  }
}
```

### List All Items (Admin)
```
GET /admin/items
```
**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| type | string | - | `plugin` or `theme` |
| status | string | - | `pending`, `approved`, `rejected`, `suspended` |
| keyword | string | - | Search keyword |
| page | number | 1 | Page number |
| page_size | number | 20 | Items per page |

**Response:** Same as public list but includes all statuses.

### Approve Item
```
PUT /admin/items/:id/approve
```
**Path Parameter:** `id` (number)
**Body:**
```json
{
  "msg": "审核通过，可选备注"
}
```
**Response:**
```json
{
  "code": 0,
  "msg": "审核通过"
}
```

### Reject Item
```
PUT /admin/items/:id/reject
```
**Path Parameter:** `id` (number)
**Body:**
```json
{
  "msg": "驳回原因（必填）"
}
```
**Response:**
```json
{
  "code": 0,
  "msg": "已驳回"
}
```

### Suspend Item (Take Down)
```
PUT /admin/items/:id/suspend
```
**Path Parameter:** `id` (number)
**Body:**
```json
{
  "msg": "下架原因（可选）"
}
```
**Response:**
```json
{
  "code": 0,
  "msg": "已下架"
}
```

### Delete Item
```
DELETE /admin/items/:id
```
**Path Parameter:** `id` (number)
**Response:**
```json
{
  "code": 0,
  "msg": "删除成功"
}
```

### List Edit Requests
```
GET /admin/edit-requests
```
**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| status | string | - | `pending`, `approved`, `rejected` |

**Response:**
```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "item_id": 5,
      "field": "price",
      "old_value": "0",
      "new_value": "10.00",
      "status": "pending",
      "created_at": "2025-01-01T00:00:00.000Z"
    }
  ]
}
```

### Approve Edit Request
```
PUT /admin/edit-requests/:id/approve
```
**Path Parameter:** `id` (number)
**Response:**
```json
{
  "code": 0,
  "msg": "审核通过，已应用修改"
}
```

### Reject Edit Request
```
PUT /admin/edit-requests/:id/reject
```
**Path Parameter:** `id` (number)
**Response:**
```json
{
  "code": 0,
  "msg": "已驳回修改申请"
}
```

### List Users
```
GET /admin/users
```
**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | number | 1 | Page number |
| page_size | number | 20 | Users per page |

**Response:**
```json
{
  "code": 0,
  "data": {
    "users": [
      {
        "id": 1,
        "username": "user",
        "email": "user@example.com",
        "role": "developer",
        "avatar": "",
        "bio": "",
        "status": "active",
        "created_at": "2025-01-01T00:00:00.000Z"
      }
    ],
    "total": 100
  }
}
```

### Update User Status
```
PUT /admin/users/:id
```
**Path Parameter:** `id` (number)
**Body:**
```json
{
  "status": "active" // or "banned"
}
```
**Response:**
```json
{
  "code": 0,
  "msg": "用户状态已更新"
}
```

---

## Error Response Format

All error responses follow this format:
```json
{
  "code": 400,
  "msg": "错误信息",
  "data": null
}
```

**Common HTTP Status Codes:**
- `200` - Success (code 0 in body)
- `400` - Bad Request (validation error)
- `401` - Unauthorized (not logged in)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `500` - Internal Server Error

**Common Error Codes:**
- `400` - Validation error / Business logic error
- `401` - Not authenticated
- `403` - Insufficient permissions
- `404` - Resource not found

---

## File Storage Structure

```
/uploads/
├── images/      # Uploaded images (UUID.ext)
└── packages/    # Uploaded ZIP packages (UUID.zip)
```

**Image URLs:** `/uploads/images/{filename}`  
**Download URLs:** `/api/items/{id}/download` (served via Express static + download)

---

## Database Schema Overview

### Users Table
- `id`, `username`, `password` (bcrypt), `email`, `role` (admin/developer), `avatar`, `bio`, `status` (active/banned), `created_at`, `updated_at`

### Items Table
- `id`, `type` (plugin/theme), `slug`, `name`, `version`, `author_id`, `author_name`, `price`, `description`, `main_image`, `screenshots` (JSON), `zip_path`, `zip_size`, `downloads`, `status` (pending/approved/rejected/suspended), `review_msg`, `requires_mnbt`, `category`, `tags` (JSON), `homepage`, `created_at`, `updated_at`
- Unique constraint: `(type, slug)`

### Item Versions Table
- `id`, `item_id`, `version`, `zip_path`, `zip_size`, `changelog`, `status`, `created_at`

### Download Logs Table
- `id`, `item_id`, `user_id` (nullable), `ip`, `created_at`

### Edit Requests Table
- `id`, `item_id`, `field`, `old_value`, `new_value`, `status` (pending/approved/rejected), `created_at`

---

## Default Admin Account

- **Username:** `admin`
- **Password:** `admin123`
- **Email:** `admin@mnbt.local`

---

## Development

```bash
# Install dependencies
npm install

# Development server (with hot reload)
npm run dev

# Build for production
npm run build

# Start production server
npm start
```

**Server runs on:** `http://localhost:3000` (or `PORT` env var)