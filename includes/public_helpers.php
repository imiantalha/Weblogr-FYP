<?php
declare(strict_types=1);

if (!function_exists('e')) { function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); } }
function app_base_url(): string { $configured=trim((string)(getenv('APP_URL')?:'')); if($configured!=='') return rtrim($configured,'/'); $scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http'; return $scheme.'://'.($_SERVER['HTTP_HOST']??'localhost'); }
function slugify(string $value): string { $value=trim($value); $value=function_exists('iconv')?(string)iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value):$value; $value=strtolower($value); $value=preg_replace('/[^a-z0-9]+/','-',$value)??''; return trim($value,'-')?:'story'; }
function article_url(int $id,string $title): string { return app_base_url().'/read/'.$id.'/'.slugify($title); }
function author_url(int $id,string $username): string { return app_base_url().'/author/'.$id.'/'.slugify($username); }
function public_url(string $path): string { return app_base_url().'/'.ltrim($path,'/'); }
function excerpt(string $value,int $length=160): string { $text=trim((string)preg_replace('/\s+/',' ',strip_tags($value))); if(function_exists('mb_strlen')&&mb_strlen($text)>$length)return rtrim(mb_substr($text,0,$length-1)).'…'; if(strlen($text)>$length)return rtrim(substr($text,0,$length-1)).'…'; return $text; }
