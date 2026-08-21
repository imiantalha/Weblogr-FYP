<?php

declare(strict_types=1);

function apply_security_headers(): void
{
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Content-Security-Policy: default-src \'self\'; img-src \'self\' data: https:; style-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src \'self\' https://cdnjs.cloudflare.com https://fonts.gstatic.com; script-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com; frame-ancestors \'self\'; base-uri \'self\'; form-action \'self\'');
}

function start_secure_session(): void
{
    apply_security_headers();
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

function rate_limit_key(string $scope, string $identifier): string
{
    return 'rate_'.hash('sha256',$scope.'|'.strtolower(trim($identifier)).'|'.($_SERVER['REMOTE_ADDR']??'unknown'));
}

function enforce_rate_limit(string $scope, string $identifier, int $maxAttempts, int $windowSeconds): void
{
    start_secure_session(); $now=time(); $key=rate_limit_key($scope,$identifier);
    $data=$_SESSION[$key]??['count'=>0,'started'=>$now]; if($now-(int)$data['started']>$windowSeconds)$data=['count'=>0,'started'=>$now];
    if((int)$data['count'] >= $maxAttempts){http_response_code(429);exit('Too many requests. Please try again later.');} $data['count']++; $_SESSION[$key]=$data;
}

function enforce_login_rate_limit(string $identifier, int $maxAttempts=5, int $windowSeconds=900): void
{
    enforce_rate_limit('login', $identifier, $maxAttempts, $windowSeconds);
}

function record_failed_login(string $identifier): void
{
    start_secure_session(); $key='login_attempts_'.hash('sha256',strtolower(trim($identifier)).'|'.($_SERVER['REMOTE_ADDR']??'unknown')); $data=$_SESSION[$key]??['count'=>0,'started'=>time()]; $data['count']=(int)$data['count']+1; $_SESSION[$key]=$data;
}

function clear_login_rate_limit(string $identifier): void
{
    start_secure_session(); unset($_SESSION[rate_limit_key('login',$identifier)]); $key='login_attempts_'.hash('sha256',strtolower(trim($identifier)).'|'.($_SERVER['REMOTE_ADDR']??'unknown')); unset($_SESSION[$key]);
}
