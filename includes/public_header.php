<?php
declare(strict_types=1);
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/public_helpers.php';
start_secure_session();

$is_authenticated = isset($_SESSION['user_id']);
$current_path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$home_url = public_url('index.html');
$discover_url = public_url('blog.php');
$login_url = public_url('registration/login.php');
$profile_url = public_url('registration/profile.php');
$logout_url = public_url('posts/logout.php');
?>
<link rel="icon" href="<?=e(public_url('assets/weblogr-mark.svg'))?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?=e(public_url('assets/weblogr-mark.svg'))?>">
<header class="site-header">
<nav class="nav" aria-label="Primary">
<a class="brand" href="<?=e($home_url)?>" aria-label="Weblogr home"><img class="brand-logo" src="<?=e(public_url('assets/weblogr-mark.svg'))?>" alt="" aria-hidden="true"><span>Weblogr</span></a>
<button class="mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="public-navigation"><i class="fas fa-bars"></i></button>
<div class="nav-links" id="public-navigation">
<a href="<?=e($discover_url)?>"<?=str_contains($current_path, '/blog.php') ? ' aria-current="page"' : ''?>>Discover</a>
<a href="<?=e(public_url('about.html'))?>"<?=str_contains($current_path, '/about.html') ? ' aria-current="page"' : ''?>>About</a>
<?php if ($is_authenticated): ?>
<a class="nav-account" href="<?=e($profile_url)?>"><i class="fas fa-user-circle" aria-hidden="true"></i> @<?=e((string)($_SESSION['username'] ?? 'Writer'))?></a>
<form method="post" action="<?=e($logout_url)?>" class="public-logout-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><button type="submit" class="public-logout"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Log out</button></form>
<?php else: ?>
<a href="<?=e($login_url)?>">Log in</a>
<a class="nav-cta" href="<?=e(public_url('registration/signup.php'))?>">Start writing</a>
<?php endif; ?>
</div>
</nav>
</header>
<style>
.public-logout-form{display:inline-flex;margin:0}.public-logout{border:0;background:transparent;color:inherit;font:inherit;cursor:pointer;padding:0}.public-logout:hover{color:#2563eb}.nav-account{display:inline-flex;align-items:center;gap:7px}.public-logout-form{align-items:center}
@media(max-width:760px){.public-logout-form{display:block}.public-logout{width:100%;text-align:left;padding:10px 0}.nav-account{display:flex}}
</style>
<script>const m=document.querySelector('.mobile-menu'),n=document.querySelector('.nav-links');m?.addEventListener('click',()=>{const o=m.getAttribute('aria-expanded')==='true';m.setAttribute('aria-expanded',String(!o));n.classList.toggle('open');});</script>
