<?php
require_once __DIR__ . '/db.php';

function register_user(PDO $pdo, $username, $email, $password) {
    $username = trim($username); $email = trim($email);
    if (strlen($username) < 3 || strlen($username) > 50) return 'Username must be 3-50 chars.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email.';
    if (strlen($password) < 6) return 'Password must be at least 6 characters.';

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) return 'Email or username already taken.';

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?,?,?)');
    $stmt->execute([$username, $email, $hash]);
    $id = (int)$pdo->lastInsertId();
    $_SESSION['user'] = ['id'=>$id,'username'=>$username,'email'=>$email,'is_admin'=>0,'avatar'=>'assets/images/default-avatar.png'];
    return true;
}

function login_user(PDO $pdo, $email, $password) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? OR username = ?');
    $stmt->execute([$email, $email]);
    $u = $stmt->fetch();
    if (!$u || !password_verify($password, $u['password'])) return 'Invalid credentials.';
    $_SESSION['user'] = [
        'id'=>(int)$u['id'],'username'=>$u['username'],'email'=>$u['email'],
        'is_admin'=>(int)$u['is_admin'],'avatar'=>$u['avatar']
    ];
    return true;
}
