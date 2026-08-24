<?php
$content = file_get_contents('invoicePage.php');

// 1. Remove my previous injection completely
$pattern = '/<\?php\s*\$session_role = trim\(\$_SESSION\[\'role\'\] \?\? \'\'\);\s*\$c_double.*?return true;\s*\}/is';
$content = preg_replace($pattern, 'return true;', $content);

// Ensure there is no duplicated "return true;" inside the if block
$content = str_replace(
    'if (fromDate == "" && toDate == "") {
                    return true;
                    return true;
                }',
    'if (fromDate == "" && toDate == "") {
                    return true;
                }',
    $content
);
$content = str_replace(
    "if (fromDate == \"\" && toDate == \"\") {\r\n                    return true;\r\n                    return true;\r\n                }",
    "if (fromDate == \"\" && toDate == \"\") {\r\n                    return true;\r\n                }",
    $content
);

// 2. Insert the NEW logic at the very top of the ext.search.push function!
$target = 'var tableId = settings.nTable.id;';

$replacement = <<<EOT
var tableId = settings.nTable.id;

                // --- START RBAC FILTER ---
                try {
                    <?php
                    \$session_role = trim(\$_SESSION['role'] ?? '');
                    \$c_double = in_array(\$session_role, ['Office Staff-A', 'Office Staff-B', 'Marketing Head']);
                    \$c_single = in_array(\$session_role, ['Marketing Executive']);
                    if (\$c_double || trim(\$_SESSION['username'] ?? '') === 'marketingh') {
                        echo "var roleInvoiceAccess = 'C_DOUBLE_TICK';\\n";
                    } elseif (\$c_single) {
                        echo "var roleInvoiceAccess = 'C_SINGLE_TICK';\\n";
                    } else {
                        echo "var roleInvoiceAccess = 'FULL';\\n";
                    }
                    ?>
                    var searchVal = $('#' + tableId + '_filter input').val();
                    if (!searchVal || searchVal.trim() === '') {
                        if (roleInvoiceAccess === 'C_SINGLE_TICK') {
                            return false;
                        }
                        if (roleInvoiceAccess === 'C_DOUBLE_TICK') {
                            var rowDate = data[4] || "";
                            var dateSplit = rowDate.split("~");
                            var dateStr = dateSplit.length > 1 ? dateSplit[1] : dateSplit[0];
                            dateStr = dateStr.trim();
                            var invDate = null;
                            if (dateStr) {
                                var parts = dateStr.split('.');
                                if (parts.length === 3) {
                                    invDate = new Date(parts[2], parts[1] - 1, parts[0]);
                                }
                            }
                            var clientName = data[1] || "";
                            var isInactive = clientName.indexOf('(Expire)') !== -1 || 
                                             clientName.indexOf('(Withdrawn)') !== -1 || 
                                             clientName.indexOf('(Suspended)') !== -1 ||
                                             clientName.indexOf('(Cancel)') !== -1;
                            if (isInactive) return false;
                            
                            var rowNode = settings.aoData[dataIndex].nTr;
                            if (rowNode) {
                                var clientStatus = $(rowNode).attr('data-client-status');
                                if (clientStatus && clientStatus !== 'Active') return false;
                            }
                            
                            if (invDate) {
                                var now = new Date();
                                var firstDayOfPrev = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                                if (invDate < firstDayOfPrev) return false;
                            } else {
                                return false; 
                            }
                        }
                    }
                } catch(e) {
                    console.error("RBAC filter error:", e);
                    return false;
                }
                // --- END RBAC FILTER ---
EOT;

$content = str_replace($target, $replacement, $content);
file_put_contents('invoicePage.php', $content);
echo "Injected robust block at the top";
?>
