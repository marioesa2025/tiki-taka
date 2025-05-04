<?php
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function authenticate_user($email, $password, $conn) {
    $stmt = $conn->prepare("SELECT id, nombre, password, aprobado FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

function register_user($nombre, $email, $password, $foto_perfil, $conn) {
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, foto_perfil) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $password, $foto_perfil);
    return $stmt->execute();
}

function upload_photo($file) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . time() . '.' . $ext;
        $target = 'assets/img/usuarios/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $filename;
        }
    }
    return 'default.jpg';
}
?>