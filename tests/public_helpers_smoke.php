<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/public_helpers.php';

$cases=['Hello, Weblogr!' => 'hello-weblogr','  PHP & MySQL: A Guide  ' => 'php-mysql-a-guide','---'=>'story'];
foreach($cases as $input=>$expected){$actual=slugify($input);if($actual!==$expected){fwrite(STDERR,"slugify failed: {$input} => {$actual}\n");exit(1);}}
if(excerpt('One two three four',8)!=='One two…') { fwrite(STDERR,"excerpt failed\n"); exit(1); }
foreach([article_url(42,'Hello World'),author_url(7,'Alice Smith')] as $url){if(strpos($url,'/read/')===false&&strpos($url,'/author/')===false){fwrite(STDERR,"URL helper failed\n");exit(1);}}
echo "Public helper smoke tests passed.\n";
