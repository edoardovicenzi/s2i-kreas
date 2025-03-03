# Welcome to Kreas API
100% Vanilla PHP
100% Vanilla MySQL
Extra chocolate with docker!

A simple API that implements the REST methodology for request and responses.

---

## Requirements
There are 2 ways to make this work:

- Install docker and run docker compose command (tested, see more below)
- PHP 8.2 (__NOT TESTED__)
---

## Installation

1. With docker

`git clone https://github.com/edoardovicenzi/s2i-kreas.git`

`cd s2i-kreas`

`docker compose up -d`

2. Without docker
    - You will need to change the information for the databse connection in /app/database/MySQLDatabase.php

---

## API Documentation

You can also view the documentation on the [wiki](https://github.com/edoardovicenzi/s2i-kreas/wiki) page or the hosted version on [this github link](https://edoardovicenzi.github.io/s2i-kreas).
After the service is running you can start using the API. Here is a reference for each and every supported endpoint.

Accessing not existing endpoints or not existing resources will yield a status code 404. A `message` property will be return for additional human-readable context on the error.

Each request and each response body __MUST__ have a "data" property in which the data is to be put. For reference:

```json
{
  "data": ...
}
```

### /products
This endpoint is responsible for CRUD operations on the products. It will always return a Product object with the following structure:
```json
{
  "product_id": Integer,
  "product_name": String,
  "saved_co2": Integer
}
```
#### GET

This method always returns an array of Product objects. If no elements are found a 404 error is thrown.

For example:

`GET /products`

Will result in:

`Status 202`
```json
{
  "data": [
    {
      "product_id": 1,
      "product_name": "steak",
      "saved_co2": 20000
    },
    {
      "product_id": 2,
      "product_name": "venison",
      "saved_co2": 13456
    },
    {
      "product_id": 3,
      "product_name": "porkchop",
      "saved_co2": 19427
    }
  ]
}
```
If no products are found:

`Status 404`
```json
{
  "message": "Products not found"
}
```
#### POST

This method allows to add a product to the available list. You will need to pass the following properties with the appropriate values.

| Property Name | Value Type | Notes |
| --- | --- | --- | 
| product_name | String | |
| saved_co2 | Integer | Must be greater than 0 |

> WARNING two prodcuts can have the same name and they will always refer to different products. This behavior is intended. Might change in the future. Because of this the method will always return a status code 202.

For example:

`POST /products`

With json body:

```
{
  "data": {
    "product_name": "stake",
    "saved_co2": 25056
  }
}
```

Will result in:

`Status 202`
```json
{
  "data": {
    "product_id": 36,
    "product_name": "stake",
    "saved_co2": 25056
  },
  "location": "/products/36",
  "message": "Item added successfully"
}
```
### /products/:id
Where `:id` __MUST__ be an integer number.

The enpoint will respond with a Product object matching the `product_id` property with the given id.

#### GET

This method gets the information of a specific product as a Product object.

For example:

`GET /products/1`

Will result in:

`Status 202`
```json
{
  "data": {
    "product_id": 1,
    "product_name": "steak",
    "saved_co2": 20000
  }
}
```
If no products are found:

`Status 404`
```json
{
  "message": "Product not found"
}
```
#### PUT
This method updates the specific resource with new values. This is the list of all required properties:

| Property Name | Value Type | Notes |
| --- | --- | --- | 
| product_name | String | |
| saved_co2 | Integer | Must be greater than 0 |

> WARNING if no resource is found with said id then a new resource is added. It is equivalent do the `POST /products` method.

For example:

`PUT /products/1`

With json body:
```json
{
  "data": {
    "product_name": "steak",
    "saved_co2": 20000
  }
}
```

Will result in:

`Status 200`
```json
{
  "data": {
    "product_id": 38,
    "product_name": "test",
    "saved_co2": 156700
  },
  "message": "Item Updated successfully"
}
```

If the resource does not exist it will be created. The response will be:

`Status 201`
```json
{
  "data": {
    "product_id": 39,
    "product_name": "test",
    "saved_co2": 156700
  },
  "message": "Item Added successfully",
  "location": "/products/39"
}
```

#### DELETE
This method deletes the specific resource. Successive calls on the same resource after deleteion will result in a 404 status code.

Deletion will always return the deleted product as a Product object.

For example:

`DELETE /products/38`

Will result in:

`Status 200`
```json
{
  "data": {
    "product_id": 38,
    "product_name": "test",
    "saved_co2": 156700
  },
  "message": "Item was deleted successfully"
}
```

If the resource was already deleted the response will be:

`Status 404`
```json
{
  "message": "Error Processing Request: Item with id 38 not found."
}
```

### /orders
This endpoint is responsible for CRUD operations on the orders. It will always return an Order object with the following structure:
```json
{
  "order_id": Integer,
  "destination_country": String,
  "sold_on": DateTime,
  "products": [ProductObject]
}
```
This is a complete overview of the properties:
| Property Name | Type | Notes |
| -| -| -|
| order_id | Integer |  |
| destination_country | String | - |
| sold_on | DateTime | Format is "YYYY-MM-DD HH:mm:ss" |
| products | Array | Holds only Product objects |

#### GET

This method always returns an array of Order objects. If no elements are found a 404 error is thrown.

For example:

`GET /orders`

Will result in:

`Status 202`
```json
{
  "data": [
    {
      "order_id": 94,
      "destination_country": "USA",
      "sold_on": "2025-01-10 14:16:33",
      "products": [
        {
          "product_id": 1,
          "product_name": "steak",
          "quantity": 3000,
          "saved_co2": 20000
        },
        {
          "product_id": 1,
          "product_name": "steak",
          "quantity": 3000,
          "saved_co2": 20000
        },
        {
          "product_id": 1,
          "product_name": "steak",
          "quantity": 3000,
          "saved_co2": 20000
        }
      ]
    },
    {
      "order_id": 95,
      "destination_country": "Italy",
      "sold_on": "2025-01-27 09:52:22",
      "products": [
        {
          "product_id": 1,
          "product_name": "steak",
          "quantity": 320,
          "saved_co2": 20000
        }
      ]
    }
  ]
}
```
If no products are found:

`Status 404`
```json
{
  "message": "No orders found"
}
```
#### POST
This method allows to add an order to the available list. You will need to pass the following properties with the appropriate values.

| Property Name | Value Type | Notes |
| --- | --- | --- | 
| destination_country | String | |
| products | Array | All items must be valid Product objects (see `/products` endpoint)|


For example:

`POST /orders`

With json body:

```
{
  "data": {
    "destination_country": "Italy",
    "products": [
      {
        "product_id": 1,
        "quantity": 320
      }
    ]
  }
}
```

Will result in:

`Status 202`
```json
{
  "data": {
    "order_id": 96,
    "destination_country": "italy",
    "sold_on": "2025-01-27 15:41:18",
    "products": [
      {
        "product_id": 1,
        "product_name": "steak",
        "quantity": 320,
        "saved_co2": 20000
      }
    ]
  },
  "message": "Order placed successfully",
  "location": "/orders/96"
}
```
### /orders/:id
Where `:id` __MUST__ be an integer number.

The enpoint will respond with an Order object matching the `order_id` property with the given id.

#### GET
This method gets the information of a specific order as a Orders object.

For example:

`GET /orders/96`

Will result in:

`Status 202`
```json
{
  "data": {
    "order_id": 94,
    "destination_country": "USA",
    "sold_on": "2025-01-10 14:16:33",
    "products": [
      {
        "product_id": 1,
        "product_name": "steak",
        "quantity": 3000,
        "saved_co2": 20000
      },
      {
        "product_id": 2,
        "product_name": "venison",
        "quantity": 1500,
        "saved_co2": 13456
      },
      {
        "product_id": 3,
        "product_name": "porkchop",
        "quantity": 1500,
        "saved_co2": 19427
      }
    ]
  }
}
```
If no products are found:

`Status 404`
```json
{
  "message": "Order not found"
}
```

#### PUT
This method updates the specific order with new values. All properties are optional.

> WARNING if no resource is found with said id then a new resource is added. It is equivalent do the `POST /products` method.

For example:

`PUT /orders/1`

With json body:
```json
{
  "data": {
    "destination_country": "USA",
    "sold_on": "2025-01-10 14:16:33",
    "products": [
      {
        "product_id": 1,
        "quantity": 3000
      },
      {
        "product_id": 2,
        "quantity": 1500
      }
    ]
  }
}
```

Will result in:

`Status 200`
```json
{
    "data": {
        "order_id": 94,
        "destination_country": "usa",
        "sold_on": "2025-01-10 14:16:33",
        "products": [
            {
                "product_id": 1,
                "product_name": "steak",
                "quantity": 3000,
                "saved_co2": 20000
            },
            {
                "product_id": 2,
                "product_name": "venison",
                "quantity": 1500,
                "saved_co2": 13456
            },
            {
                "product_id": 3,
                "product_name": "porkchop",
                "quantity": 1500,
                "saved_co2": 19427
            }
        ]
    }
}
```

If the resource does not exist it will be created. The response will be:

`Status 201`
```json
{
  "data": {
    "order_id": 97,
    "destination_country": "usa",
    "sold_on": "2025-01-27 15:52:47",
    "products": [
      {
        "product_id": 1,
        "product_name": "steak",
        "quantity": 3000,
        "saved_co2": 20000
      },
      {
        "product_id": 2,
        "product_name": "venison",
        "quantity": 1500,
        "saved_co2": 13456
      }
    ]
  },
  "message": "Order placed successfully",
  "location": "/orders/97"
}
```

#### DELETE
This method deletes the specific order. Successive calls on the same resource after deleteion will result in a 404 status code.

Deletion will always return the deleted product as an Order object.

For example:

`DELETE /products/96`

Will result in:

`Status 200`
```json
{
  "data": {
    "order_id": 96,
    "destination_country": "italy",
    "sold_on": "2025-01-27 15:41:18",
    "products": [
      {
        "product_id": 1,
        "product_name": "steak",
        "quantity": 320,
        "saved_co2": 20000
      }
    ]
  },
  "message": "Order was deleted successfully"
}
```

If the resource was already deleted the response will be:

`Status 404`
```json
{
  "message": "Error Processing Request: Item with id 96 not found."
}
```
### /orders/:id/:resource

#### GET
If `resource` exist within the order then return it. It returns the value of the resource.

For example:

`GET /orders/94/products`

Will result in:

`Status 202`
```json
{
  "data": [
    {
      "product_id": 1,
      "product_name": "steak",
      "quantity": 3000,
      "saved_co2": 20000
    },
    {
      "product_id": 2,
      "product_name": "venison",
      "quantity": 1500,
      "saved_co2": 13456
    },
    {
      "product_id": 3,
      "product_name": "porkchop",
      "quantity": 1500,
      "saved_co2": 19427
    }
  ]
}
```
Example 2:

`GET /orders/94/destination_country`

Will result in:

`Status 202`
```json
{
  "data": "usa"
}
```

### /totalco2
This endpoint supports url query parameters to refine your research.

####  GET
Return an integer that is the sum of all the saved co2 from the Orders. Search can be refined with the following URL parameters:

| Parameter Name | Type | Notes |
| --- | --- | --- | 
| from | DateTime | Format is "YYYY-MM-DD HH:mm:ss"|
| to | DateTime | Format is "YYYY-MM-DD HH:mm:ss" |
| pid | Integer | Is the product id |
| country | String | Case insensitive |

For example:

`GET /totalco2`

Will result in:

`Status 202`
```json
{
  "data": {
    "saved_co2": 72883
  }
}
```
A failed request returns:

`Status 400`
```json
{
  "message": "Request malformed please retry. Follow The documentation if needed."
}
```
Example 2:

`GET /totalco2?from=2025-01-01&pid=1&country=usa&to=2025-01-27`

Will result in:

`Status 202`
```json
{
  "data": {
    "saved_co2": 20000
  }
}
```
