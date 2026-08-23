<?php
declare(strict_types=1);
function apply_security_headers(): void{if(headers_sent())return;header('X-Content-Type-Options: nosniff');header('X-Frame-Options: SAMEORIGIN');header('Referrer-Policy: strict-origin-when-cross-origin');header('Permissions-Policy: camera=(), microphone=(), geolocation=()');header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");}
function render_product_error(string $title,string $message,int $status=500): never{http_response_code($status);apply_security_headers();$safeTitle=htmlspecialchars($title,ENT_QUOTES,'UTF-8');$safeMessage=htmlspecialchars($message,ENT_QUOTES,'UTF-8');$back=$_SERVER['HTTP_REFERER']??'';$backSafe=$back!==''&&str_starts_with($back,'/')&&!str_starts_with($back,'//')?htmlspecialchars($back,ENT_QUOTES,'UTF-8'):'';echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#2563eb"><title>Weblogr · '. $safeTitle .'</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#f5f7fb;color:#172033;font-family:Inter,system-ui,-apple-system,sans-serif}.card{width:min(520px,100%);padding:36px;background:#fff;border:1px solid #e6eaf0;border-radius:22px;box-shadow:0 18px 45px rgba(23,32,51,.09);text-align:center}.mark{width:58px;height:58px;margin:0 auto 18px;border-radius:17px;display:grid;place-items:center;background:#eef4ff;color:#2563eb;font-weight:800;font-size:25px}.eyebrow{margin:0 0 7px;color:#2563eb;font-size:11px;font-weight:800;letter-spacing:.12em}.card h1{margin:0 0 10px;font-size:28px;letter-spacing:-.04em}.card p{color:#697386;line-height:1.6}.message{margin-top:18px;padding:13px 15px;border:1px solid #fecaca;border-radius:11px;background:#fef2f2;color:#b42318;text-align:left;font-size:13px}.actions{display:flex;justify-content:center;gap:10px;margin-top:24px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 17px;border-radius:10px;text-decoration:none;font-weight:700;background:#2563eb;color:#fff}.secondary{background:#eef2f7;color:#172033}@media(max-width:520px){.actions{flex-direction:column}.button{width:100%}}</style></head><body><main class="card"><div class="mark">W</div><p class="eyebrow">WEBLOGR</p><h1>'.$safeTitle.'</h1><p>We could not complete your request right now.</p><div class="message" role="alert">'.$safeMessage.'</div><div class="actions"><a class="button" href="/">Weblogr home</a>'.($backSafe?'<a class="button secondary" href="'.$backSafe.'">Go back</a>':'').'</div></main></body></html>';exit;}
function start_secure_session(): void{apply_security_headers();if(session_status()===PHP_SESSION_ACTIVE)return;$secure=!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off';session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Lax']);session_start();}
function csrf_token(): string{start_secure_session();if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));return $_SESSION['csrf_token'];}
function verify_csrf(): void{start_secure_session();$sessionToken=(string)($_SESSION['csrf_token']??'');$requestToken=(string)($_POST['csrf_token']??'');if($sessionToken===''||$requestToken===''||!hash_equals($sessionToken,$requestToken))render_product_error('Security check failed','Your form security token is missing or expired. Please return to the form and submit it again.',419);}
function require_authentication(): int{start_secure_session();if(!isset($_SESSION['user_id'])){header('Location: ../registration/login.php');exit;}return (int)$_SESSION['user_id'];}
function rate_limit_key(string $scope,string $identifier): string{return hash('sha256',$scope.'|'.strtolower(trim($identifier)).'|'.($_SERVER['REMOTE_ADDR']??'unknown'));}
// Persistent (DB-backed) rate limiting. Session-based limits are trivially bypassed by any
// client that doesn't retain the session cookie (fresh incognito tab, scripted requests, etc.),
// so attempt counters live in the `rate_limits` table instead, keyed by scope+identifier+IP.
function enforce_rate_limit(string $scope,string $identifier,int $maxAttempts,int $windowSeconds): void{
    require_once __DIR__.'/../database/db.php';
    $key=rate_limit_key($scope,$identifier);
    $now=time();
    $select=$con->prepare('SELECT attempt_count,UNIX_TIMESTAMP(window_started_at) started FROM rate_limits WHERE limit_key=? LIMIT 1');
    $select->bind_param('s',$key);$select->execute();$row=$select->get_result()->fetch_assoc();$select->close();
    if($row!==null && ($now-(int)$row['started'])>$windowSeconds){
        $reset=$con->prepare('UPDATE rate_limits SET attempt_count=0,window_started_at=NOW() WHERE limit_key=?');
        $reset->bind_param('s',$key);$reset->execute();$reset->close();
        $row=['attempt_count'=>0,'started'=>$now];
    }
    if($row!==null && (int)$row['attempt_count']>=$maxAttempts){$con->close();render_product_error('Too many requests','Please wait a little while before trying again.',429);}
    $upsert=$con->prepare('INSERT INTO rate_limits (limit_key,attempt_count,window_started_at) VALUES (?,1,NOW()) ON DUPLICATE KEY UPDATE attempt_count=attempt_count+1');
    $upsert->bind_param('s',$key);$upsert->execute();$upsert->close();
}
function enforce_login_rate_limit(string $identifier,int $maxAttempts=5,int $windowSeconds=900): void{enforce_rate_limit('login',$identifier,$maxAttempts,$windowSeconds);}
function record_failed_login(string $identifier): void{/* Kept as a no-op call site for backward compatibility; enforce_rate_limit() already records every attempt (successful or not) at call time. */}
function clear_login_rate_limit(string $identifier): void{
    require_once __DIR__.'/../database/db.php';
    $key=rate_limit_key('login',$identifier);
    $delete=$con->prepare('DELETE FROM rate_limits WHERE limit_key=?');
    $delete->bind_param('s',$key);$delete->execute();$delete->close();
}
