# Shop Module

## Overview

Provides public access to shop categories.

No authentication required.

---

# Endpoints

## List Categories

### Request

```http
GET /api/shop/categories
```

### Success Response

```json
{
  "success": true,
  "message": "Categories list",
  "data": {
    "categories": [
      {
        "id": 1,
        "title": "Electronics",
        "slug": "electronics",
        "image": "categories/electronics.jpg",
        "meta_title": "Electronics",
        "meta_description": "All electronics",
        "children": []
      }
    ]
  }
}
```

---

## Get Category by Slug

### Request

```http
GET /api/shop/categories/{slug}
```

### URL Parameters

| Parameter | Type   | Required | Description       |
|-----------|--------|----------|-------------------|
| slug      | string | yes      | Category slug     |

### Success Response

```json
{
  "success": true,
  "message": "Category info",
  "data": {
    "category": {
      "id": 1,
      "title": "Electronics",
      "slug": "electronics",
      "image": "categories/electronics.jpg",
      "meta_title": "Electronics",
      "meta_description": "All electronics",
      "children": [
        {
          "id": 2,
          "title": "Phones",
          "slug": "phones",
          "image": null,
          "meta_title": null,
          "meta_description": null,
          "children": []
        }
      ]
    }
  }
}
```

### Error Response (not found)

```json
{
  "success": false,
  "message": "Not found",
  "data": null
}
```

> Returns 404 when no visible category matches the slug.

---

## List Products by Category

### Request

```http
GET /api/shop/categories/{slug}/products
```

### URL Parameters

| Parameter | Type   | Required | Description    |
|-----------|--------|----------|----------------|
| slug      | string | yes      | Category slug  |

### Query Parameters

| Parameter | Type    | Required | Description                        |
|-----------|---------|----------|------------------------------------|
| page      | integer | no       | Page number (default: 1)           |
### Success Response

```json
{
  "success": true,
  "message": "Products list",
  "data": {
    "products": [
      {
        "id": 1,
        "title": "iPhone 15",
        "slug": "iphone-15",
        "article": "APL-IP15",
        "price": 999.00,
        "price2": 1099.00,
        "image": "products/iphone-15.jpg",
        "text": "Product description...",
        "meta_title": "Buy iPhone 15",
        "meta_description": "Best price on iPhone 15",
        "brand": {
          "id": 3,
          "title": "Apple"
        }
      }
    ],
    "pagination": {
      "total": 42,
      "per_page": 10,
      "current_page": 1,
      "last_page": 5,
      "from": 1,
      "to": 10
    }
  }
}
```

### Error Response (category not found)

```json
{
  "success": false,
  "message": "Not found",
  "data": null
}
```

> Returns 404 when no visible category matches the slug.

---

## Get Product by Slug

### Request

```http
GET /api/shop/categories/{categorySlug}/products/{productSlug}
```

### URL Parameters

| Parameter    | Type   | Required | Description    |
|--------------|--------|----------|----------------|
| categorySlug | string | yes      | Category slug  |
| productSlug  | string | yes      | Product slug   |

### Success Response

```json
{
  "success": true,
  "message": "Product information",
  "data": {
    "product": {
      "id": 1,
      "title": "iPhone 15",
      "slug": "iphone-15",
      "article": "APL-IP15",
      "price": 999.00,
      "price2": 1099.00,
      "image": "products/iphone-15.jpg",
      "text": "Product description...",
      "meta_title": "Buy iPhone 15",
      "meta_description": "Best price on iPhone 15",
      "brand": {
        "id": 3,
        "title": "Apple"
      }
    }
  }
}
```

### Error Response (not found)

```json
{
  "success": false,
  "message": "Not found",
  "data": null
}
```

> Returns 404 when category or product slug does not match a visible record.

---

[Back to main readme](../api/README.md)
