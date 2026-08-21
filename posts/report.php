<?php

declare(strict_types=1);
require '../includes/security.php';
require_authentication();
require '../database/db.php';

$blog_id=filter_var($_GET['blog_id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
$blogger_id=filter_var($_GET['blogger_id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$blog_id||!$blogger_id){http_response_code(400);exit('Invalid post.');}
$csrf=htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8');
$statement=$con->prepare('SELECT title,user_id FROM blogs WHERE blog_id=? LIMIT 1');$statement->bind_param('i',$blog_id);$statement->execute();$blog=$statement->get_result()->fetch_assoc();$statement->close();
if(!$blog||((int)$blog['user_id']!==$blogger_id)){http_response_code(404);exit('Post not found.');}
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$content=trim((string)($_POST['content']??''));if($content===''){http_response_code(422);exit('Report details are required.');}if(mb_strlen($content)>2000){http_response_code(422);exit('Report is too long.');}$reporter_id=(int)$_SESSION['user_id'];if($reporter_id===$blogger_id){http_response_code(422);exit('You cannot report your own post.');}try{$con->begin_transaction();$statement=$con->prepare('INSERT INTO reports (blog_id,blogger_id,reporter_id,content) VALUES (?,?,?,?)');$statement->bind_param('iiis',$blog_id,$blogger_id,$reporter_id,$content);$statement->execute();$statement->close();$notification="A post was reported (Blog ID: $blog_id): $content";$statement=$con->prepare('INSERT INTO notifications (content,user_id) VALUES (?,?)');$statement->bind_param('si',$notification,$blogger_id);$statement->execute();$statement->close();$con->commit();$con->close();header('Location: index.php?reported=1');exit;}catch(Throwable $e){$con->rollback();$con->close();error_log('Report failed: '.$e->getMessage());http_response_code(500);exit('Unable to submit report.');}}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Report Post | Weblogr</title><link rel="stylesheet" href="style.css"></head><body><?php include 'sidebar.php';?><main class="container"><section class="page-header"><div><p class="eyebrow">COMMUNITY SAFETY</p><h1>Report this post</h1><p>Help keep Weblogr useful by telling us what needs attention.</p></div></section><article class="report-card"><h2><?php echo htmlspecialchars((string)$blog['title'],ENT_QUOTES,'UTF-8');?></h2><form method="post"><input type="hidden" name="csrf_token" value="<?php echo $csrf;?>"><label for="content">Why are you reporting it?</label><textarea id="content" name="content" rows="7" maxlength="2000" required placeholder="Describe the issue..."></textarea><div class="form-actions"><a href="index.php" class="secondary-button">Cancel</a><button id="save-btn" type="submit">Submit Report</button></div></form></article></main></body></html>
