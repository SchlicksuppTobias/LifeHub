CREATE TABLE IF NOT EXISTS shopping_lists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unique_id CHAR(36) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL DEFAULT 'Einkaufsliste',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_shopping_lists_unique_id (unique_id),
    KEY idx_shopping_lists_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shopping_list_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    list_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    amount VARCHAR(50) DEFAULT NULL,
    unit VARCHAR(20) DEFAULT NULL,
    checked TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_shopping_list_items_list_id (list_id),
    CONSTRAINT fk_shopping_list_items_list
        FOREIGN KEY (list_id) REFERENCES shopping_lists(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
