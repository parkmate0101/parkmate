<?php
include "db_connect.php";
$adminPassword = password_hash("Admin@123", PASSWORD_DEFAULT);
$conn->query("
INSERT IGNORE INTO users (name,email,contact,password,role_id,is_verified)
VALUES ('admin','parkmate0101@gmail.com','7048515917','$adminPassword',1, 1)");
if ($conn->affected_rows > 0) {
    echo "Admin created successfully";
} else {
    echo "Admin already exists";
}
?>