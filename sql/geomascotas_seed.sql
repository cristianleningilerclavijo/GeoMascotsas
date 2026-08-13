-- GeoMascotas - Datos de prueba
-- Ejecutar después de geomascotas_schema.sql
-- Usuario demo: demo@geomascotas.test / demo1234

USE geomascotas;

INSERT INTO owners (full_name, email, password_hash, phone) VALUES
('Cristian Giler', 'demo@geomascotas.test', '$2y$10$lYSJEltgCHXN7hFSLi4HzuDxXgQnZzmj.yc.t54JnNZC6h7OsXe8m', '0991234567');

INSERT INTO pets (owner_id, name, species, breed, color, medical_notes, status) VALUES
(1, 'Firulais', 'perro', 'Mestizo', 'Café y blanco', 'Alérgico a la penicilina', 'perdido');

INSERT INTO qr_tokens (pet_id, token, is_active) VALUES
(1, '3f9a1c2e-8b7d-4e21-9c3a-1a2b3c4d5e6f', 1);
