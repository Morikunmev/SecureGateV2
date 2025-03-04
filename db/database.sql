ALTER TABLE usuarios ADD COLUMN status ENUM('pending', 'active') NOT NULL DEFAULT 'pending';

CREATE TABLE IF NOT EXISTS verificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    temp_qr_code VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (usuario_id),
    INDEX (token),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;