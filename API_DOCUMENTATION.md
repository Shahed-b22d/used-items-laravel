# TajdidP API Documentation

## Table of Contents
- [Authentication](#authentication)
  - [User Authentication](#user-authentication)
  - [Store Authentication](#store-authentication)
  - [Admin Authentication](#admin-authentication)
- [Products](#products)
- [Categories](#categories)
- [Cart](#cart)
- [Complaints](#complaints)
- [Store Management](#store-management)
- [Admin Operations](#admin-operations)

## Authentication

### User Authentication

#### Register User
> يتم استخدام هذا المسار لتسجيل مستخدم جديد في النظام. يجب التأكد من أن البريد الإلكتروني غير مستخدم مسبقاً وأن كلمة المرور تتكون من 8 أحرف على الأقل. عند نجاح التسجيل، سيتم إنشاء توكن للمستخدم تلقائياً.

```http
POST /api/user/register
```

**Request Body:**
```json
{
    "name": "string",
    "email": "string",
    "password": "string (min: 8 characters)"
}
```

**Response (201):**
```json
{
    "token": "string",
    "user": {
        "name": "string",
        "email": "string",
        "id": "integer"
    }
}
```

#### User Login
> يستخدم هذا المسار لتسجيل دخول المستخدم. يجب إرسال البريد الإلكتروني وكلمة المرور. في حالة نجاح تسجيل الدخول، سيتم إرجاع توكن جديد مع بيانات المستخدم.

```http
POST /api/user/login
```

**Request Body:**
```json
{
    "email": "string",
    "password": "string"
}
```

**Response (200):**
```json
{
    "token": "string",
    "user": {
        "name": "string",
        "email": "string",
        "id": "integer"
    }
}
```

#### User Logout
> يستخدم لتسجيل خروج المستخدم وإبطال التوكن الحالي. يجب إرسال التوكن في رأس الطلب.

```http
POST /api/user/logout
```
**Headers Required:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "message": "Logged out successfully"
}
```

### Store Authentication

#### Register Store
> يستخدم لتسجيل متجر جديد. يجب إرفاق السجل التجاري كملف. المتجر لن يكون نشطاً حتى تتم الموافقة عليه من قبل الأدمن. يجب أن يكون حجم الملف أقل من 2 ميجابايت.

```http
POST /api/store/register
```

**Request Body (multipart/form-data):**
```json
{
    "name": "string",
    "email": "string",
    "password": "string (min: 8 characters)",
    "commercial_record": "file (jpg,jpeg,png,pdf, max: 2MB)"
}
```

**Response (201):**
```json
{
    "status": "success",
    "message": "تم استلام طلب التسجيل وسيتم مراجعته من قبل الإدارة",
    "data": {
        "name": "string",
        "email": "string"
    }
}
```

#### Store Login
> يستخدم لتسجيل دخول المتجر. لن يتمكن المتجر من تسجيل الدخول إلا بعد موافقة الأدمن على حسابه.

```http
POST /api/store/login
```

**Request Body:**
```json
{
    "email": "string",
    "password": "string"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "تم تسجيل الدخول بنجاح",
    "data": {
        "token": "string",
        "store": {
            "name": "string",
            "email": "string"
        }
    }
}
```

#### Store Logout
```http
POST /api/store/logout
```
**Headers Required:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "status": "success",
    "message": "تم تسجيل الخروج بنجاح"
}
```

### Admin Authentication

#### Admin Login
> يستخدم لتسجيل دخول الأدمن إلى النظام.

```http
POST /api/admin/login
```

**Request Body:**
```json
{
    "username": "string",
    "password": "string"
}
```

**Response (200):**
```json
{
    "status": "success",
    "token": "string",
    "message": "تم تسجيل الدخول بنجاح"
}
```

#### Change Admin Password
> يستخدم لتغيير كلمة مرور الأدمن الحالية.

```http
POST /api/admin/change-password
```
**Headers Required:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "current_password": "string",
    "new_password": "string",
    "new_password_confirmation": "string"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "تم تغيير كلمة المرور بنجاح"
}
```

#### Forgot Admin Password
> يستخدم لطلب إعادة تعيين كلمة مرور الأدمن عن طريق البريد الإلكتروني.

```http
POST /api/admin/forgot-password
```

**Request Body:**
```json
{
    "email": "string"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني"
}
```

#### Reset Admin Password
> يستخدم لإعادة تعيين كلمة مرور الأدمن باستخدام الرمز المرسل بالبريد.

```http
POST /api/admin/reset-password
```

**Request Body:**
```json
{
    "email": "string",
    "token": "string",
    "password": "string",
    "password_confirmation": "string"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "تم إعادة تعيين كلمة المرور بنجاح"
}
```

## Products

### List All Approved Products
> يستخدم لعرض جميع المنتجات المعتمدة من قبل الأدمن. النتائج مقسمة إلى صفحات، كل صفحة تحتوي على 10 منتجات.

```http
GET /api/products
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "current_page": "integer",
        "data": [
            {
                "id": "integer",
                "name": "string",
                "description": "string",
                "price": "number",
                "stock": "integer",
                "location": "string",
                "image": "string",
                "category": {
                    "id": "integer",
                    "name": "string"
                },
                "user": {
                    "id": "integer",
                    "name": "string"
                }
            }
        ],
        "first_page_url": "string",
        "last_page": "integer",
        "last_page_url": "string",
        "next_page_url": "string",
        "path": "string",
        "per_page": "integer",
        "prev_page_url": "string",
        "to": "integer",
        "total": "integer"
    },
    "message": "تم جلب المنتجات بنجاح"
}
```

### Create Product
> يستخدم لإضافة منتج جديد في قسم معين. يجب أن يكون المتجر مسجل الدخول. المنتج سيكون في حالة انتظار حتى يتم اعتماده من قبل الأدمن.

```http
POST /api/store/categories/{categoryId}/products
```
**Headers Required:** `Authorization: Bearer {token}`

**Request Body (multipart/form-data):**
```json
{
    "name": "string",
    "description": "string",
    "price": "number",
    "stock": "integer",
    "location": "string",
    "image": "file (optional, jpg,jpeg,png, max: 2MB)"
}
```

**Response (201):**
```json
{
    "message": "تم إرسال المنتج بنجاح بانتظار موافقة الأدمن.",
    "product": {
        "id": "integer",
        "name": "string",
        "description": "string",
        "price": "number",
        "stock": "integer",
        "location": "string",
        "image": "string",
        "is_approved": false,
        "category_id": "integer",
        "store_id": "integer",
        "created_at": "timestamp",
        "updated_at": "timestamp"
    }
}
```

## Categories

### List Main Categories
> يستخدم لعرض جميع الأقسام الرئيسية المعتمدة من قبل الأدمن.

```http
GET /api/categories
```
**Headers Required:** `Authorization: Bearer {token}`

### Store Categories Management

#### Create Store Category
> يستخدم لإنشاء أقسام خاصة بالمتجر. يمكن إضافة أقسام من الأقسام الرئيسية أو إنشاء أقسام خاصة جديدة.

```http
POST /api/store/categories
```
**Headers Required:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "category_ids": ["integer"], // معرفات الأقسام الرئيسية (اختياري)
    "new_categories": ["string"] // أسماء الأقسام الجديدة (اختياري)
}
```

**Response (201):**
```json
{
    "status": "success",
    "message": "تم إضافة الأقسام بنجاح",
    "data": {
        "added_categories": [
            {
                "id": "integer",
                "name": "string",
                "is_main": "boolean",
                "main_category": {
                    "id": "integer",
                    "name": "string"
                }
            }
        ],
        "errors": ["string"] // أي أخطاء حدثت أثناء الإضافة
    }
}
```

#### List Store Categories
```http
GET /api/store/categories
```
**Headers Required:** `Authorization: Bearer {token}`

#### Update Store Category
```http
PUT /api/store/categories/{id}
```
**Headers Required:** `Authorization: Bearer {token}`

#### Delete Store Category
```http
DELETE /api/store/categories/{id}
```
**Headers Required:** `Authorization: Bearer {token}`

## Cart

### Add to Cart
> يستخدم لإضافة منتج إلى سلة المشتريات. يتم التحقق من توفر الكمية المطلوبة.

```http
POST /api/cart
```
**Headers Required:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "product_id": "integer",
    "quantity": "integer"
}
```

**Response (200):**
```json
{
    "message": "تمت إضافة المنتج إلى السلة"
}
```

### View Cart
> يستخدم لعرض محتويات سلة المشتريات. يتم حذف المنتجات غير المتوفرة تلقائياً.

```http
GET /api/cart
```
**Headers Required:** `Authorization: Bearer {token}`

**Response (200):**
```json
[
    {
        "id": "integer",
        "user_id": "integer",
        "product_id": "integer",
        "quantity": "integer",
        "product": {
            "id": "integer",
            "name": "string",
            "price": "number",
            "image": "string",
            "stock": "integer"
        }
    }
]
```

### Update Cart Item Quantity
```http
PUT /api/cart/{product_id}
```
**Headers Required:** `Authorization: Bearer {token}`

### Remove from Cart
```http
DELETE /api/cart/{product_id}
```
**Headers Required:** `Authorization: Bearer {token}`

## Admin Operations

### Store Moderation

#### List Pending Stores
> يستخدم لعرض جميع المتاجر التي تنتظر الموافقة من قبل الأدمن.

```http
GET /api/admin/stores/pending
```
**Headers Required:** `Authorization: Bearer {token}`

#### Approve Store
> يستخدم للموافقة على متجر جديد. بعد الموافقة، يمكن للمتجر تسجيل الدخول وإضافة المنتجات.

```http
POST /api/admin/stores/{id}/approve
```
**Headers Required:** `Authorization: Bearer {token}`

#### Reject Store
> يستخدم لرفض طلب تسجيل متجر. سيتم حذف بيانات المتجر من النظام.

```http
DELETE /api/admin/stores/{id}/reject
```
**Headers Required:** `Authorization: Bearer {token}`

### Product Moderation

#### List Pending Products
```http
GET /api/admin/products/pending
```
**Headers Required:** `Authorization: Bearer {token}`

#### Approve Product
```http
POST /api/admin/products/{id}/approve
```
**Headers Required:** `Authorization: Bearer {token}`

#### Reject Product
```http
DELETE /api/admin/products/{id}/reject
```
**Headers Required:** `Authorization: Bearer {token}`

### Category Management (Admin)

#### List Categories
```http
GET /api/admin/categories
```
**Headers Required:** `Authorization: Bearer {token}`

#### Create Category
```http
POST /api/admin/categories
```
**Headers Required:** `Authorization: Bearer {token}`

#### Update Category
```http
PUT /api/admin/categories/{id}
```
**Headers Required:** `Authorization: Bearer {token}`

#### Delete Category
```http
DELETE /api/admin/categories/{id}
```
**Headers Required:** `Authorization: Bearer {token}`

## Complaints

### Create Complaint
> يستخدم لتقديم شكوى من قبل المستخدم أو المتجر.

```http
POST /api/complaints
```
**Headers Required:** `Authorization: Bearer {token}`

### List Complaints (Admin Only)
> يستخدم لعرض جميع الشكاوى المقدمة. متاح فقط للأدمن.

```http
GET /api/complaints
```
**Headers Required:** `Authorization: Bearer {token}`

## General Notes

1. All authenticated routes require the `Authorization` header with a Bearer token
2. File uploads should not exceed 2MB
3. Pagination is implemented for listing endpoints with 10 items per page
4. All error responses follow the format:
```json
{
    "status": "error",
    "message": "string",
    "errors": {
        "field": ["error messages"]
    }
}
```
5. Successful responses generally follow the format:
```json
{
    "status": "success",
    "message": "string",
    "data": {}
}
```

## Authentication Notes

1. Store accounts require admin approval before they can login
2. Failed login attempts return 401 Unauthorized
3. Invalid tokens return 401 Unauthorized
4. Insufficient permissions return 403 Forbidden

## Store Products Management

#### List Store Products
> يستخدم لعرض جميع منتجات المتجر.

```http
GET /api/store/products
```
**Headers Required:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": "integer",
            "name": "string",
            "description": "string",
            "price": "number",
            "stock": "integer",
            "category_id": "integer",
            "image": "string",
            "is_approved": "boolean",
            "created_at": "timestamp",
            "updated_at": "timestamp"
        }
    ]
}
```

#### Update Store Product
> يستخدم لتحديث بيانات منتج موجود في المتجر.

```http
PUT /api/store/products/{id}
```
**Headers Required:** `Authorization: Bearer {token}`

**Request Body (multipart/form-data):**
```json
{
    "name": "string",
    "description": "string",
    "price": "number",
    "stock": "integer",
    "image": "file (optional, jpg,jpeg,png, max: 2MB)"
}
```

**Response (200):**
```json
{
    "status": "success",
    "message": "تم تحديث المنتج بنجاح",
    "data": {
        "id": "integer",
        "name": "string",
        "description": "string",
        "price": "number",
        "stock": "integer",
        "image": "string",
        "updated_at": "timestamp"
    }
}
```

#### Delete Store Product
> يستخدم لحذف منتج من المتجر.

```http
DELETE /api/store/products/{id}
```
**Headers Required:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "status": "success",
    "message": "تم حذف المنتج بنجاح"
}
```

### Complaints

#### Create Complaint
> يستخدم لتقديم شكوى من قبل المستخدم أو المتجر.

```http
POST /api/complaints
```
**Headers Required:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "title": "string",
    "description": "string",
    "type": "string (user/store/product)",
    "related_id": "integer"
}
```

**Response (201):**
```json
{
    "status": "success",
    "message": "تم تسجيل الشكوى بنجاح",
    "data": {
        "id": "integer",
        "title": "string",
        "description": "string",
        "status": "pending",
        "created_at": "timestamp"
    }
}
```

#### List Complaints (Admin Only)
> يستخدم لعرض جميع الشكاوى المقدمة. متاح فقط للأدمن.

```http
GET /api/complaints
```
**Headers Required:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": "integer",
            "title": "string",
            "description": "string",
            "type": "string",
            "status": "string",
            "complainant": {
                "id": "integer",
                "name": "string",
                "type": "string (user/store)"
            },
            "created_at": "timestamp"
        }
    ]
}
``` 