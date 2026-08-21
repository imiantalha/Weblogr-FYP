<?php

declare(strict_types=1);
require '../includes/security.php';
$user_id=require_authentication();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);header('Allow: POST');exit('Method not allowed.');}
verify_csrf();require '../database/db.php';
$blog_id=filter_var($_POST['blog_id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$reason=trim((string)($_POST['reason']??''));$details=trim((string)($_POST['details']??''));
$allowed=['spam','harassment','hate or abuse','misinformation','copyright','other'];
if(!$blog_id||!in_array($reason,$allowed,true)){http_response_code(422);exit('Please provide a valid report reason.');}
$s=$con->prepare('SELECT blog_id FROM blogs WHERE blog_id=? LIMIT 1');$s->bind_param('i',$blog_id);$s->execute();$exists=$s->get_result()->num_rows===1;$s->close();if(!$exists){$con->close();http_response_code(404);exit('Post not found.');}
$s=$con->prepare("SELECT report_id FROM reports WHERE blog_id=? AND reporter_id=? AND status='pending' LIMIT 1");$s->bind_param('ii',$blog_id,$user_id);$s->execute();$duplicate=$s->get_result()->num_rows===1;$s->close();
if($duplicate){$con->close();header('Location: index.php?report=already-submitted');exit;}
$s=$con->prepare('INSERT INTO reports(blog_id,reporter_id,reason,details) VALUES(?,?,?,?)');$s->bind_param('iiss',$blog_id,$user_id,$reason,$details);$s->execute();$s->close();$con->close();header('Location: index.php?report=submitted');exit;
