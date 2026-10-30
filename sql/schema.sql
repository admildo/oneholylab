-- oneholylab database schema (MySQL 8 / MariaDB 10.4+)
-- Run once:  mysql -u USER -p DBNAME < sql/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_log, stock_movements, payments, order_items, orders,
    cart_items, carts, product_images, product_variants, products, categories,
    addresses, users;

SET FOREIGN_KEY_CHECKS = 1;

-- Customers and admins share one table; `role` separates them.
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(80)  NOT NULL,
    last_name     VARCHAR(80)  NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    phone         VARCHAR(30)  NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    marketing_opt_in TINYINT(1) NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE addresses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    line1       VARCHAR(190) NOT NULL,
    city        VARCHAR(100) NOT NULL,
    province    VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20)  NOT NULL,
    country     VARCHAR(100) NOT NULL DEFAULT 'South Africa',
    is_default  TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(100) NOT NULL,
    slug  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE products (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name        VARCHAR(190) NOT NULL,
    slug        VARCHAR(190) NOT NULL UNIQUE,
    description TEXT NULL,
    price       DECIMAL(10,2) NOT NULL,
    colour      VARCHAR(50) NULL,
    status      ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_products_status (status),
    FULLTEXT KEY ft_products_search (name, description)
) ENGINE=InnoDB;

-- One row per sellable size. Stock lives here.
CREATE TABLE product_variants (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    sku        VARCHAR(60) NOT NULL UNIQUE,
    size       VARCHAR(20) NOT NULL,
    stock      INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_variant (product_id, size)
) ENGINE=InnoDB;

CREATE TABLE product_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    path       VARCHAR(255) NOT NULL,
    alt        VARCHAR(190) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- A cart belongs to a logged-in user or, for guests, to a session token.
CREATE TABLE carts (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NULL,
    session_token CHAR(64) NULL UNIQUE,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id    INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NOT NULL,
    quantity   INT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (cart_id)    REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    UNIQUE KEY uq_cart_variant (cart_id, variant_id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_no       VARCHAR(20) NOT NULL UNIQUE,
    user_id        INT UNSIGNED NOT NULL,
    status         ENUM('pending','paid','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    subtotal       DECIMAL(10,2) NOT NULL,
    delivery_fee   DECIMAL(10,2) NOT NULL DEFAULT 0,
    total          DECIMAL(10,2) NOT NULL,
    delivery_method VARCHAR(40) NOT NULL,
    ship_name      VARCHAR(160) NOT NULL,
    ship_line1     VARCHAR(190) NOT NULL,
    ship_city      VARCHAR(100) NOT NULL,
    ship_province  VARCHAR(100) NOT NULL,
    ship_postal    VARCHAR(20)  NOT NULL,
    ship_country   VARCHAR(100) NOT NULL,
    tracking_no    VARCHAR(80) NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB;

-- Name and price are copied at purchase time so later edits never change history.
CREATE TABLE order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    variant_id   INT UNSIGNED NULL,
    product_name VARCHAR(190) NOT NULL,
    size         VARCHAR(20) NOT NULL,
    unit_price   DECIMAL(10,2) NOT NULL,
    quantity     INT UNSIGNED NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    provider     VARCHAR(40) NOT NULL,
    provider_ref VARCHAR(120) NULL,
    amount       DECIMAL(10,2) NOT NULL,
    status       ENUM('pending','succeeded','failed','refunded') NOT NULL DEFAULT 'pending',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Every stock change is recorded: who, why, and by how much.
CREATE TABLE stock_movements (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variant_id INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NULL,
    change_qty INT NOT NULL,
    reason     ENUM('order','restock','adjustment','cancellation') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Admin actions (product edits, status changes) for tracking changes.
CREATE TABLE audit_log (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    action     VARCHAR(60)  NOT NULL,
    entity     VARCHAR(40)  NOT NULL,
    entity_id  INT UNSIGNED NULL,
    detail     VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
