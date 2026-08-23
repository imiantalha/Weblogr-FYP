<?php
declare(strict_types=1);
require '../includes/security.php';
require_authentication();
require '../database/db.php';

$draft_id = filter_var($_GET['draft_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$draft_id) {
    $con->close();
    http_response_code(400);
    exit('A valid draft ID is required.');
}

$user_id = (int) $_SESSION['user_id'];
$statement = $con->prepare('SELECT draft_id, title, image, description, category FROM draft_posts WHERE draft_id = ? AND user_id = ? LIMIT 1');
$statement->bind_param('ii', $draft_id, $user_id);
$statement->execute();
$draft = $statement->get_result()->fetch_assoc();
$statement->close();

if ($draft === null) {
    $con->close();
    http_response_code(404);
    exit('Draft not found or access denied.');
}

$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
$categories = ['education'=>'Education','technology'=>'Technology','travel'=>'Travel','food'=>'Food','fashion'=>'Fashion','sport'=>'Sport','other'=>'Others'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="theme-color" content="#0f172a">
<meta name="description" content="Continue editing your Weblogr draft."><title>Edit Draft | Weblogr</title>
<link rel="icon" type="image/svg+xml" href="../assets/weblogr-mark.svg">
<link rel="stylesheet" href="style.css"><link rel="stylesheet" href="../assets/responsive.css"><link rel="stylesheet" href="../assets/weblogr-product.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
.draft-editor{width:min(1050px,calc(100% - 48px));margin:0 auto;padding:38px 0 80px}.draft-head{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:22px}.draft-eyebrow{margin:0 0 5px;color:#2563eb;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.draft-head h1{margin:0;color:#101828;font-size:32px;line-height:1.1;letter-spacing:-.045em}.draft-head p:last-child{margin:8px 0 0;color:#667085;font-size:13px}.draft-actions{display:flex;gap:9px}.draft-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:0 15px;border-radius:10px;border:1px solid #d0d5dd;background:#fff;color:#182230;font-size:12px;font-weight:750;text-decoration:none;cursor:pointer}.draft-btn.primary{border-color:#2563eb;background:#2563eb;color:#fff}.draft-btn.primary:hover{background:#1d4ed8}.draft-layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:18px}.draft-card,.draft-side{background:#fff;border:1px solid #e4e7ec;border-radius:18px;box-shadow:0 10px 35px rgba(16,24,40,.07)}.draft-card{padding:30px}.draft-field{margin-bottom:22px}.draft-field label.field-label{display:block;margin-bottom:8px;color:#344054;font-size:11px;font-weight:800}.draft-title,.draft-body,.draft-select{width:100%;border:1px solid #d0d5dd;border-radius:11px;background:#fff;color:#101828;outline:0;transition:.18s}.draft-title{height:58px;padding:0 16px;font-size:22px;font-weight:650;letter-spacing:-.025em}.draft-body{min-height:430px;padding:16px;resize:vertical;font-size:15px;line-height:1.8}.draft-select{height:46px;padding:0 12px;font-size:13px}.draft-title:focus,.draft-body:focus,.draft-select:focus{border-color:#7ca4ff;box-shadow:0 0 0 4px rgba(37,99,235,.09)}.draft-cover{position:relative;display:grid;place-items:center;min-height:190px;border:1.5px dashed #b8c4d4;border-radius:14px;background:#f8fafc;overflow:hidden}.draft-cover img{display:block;width:100%;height:190px;object-fit:cover}.draft-cover-empty{padding:25px;text-align:center;color:#667085}.draft-cover-empty i{display:block;margin-bottom:9px;color:#2563eb;font-size:25px}.draft-cover-empty strong{display:block;color:#182230;font-size:13px}.draft-cover-empty span{display:block;margin-top:4px;font-size:11px}.draft-upload{display:block;margin-top:10px;padding:11px 13px;border:1px solid #e4e7ec;border-radius:10px;background:#fff;color:#344054;font-size:11px;font-weight:700;cursor:pointer}.draft-upload input{display:block;margin-top:7px;width:100%;font-size:11px}.draft-side{height:max-content;padding:20px}.draft-side h2{margin:0 0 14px;color:#101828;font-size:14px}.draft-status{display:flex;align-items:center;gap:9px;padding:12px;margin-bottom:18px;border-radius:11px;background:#f8fafc;color:#475467;font-size:11px}.draft-status i{color:#2563eb}.draft-tip{display:flex;gap:10px;padding:12px 0;border-top:1px solid #eef0f3}.draft-tip i{width:28px;height:28px;display:grid;place-items:center;flex:none;border-radius:8px;background:#eef4ff;color:#2563eb;font-size:11px}.draft-tip strong{display:block;color:#344054;font-size:11px}.draft-tip span{display:block;margin-top:2px;color:#98a2b3;font-size:10px;line-height:1.5}.draft-footer{display:flex;justify-content:space-between;gap:15px;margin-top:15px;color:#98a2b3;font-size:10px}.draft-error{display:none;margin-bottom:18px;padding:11px 13px;border:1px solid #fecdca;border-radius:10px;background:#fff5f4;color:#b42318;font-size:11px}.draft-error.show{display:block}@media(max-width:900px){.draft-layout{grid-template-columns:1fr}.draft-side{order:-1}}@media(max-width:600px){.draft-editor{width:calc(100% - 28px);padding-top:72px}.draft-head{align-items:flex-start;flex-direction:column}.draft-head h1{font-size:27px}.draft-actions{width:100%}.draft-btn{flex:1}.draft-card{padding:20px}.draft-title{font-size:19px}.draft-body{min-height:330px}}
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="content">
<section class="draft-editor">
<div class="draft-head"><div><p class="draft-eyebrow">Publishing studio</p><h1>Continue your story</h1><p>Your draft is saved. Refine it, then publish when you're ready.</p></div><div class="draft-actions"><a class="draft-btn" href="draft_posts.php"><i class="fa-solid fa-arrow-left"></i> Back to drafts</a><button class="draft-btn primary" type="submit" form="draft-form" name="publish" value="1" id="publish-btn"><i class="fa-solid fa-paper-plane"></i> Publish story</button></div></div>
<form id="draft-form" action="save_post.php" method="POST" enctype="multipart/form-data" novalidate>
<input type="hidden" name="draft_id" value="<?php echo (int)$draft['draft_id']; ?>"><input type="hidden" name="from_draft" value="1"><input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
<div class="draft-layout"><div class="draft-card"><div id="draft-error" class="draft-error" role="alert"></div>
<div class="draft-field"><label class="field-label" for="blog-title">Title</label><input class="draft-title" id="blog-title" name="title" type="text" maxlength="255" required value="<?php echo htmlspecialchars((string)$draft['title'],ENT_QUOTES,'UTF-8'); ?>" placeholder="Give your story a clear title" autocomplete="off"></div>
<div class="draft-field"><label class="field-label">Cover image</label><div class="draft-cover"><?php if(!empty($draft['image'])):?><img src="../images/<?php echo rawurlencode((string)$draft['image']); ?>" alt="Current draft cover"><?php else:?><div class="draft-cover-empty"><i class="fa-regular fa-image"></i><strong>No cover image yet</strong><span>Add a cover to make your story stand out.</span></div><?php endif;?></div><label class="draft-upload">Replace cover image<input type="file" name="uploadimage" accept="image/jpeg,image/png,image/gif,image/webp"></label></div>
<div class="draft-field"><label class="field-label" for="blog-para">Your story</label><textarea class="draft-body" id="blog-para" name="description" maxlength="10000" required placeholder="Continue writing..."><?php echo htmlspecialchars((string)$draft['description'],ENT_QUOTES,'UTF-8'); ?></textarea><div class="draft-footer"><span>Keep paragraphs short and easy to read.</span><span id="char-count">0 / 10,000</span></div></div>
</div><aside class="draft-side"><h2>Story settings</h2><div class="draft-status"><i class="fa-regular fa-floppy-disk"></i><span>Currently saved as a draft</span></div><div class="draft-field"><label class="field-label" for="category">Category</label><select class="draft-select" name="category" id="category" required><option value="">Choose a category</option><?php foreach($categories as $value=>$label):?><option value="<?php echo $value;?>" <?php echo $draft['category']===$value?'selected':'';?>><?php echo $label;?></option><?php endforeach;?></select></div><button class="draft-btn" type="submit" name="save_draft" value="1" style="width:100%;margin-bottom:12px"><i class="fa-regular fa-floppy-disk"></i> Save draft</button><div class="draft-tip"><i class="fa-solid fa-lightbulb"></i><div><strong>Strengthen the opening</strong><span>Give readers a reason to continue after the first paragraph.</span></div></div><div class="draft-tip"><i class="fa-solid fa-layer-group"></i><div><strong>Improve structure</strong><span>Use short paragraphs and clear sections for readability.</span></div></div><div class="draft-tip"><i class="fa-solid fa-check"></i><div><strong>Final check</strong><span>Review the title, cover and category before publishing.</span></div></div></aside></div>
</form></section></main>
<script>document.addEventListener('DOMContentLoaded',()=>{const form=document.getElementById('draft-form'),body=document.getElementById('blog-para'),title=document.getElementById('blog-title'),category=document.getElementById('category'),count=document.getElementById('char-count'),error=document.getElementById('draft-error'),publish=document.getElementById('publish-btn');const update=()=>count.textContent=`${body.value.length.toLocaleString()} / 10,000`;body.addEventListener('input',update);update();form.addEventListener('submit',e=>{error.classList.remove('show');if(!title.value.trim()||!body.value.trim()||!category.value){e.preventDefault();error.textContent='Please complete the title, story and category before saving or publishing.';error.classList.add('show');return;}if(e.submitter===publish){publish.disabled=true;publish.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Publishing...';}});});</script>
</body></html>
<?php $con->close(); ?>
