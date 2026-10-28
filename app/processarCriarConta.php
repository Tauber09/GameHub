<?php

require_once __DIR__ . "/init.php";

if($_SERVER['REQUEST_METHOD'] == "POST" || isset($_POST)){
    $senha = is_string($_POST['senha'] ?? null) ? trim($_POST['nome']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['email']) : '';
    
    if(
        strlen($senha) > 50 || $senha == '') {
            header("Location: criarConta.php?erro=SENHA");
            exit;
        }elseif(
        strlen($email) > 100 || $email == '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)){
            header("Location: criarConta.php?erro=EMAIL");
            exit;
        }elseif(
        strlen($username) > 20 || $username == ''){
            header("Location: criarConta.php?erro=USERNAME");
            exit;
    }
}