<?php
$file = 'C:/xampp/htdocs/public_html/customerAccount.php';
$content = file_get_contents($file);

$search = '" <?php echo isset($isAuditor) && $isAuditor ? " auditor-disabled" : ""; ?> <?php echo isset($isAuditor) && $isAuditor ? "title=\"Not Allowed\"" : ""; ?>>
';
// Wait, the string in the file has whitespace maybe?
// I will use regex on the exact thing I just wrote.

$pattern = '/(<div class="[^"]*?)"\s*<\?php echo isset\(\$isAuditor\) && \$isAuditor \? " auditor-disabled" : ""; \?>\s*<\?php echo isset\(\$isAuditor\) && \$isAuditor \? "title=\\\\"Not Allowed\\\\"" : ""; \?>/is';
$replacement = '$1<?php echo isset($isAuditor) && $isAuditor ? " auditor-disabled" : ""; ?>" <?php echo isset($isAuditor) && $isAuditor ? \'title="Not Allowed"\' : ""; ?>';

$newContent = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $newContent);
echo "Replaced " . preg_match_all($pattern, $content) . " occurrences.\n";
?>
