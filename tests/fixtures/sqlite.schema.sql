CREATE TABLE product (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  name VARCHAR NOT NULL,
  rrp VARCHAR NOT NULL,
  ctime TIMESTAMP NOT NULL,
  mtime TIMESTAMP NOT NULL,
  UNIQUE(name)
);

CREATE TABLE country (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  name VARCHAR NOT NULL,
  ctime TIMESTAMP NOT NULL,
  mtime TIMESTAMP NOT NULL,
  UNIQUE(name)
);

CREATE TABLE shop (
  id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  name VARCHAR NOT NULL,
  city VARCHAR NOT NULL,
  country_id INTEGER NOT NULL,
  ctime TIMESTAMP NOT NULL,
  mtime TIMESTAMP NOT NULL,
  UNIQUE(name)
  FOREIGN KEY shop_country (country_id) REFERENCES country (id)
);

CREATE TABLE shop_product (
  shop_id INTEGER NOT NULL,
  product_id INTEGER NOT NULL,
  PRIMARY KEY (shop_id, product_id),
  FOREIGN KEY shop_product (shop_id) REFERENCES shop (id),
  FOREIGN KEY product_shop (product_id) REFERENCES product (id)
);

INSERT INTO product (id, name, rrp, ctime, mtime) VALUES
(1, 'iPhone 5', '$199/$299/$399', NOW(), NOW()),
(2, 'iPhone 5C', '$199/$299/$399', NOW(), NOW()),
(3, 'iPhone 5S', '$199/$299/$399', NOW(), NOW()),
(4, 'iPhone 6', '$649/$749/$849', NOW(), NOW()),
(5, 'iPhone 6 Plus', '$749/$849/$949', NOW(), NOW()),
(6, 'iPhone 6S', '$649/$749/$849', NOW(), NOW()),
(7, 'iPhone 6S Plus', '$749/$849/$949', NOW(), NOW()),
(8, 'iPhone SE', '$399/$499', NOW(), NOW()),
(9, 'iPhone 7', '$649/$749/$849 ', NOW(), NOW()),
(10, 'iPhone 7 Plus', '$769/$869/$969', NOW(), NOW()),
(11, 'iPhone 8', '$699/$849', NOW(), NOW()),
(12, 'iPhone 8 Plus', '$799/$949', NOW(), NOW()),
(13, 'iPhone X', '$999/$1149', NOW(), NOW()),
(14, 'iPhone XS', '$999/$1149/$1349', NOW(), NOW()),
(15, 'iPhone XS Max', '$1099/$1249/$1449', NOW(), NOW()),
(16, 'iPhone XR', '$749', NOW(), NOW());

INSERT INTO country (id, name, ctime, mtime) VALUES
(1, 'Germany', NOW(), NOW()),
(2, 'USA', NOW(), NOW()),
(3, 'China', NOW(), NOW()),
(4, 'Japan', NOW(), NOW());

INSERT INTO shop (id, name, city, country_id, ctime, mtime) VALUES
(1, 'Apple Store Berlin', 'Berlin', 1, NOW(), NOW()),
(2, 'Apple Store Munich', 'Munich', 1, NOW(), NOW()),
(3, 'Apple Store New York', 'New York', 2, NOW(), NOW()),
(4, 'Apple Store San Francisco', 'San Francisco', 2, NOW(), NOW()),
(5, 'Apple Store Beijing', 'Beijing', 3, NOW(), NOW()),
(6, 'Apple Store Shanghai', 'Shanghai', 3, NOW(), NOW()),
(7, 'Apple Store Tokio', 'Tokio', 4, NOW(), NOW()),
(8, 'Apple Store Yokohama', 'Yokohama', l4, NOW(), NOW());

INSERT INTO shop_product (shop_id, product_id)
SELECT shop.id AS shop_id, product.id AS product_id FROM shop, product;
