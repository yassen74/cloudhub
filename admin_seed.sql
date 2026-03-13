USE lms_db;
DELETE FROM admin;
INSERT INTO admin (admin_name, admin_email, admin_pass)
VALUES ('Admin','admin@gmail.com',MD5('123456'));
