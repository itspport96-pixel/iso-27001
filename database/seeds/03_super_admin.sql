-- ==========================================================
-- USUARIO SUPER ADMIN INICIAL
-- ==========================================================

USE iso_platform;

-- 1. Empresa ficticia para super_admin
INSERT INTO empresas (ruc, razon_social, contacto) VALUES
('00000000000','Entropic Networks','{"email":"admin@entropic.pe","telefono":"+51 1 2345678"}');

-- 2. Usuario super_admin
INSERT INTO usuarios (empresa_id, nombre, email, password_hash, rol, estado) VALUES
(1,'Super Administrador','admin@entropic.pe','$argon2id$v=19$m=65536,t=4,p=1$MEtVb3dRdW9Md1R0NFM2QQ$KqH3bPb3qQ8KB4zX3mYz9mOP3Y5Kz9YzKz9YzKz9YzKz9Y','super_admin','activo');
