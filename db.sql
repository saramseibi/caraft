CREATE TABLE  db (
    id int(11) NOT NULL AUTO_INCREMENT,
    full_name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    phone varchar(20) DEFAULT NULL,
    address text DEFAULT NULL,
    profile varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
);
CREATE TABLE  products (
    id int(11) NOT NULL AUTO_INCREMENT,
    name varchar(100) NOT NULL,
    description text DEFAULT NULL,
    price decimal(10,2) NOT NULL,
    image varchar(255) DEFAULT NULL,
    stock_quantity int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE TABLE  orders (
    id int(11) NOT NULL AUTO_INCREMENT,
    db_id int(11) NOT NULL,
    order_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status varchar(50) NOT NULL DEFAULT 'pending',
    shipping_address text,
    payment_method varchar(50),
    total_amount decimal(10,2) NOT NULL,
    tracking_number varchar(100),
    notes text,
    PRIMARY KEY (id),
    FOREIGN KEY (db_id) REFERENCES db(id)
);

CREATE TABLE liked_order (
    id int(11) NOT NULL AUTO_INCREMENT,
    db_id int(11) NOT NULL,
    product_id int(11) NOT NULL,
    date_added datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (db_id) REFERENCES db(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY db_product (db_id, product_id)
);
CREATE TABLE cart (
    id int(11) NOT NULL AUTO_INCREMENT,
    db_id int(11) NOT NULL,
    product_id int(11) NOT NULL,
    quantity int(11) NOT NULL DEFAULT 1,
    date_added datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (db_id) REFERENCES db(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY db_product (db_id, product_id)
);