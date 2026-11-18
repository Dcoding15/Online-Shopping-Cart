# Online Shopping Cart - Website Flowchart

This document visualizes the main visitor, buyer, seller, administrator, and payment flows.

## Overall Website Flow

```mermaid
flowchart TD
    A([Visitor opens website]) --> B{Has account?}

    B -->|No| C[Register]
    C --> D{Select role}
    D -->|Buyer| E[Create buyer account]
    D -->|Seller| F[Create seller account]
    E --> G[Login automatically]
    F --> G

    B -->|Yes| H[Login]
    H --> I{Credentials valid?}
    I -->|No| J[Show login error]
    J --> H
    I -->|Yes| G

    G --> K{Authenticated role}
    K -->|Buyer| L[Buyer dashboard]
    K -->|Seller| M[Seller dashboard]
    K -->|Administrator| N[Admin dashboard]

    A --> O[Browse public catalogue]
    O --> P[Search and filter products]
    P --> Q[View product details]
    Q --> R{Signed in as buyer?}
    R -->|Yes| S[Add product to cart]
    R -->|No| H

    L --> O
    L --> T[View cart]
    T --> U[Update or remove items]
    U --> V[Proceed to checkout]
    V --> W[Enter shipping address]
    W --> X[Create Razorpay order]
    X --> Y{Payment successful?}
    Y -->|No| Z[Return to checkout]
    Y -->|Yes| AA[Verify payment signature]
    AA --> AB{Enough stock?}
    AB -->|Yes| AC[Confirm order]
    AC --> AD[Reduce stock]
    AD --> AE[Clear cart]
    AE --> AF[View order invoice]
    AB -->|No| AG[Request payment refund]
    AG --> AH[Do not fulfill order]

    M --> AI[View own products]
    AI --> AJ[Create product]
    AI --> AK[Edit product]
    AI --> AL[Delete own unpurchased product]

    N --> AM[Manage users]
    N --> AN[Manage sellers]
    N --> AO[Manage products]
    N --> AP[View marketplace orders]
    AM --> AQ[Edit or delete user]
    AN --> AR[Create, edit, or delete seller]
    AO --> AS{Product purchased?}
    AS -->|Yes| AT[Block deletion]
    AS -->|No| AU[Delete product]
    AP --> AV[View order details]
```

## Authentication and Validation Flow

```mermaid
flowchart LR
    A[Registration form] --> B[Enter name]
    B --> C{Name contains only A-Z or a-z?}
    C -->|No| D[Remove invalid characters / show error]
    C -->|Yes| E[Enter 10-digit phone number]

    E --> F{Exactly 10 digits?}
    F -->|No| G[Show phone validation error]
    F -->|Yes| H[Enter address]

    H --> I{Only letters, digits, spaces, comma, hyphen, parentheses?}
    I -->|No| J[Remove invalid characters / show error]
    I -->|Yes| K[Enter strong password]

    K --> L{Password meets strength rules?}
    L -->|No| M[Show password error]
    L -->|Yes| N[Confirm password]

    N --> O{Passwords match?}
    O -->|No| P[Show confirmation error]
    O -->|Yes| Q[Create account]
    Q --> R[Hash password]
    R --> S[Log in user]
```

## Buyer Purchase Flow

```mermaid
sequenceDiagram
    actor Buyer
    participant Browser
    participant Laravel
    participant Database
    participant Razorpay

    Buyer->>Browser: Browse catalogue
    Browser->>Laravel: Search or view product
    Laravel->>Database: Fetch products
    Database-->>Laravel: Product data
    Laravel-->>Browser: Render catalogue

    Buyer->>Browser: Add product
    Browser->>Laravel: Add to cart
    Laravel->>Database: Save cart item
    Database-->>Laravel: Cart updated

    Buyer->>Browser: Proceed to checkout
    Browser->>Laravel: Submit shipping address
    Laravel->>Database: Create pending order and order items
    Laravel->>Razorpay: Create payment order
    Razorpay-->>Buyer: Open Checkout
    Buyer->>Razorpay: Complete payment
    Razorpay-->>Browser: Payment response

    Browser->>Laravel: Send signature and payment IDs
    Laravel->>Razorpay: Verify payment and fetch details
    Razorpay-->>Laravel: Captured payment details
    Laravel->>Database: Verify stock

    alt Stock available
        Laravel->>Database: Mark order paid
        Laravel->>Database: Reduce stock
        Laravel->>Database: Clear cart
        Laravel-->>Browser: Redirect to invoice
    else Stock unavailable
        Laravel->>Razorpay: Request refund
        Laravel->>Database: Keep order from fulfillment
    end
```

## Seller Product Management Flow

```mermaid
flowchart TD
    A[Seller logs in] --> B[Open Seller Dashboard]
    B --> C[Open My Products]
    C --> D{Choose action}

    D -->|Create| E[Enter product details]
    E --> F[Upload optional picture]
    F --> G[Save product]
    G --> H[Product appears in catalogue]

    D -->|Edit| I[Open owned product]
    I --> J[Update details]
    J --> K[Save changes]

    D -->|Delete| L{Product is owned by seller?}
    L -->|No| M[Reject request]
    L -->|Yes| N{Product has been purchased?}
    N -->|Yes| O[Block seller deletion]
    N -->|No| P[Delete product]
```

## Administrator Management Flow

```mermaid
flowchart TD
    A[Administrator logs in] --> B[Open Admin Dashboard]
    B --> C{Select management area}

    C -->|Users| D[View and search users]
    D --> E[Edit user role or details]
    D --> F{User owns active products?}
    F -->|Yes| G[Block account deletion]
    F -->|No| H[Delete account]

    C -->|Sellers| I[View seller list]
    I --> J[Create or edit seller]
    I --> K{Seller owns products?}
    K -->|Yes| L[Block seller deletion]
    K -->|No| M[Delete seller]

    C -->|Products| N[View platform products]
    N --> O{Product has paid/purchased order?}
    O -->|Yes| P[Disable Remove action]
    P --> Q[Show purchased-product error]
    O -->|No| R[Remove product]

    C -->|Orders| S[View orders]
    S --> T[Open order invoice]
    T --> U[Review buyer, items, totals, and payment]
```

## Database Relationship Flow

```mermaid
erDiagram
    USERS ||--o{ PRODUCTS : owns
    USERS ||--o{ CART : has
    USERS ||--o{ ORDERS : places
    USERS ||--o{ ORDER_ITEMS : supplies
    PRODUCTS ||--o{ CART : "may appear in"
    PRODUCTS o|--o{ ORDER_ITEMS : "may be referenced by"
    ORDERS ||--|{ ORDER_ITEMS : contains

    USERS {
        bigint user_id PK
        string user_name
        string phone_no
        string user_addr
        enum user_type
        string password
    }

    PRODUCTS {
        bigint product_id PK
        bigint user_id FK
        string product_name
        decimal product_price
        integer product_available_quantity
    }

    CART {
        bigint cart_id PK
        bigint user_id FK
        bigint product_id FK
        integer quantity
    }

    ORDERS {
        bigint order_id PK
        bigint user_id FK
        decimal order_total_amount
        string order_status
        boolean payment_confirmed
    }

    ORDER_ITEMS {
        bigint order_item_id PK
        bigint order_id FK
        bigint product_id FK
        bigint seller_id FK
        integer quantity
        decimal unit_price
    }
```
