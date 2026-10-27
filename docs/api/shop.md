# Shop Module

## Overview

Provides public access to shop categories and products.

No authentication required.

---

# Endpoints

## List Categories

### Request

```http
GET /api/shop/categories
```

### Query Parameters

| Parameter           | Type    | Required | Description                                  |
|---------------------|---------|----------|-----------------------------------------------|
| has_image           | boolean | no       | Only categories with an image                 |
| only_with_products  | boolean | no       | Only categories that have at least one product|
| random              | boolean | no       | Random order                                  |
| limit               | integer | no       | Limit the number of returned root categories  |

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
  },
  "errors": null
}
```

---

## Get Category by ID

### Request

```http
GET /api/shop/categories/{id}
```

### URL Parameters

| Parameter | Type    | Required | Description  |
|-----------|---------|----------|--------------|
| id        | integer | yes      | Category ID  |

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
  },
  "errors": null
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

> Returns 404 when no visible category matches the ID.

---

## List Products

### Request

```http
GET /api/shop/products
```

### Query Parameters

All parameters are optional and validated by `ShopProductsRequest`; anything invalid
returns `422` in the standard validation-error envelope.

| Parameter   | Type    | Rules | Description |
|-------------|---------|-------|-------------|
| category_id | integer | must exist in `shop_categories` | Restrict to a category (and its descendants) |
| brand       | string / array | `1,2,3` or `brand[]=1&brand[]=2`; every id a positive integer | Filter by brand |
| {code}      | string / array | same shapes as `brand`, option ids | Filter by a product parameter, under its code from admin → Catalog → Parameters (`season[]=3`); values of one parameter are alternatives |
| price_from  | number  | `>= 0` | Minimum price (of the smallest set) |
| price_to    | number  | `>= 0`, not below `price_from` | Maximum price |
| search      | string  | max 255 characters | Search term |
| sort        | string  | one of the values below | `price_asc`, `price_desc`, `newest`, `title_asc`, `title_desc` |
| page        | integer | `>= 1` | Page number (default: 1) |

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
        "packs": [{"quantity": 1, "price": 999.00}, {"quantity": 3, "price": 2799.00}],
        "is_pack": false,
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
  },
  "errors": null
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

> Returns 404 when `category_id` does not match a visible category.

---

## Get Product by ID

### Request

```http
GET /api/shop/products/{id}
```

### URL Parameters

| Parameter | Type    | Required | Description |
|-----------|---------|----------|-------------|
| id        | integer | yes      | Product ID  |

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
      "packs": [{"quantity": 1, "price": 999.00}, {"quantity": 3, "price": 2799.00}],
      "is_pack": false,
      "image": "products/iphone-15.jpg",
      "text": "Product description...",
      "meta_title": "Buy iPhone 15",
      "meta_description": "Best price on iPhone 15",
      "brand": {
        "id": 3,
        "title": "Apple"
      }
    }
  },
  "errors": null
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

> Returns 404 when no visible product matches the ID.

---

[Back to main readme](../api/README.md)
