-- GeoMascotas - Esquema de base de datos
-- Importar en MySQL Workbench o vía phpMyAdmin (XAMPP)

CREATE DATABASE IF NOT EXISTS geomascotas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE geomascotas;

-- ---------------------------------------------------------------
-- owners: dueños de mascotas (usuarios del sistema)
-- ---------------------------------------------------------------
CREATE TABLE owners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- pets: mascotas registradas
-- ---------------------------------------------------------------
CREATE TABLE pets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  species ENUM('perro','gato','otro') NOT NULL DEFAULT 'perro',
  breed VARCHAR(100),
  color VARCHAR(50),
  photo_url VARCHAR(255),
  medical_notes TEXT,
  status ENUM('activo','perdido','recuperado') NOT NULL DEFAULT 'activo',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pets_owner FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- qr_tokens: token único por QR generado (permite regenerar sin perder historial)
-- ---------------------------------------------------------------
CREATE TABLE qr_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pet_id INT NOT NULL,
  token CHAR(36) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_qrtokens_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  INDEX idx_qrtokens_pet_active (pet_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- scan_logs: cada vez que alguien abre la ficha pública de una mascota
-- ---------------------------------------------------------------
CREATE TABLE scan_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  qr_token_id INT NOT NULL,
  pet_id INT NOT NULL,
  scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  CONSTRAINT fk_scanlogs_token FOREIGN KEY (qr_token_id) REFERENCES qr_tokens(id) ON DELETE CASCADE,
  CONSTRAINT fk_scanlogs_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
