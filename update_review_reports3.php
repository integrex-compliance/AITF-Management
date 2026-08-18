<?php
$file = 'C:/xampp/htdocs/public_html/customerAccount.php';
$content = file_get_contents($file);

$pattern = '/(<div class="[^"]*?)"(?:\s*<\?php[^>]*\?>)*(\s*>)\s*(<span[^>]*>\s*(?:Review Report - CDC DELIBERATION AND DECISION|Certificate Print|CONTROL FORMAT -1[^<]*|CONTROL FORMAT -2[^<]*)\s*<\/span>)/is';

$replacement = '$1<?php echo isset($isAuditor) && $isAuditor ? " auditor-disabled" : ""; ?>" <?php echo isset($isAuditor) && $isAuditor ? \'title="Not Allowed"\' : ""; ?>$2 $3';

$newContent = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $newContent);
echo "Replaced " . preg_match_all($pattern, $content) . " occurrences.\n";
?>
