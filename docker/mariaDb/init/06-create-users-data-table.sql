CREATE TABLE IF NOT EXISTS usersData (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,

    phone VARCHAR(50) NULL,

    street VARCHAR(255) NULL,
    house_number VARCHAR(20) NULL,
    postal_code VARCHAR(20) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    country VARCHAR(100) NULL,

    birth_date DATE NULL,
    weight INT NULL,
    height INT NULL,

    bio TEXT NULL,
    avatar TEXT NULL,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id)
);

INSERT INTO usersData (
    id,
    first_name,
    last_name,
    email,
    phone,
    street,
    house_number,
    postal_code,
    city,
    state,
    country,
    birth_date,
    weight,
    height,
    bio,
    avatar
) VALUES (
    1,
    'Max',
    'Mustermann',
    'max@example.com',
    '01761234567',
    'Musterstraße',
    '12A',
    '70173',
    'Stuttgart',
    'Baden-Württemberg',
    'Deutschland',
    '1990-01-01',
    80,
    180,
    'Ich liebe gutes Essen 🍝',
    NULL
         );