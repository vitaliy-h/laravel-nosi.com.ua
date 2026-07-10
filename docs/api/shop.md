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
  }
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

> Returns 404 when no visible category matches the ID.

---

## List Products

### Request

```http
GET /api/shop/products
```

### Query Parameters

| Parameter   | Type    | Required | Description                                             |
|-------------|---------|----------|----------------------------------------------------------|
| category_id | integer | no       | Restrict to a category (and its descendants)              |
| brand       | string  | no       | Comma-separated brand IDs                                  |
| price_from  | number  | no       | Minimum price                                               |
| price_to    | number  | no       | Maximum price                                               |
| search      | string  | no       | Search term                                                 |
| sort        | string  | no       | `price_asc`, `price_desc`, `newest`, `title_asc`, `title_desc` |
| page        | integer | no       | Page number (default: 1)                                    |

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

> Returns 404 when no visible product matches the ID.

---

[Back to main readme](../api/README.md)
