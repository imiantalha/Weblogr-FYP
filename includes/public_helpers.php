<?php
declare(strict_types=1);

if (!function_exists('e')) { function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); } }
function app_base_url(): string { $configured=trim((string)(getenv('APP_URL')?:'')); if($configured!=='') return rtrim($configured,'/'); $scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http'; return $scheme.'://'.($_SERVER['HTTP_HOST']??'localhost'); }
function slugify(string $value): string { $value=trim($value); $value=function_exists('iconv')?(string)iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value):$value; $value=strtolower($value); $value=preg_replace('/[^a-z0-9]+/','-',$value)??''; return trim($value,'-')?:'story'; }
function article_url(int $id,string $title): string { return app_base_url().'/read/'.$id.'/'.slugify($title); }
function author_url(int $id,string $username): string { return app_base_url().'/author/'.$id.'/'.slugify($username); }
function public_url(string $path): string { return app_base_url().'/'.ltrim($path,'/'); }
function excerpt(string $value,int $length=160): string { $text=trim((string)preg_replace('/\s+/',' ',strip_tags($value))); if(function_exists('mb_strlen')&&mb_strlen($text)>$length)return rtrim(mb_substr($text,0,$length-1)).'…'; if(strlen($text)>$length)return rtrim(substr($text,0,$length-1)).'…'; return $text; }
function public_not_found(string $title='Story not found',string $message='The content you requested is unavailable or may have been removed.'): never { http_response_code(404); echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>'.e($title).' | Weblogr</title><link rel="icon" href="'.e(public_url('assets/weblogr-mark.svg')).'" type="image/svg+xml"><link rel="stylesheet" href="'.e(public_url('assets/public.css')).'"></head><body><main><div class="container" style="padding:90px 0"><div class="empty-state"><p class="eyebrow">404</p><h1>'.e($title).'</h1><p>'.e($message).'</p><a class="button button-primary" href="'.e(public_url('blog.php')).'">Explore stories</a></div></div></main></body></html>'; exit; }
