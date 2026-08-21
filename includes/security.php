<?php

declare(strict_types=1);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Lax']);
    session_start();
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    start_secure_session();
    $sessionToken=(string)($_SESSION['csrf_token']??''); $requestToken=(string)($_POST['csrf_token']??'');
    if($sessionToken===''||$requestToken===''||!hash_equals($sessionToken,$requestToken)){http_response_code(419);exit('Invalid or expired security token. Please refresh the page and try again.');}
}

function require_authentication(): int
{
    start_secure_session();
    if(!isset($_SESSION['user_id'])){header('Location: ../registration/login.php');exit;}
    return (int)$_SESSION['user_id'];
}

function enforce_login_rate_limit(string $identifier, int $maxAttempts=5, int $windowSeconds=900): void
{
    start_secure_session();
    $now=time(); $key='login_attempts_'.hash('sha256',strtolower(trim($identifier)).'|'.($_SERVER['REMOTE_ADDR']??'unknown'));
    $data=$_SESSION[$key]??['count'=>0,'started'=>$now];
    if($now-(int)$data['started']>$windowSeconds)$data=['count'=>0,'started'=>$now];
    if((int)$data['count'] >= $maxAttempts){http_response_code(429);exit('Too many login attempts. Please try again later.');}
    $_SESSION[$key]=$data;
}

function record_failed_login(string $identifier): void
{
    start_secure_session();
    $key='login_attempts_'.hash('sha256',strtolower(trim($identifier)).'|'.($_SERVER['REMOTE_ADDR']??'unknown'));
    $data=$_SESSION[$key]??['count'=>0,'started'=>time()]; $data['count']=(int)$data['count']+1; $_SESSION[$key]=$data;
}

function clear_login_rate_limit(string $identifier): void
{
    start_secure_session();
    $key='login_attempts_'.hash('sha256',strtolower(trim($identifier)).'|'.($_SERVER['REMOTE_ADDR']??'unknown')); unset($_SESSION[$key]);
}
