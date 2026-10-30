<?php

#require_once __DIR__ . "/init.php";

if($_SERVER['REQUEST_METHOD'] == "POST" || isset($_POST)){
    $senha = $_POST['senha'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    
    if(
        strlen(trim($senha)) > 50) {
            header("Location: criarConta.php?erro=SENHA");
            exit;
        }elseif(
        strlen(trim($senha)) < 8 || $senha == '' || !isset($senha)) {
            header("Location: criarConta.php?erro=SENHA_ERRADO");
            exit;
        }elseif(
        strlen(trim($email)) > 100){
            header("Location: criarConta.php?erro=EMAIL");
            exit;
        }elseif(
        $email == '' || !isset($email) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)){
            header("Location: criarConta.php?erro=EMAIL_ERRADO");
            exit;
        }elseif(
        strlen(trim($username)) > 20){
            header("Location: criarConta.php?erro=USERNAME");
            exit;
        }elseif(
        (!isset($username) || $username == '' || strlen(trim($username)) < 5) ){
            header("Location: criarConta.php?erro=USERNAME_ERRADO");
            exit;
    }else{
        header("Location: criarConta.php");
        # ainda vou adicionar mais coisa
    }
}