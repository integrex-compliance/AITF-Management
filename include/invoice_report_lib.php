<?php
/**
 * include/invoice_report_lib.php
 *
 * Shared building blocks for the Invoice Report (used by the PDF download,
 * the Excel download, and the "email me the report" endpoint).
 *
 *   - fetchInvoiceReportData()      : runs the same query/filters for every output
 *   - SimpleXlsx                    : minimal native .xlsx writer (ZipArchive + XML)
 *   - buildInvoiceReportXlsxBytes() : returns a finished .xlsx as a raw byte string
 *
 * The PDF builder lives in include/invoice_report_pdf.php (kept separate so the
 * Excel path does not have to load TCPDF).
 */

if (!function_exists('invoiceReportBytes')) {
    function invoiceReportBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . $units[$i];
    }
}

if (!function_exists('invoiceReportLog')) {
    function invoiceReportLog($stage, array $context = []) {
        $base = [
            'stage' => $stage,
            'elapsed' => isset($GLOBALS['invoiceReportStartTime']) ? round(microtime(true) - $GLOBALS['invoiceReportStartTime'], 3) : null,
            'memory' => invoiceReportBytes(memory_get_usage(true)),
            'peak' => invoiceReportBytes(memory_get_peak_usage(true)),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];
        error_log('INVOICE_REPORT ' . json_encode(array_merge($base, $context)));
    }
}

if (!function_exists('invoiceReportRegisterShutdownLogger')) {
    function invoiceReportRegisterShutdownLogger($format) {
        register_shutdown_function(function () use ($format) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                invoiceReportLog($format . '.fatal', [
                    'error_type' => $error['type'],
                    'message' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                ]);
            }
        });
    }
}

/* ----------------- shared amount / number helpers ----------------- */
if (!function_exists('cleanAmount')) {
    function cleanAmount($value) {
        if ($value === null || $value === '') return 0.0;
        $clean = str_replace(['Rs.', '₹', ','], '', $value);
        $clean = preg_replace('/[^\d.]/', '', $clean);
        return (float)$clean;
    }
}

if (!function_exists('taxAmount')) {
    function taxAmount($val) {
        if ($val === null || $val === '' || strtoupper($val) === 'NA') return null;
        return cleanAmount($val);
    }
}

if (!function_exists('formatIndian')) {
    function formatIndian($num) {
        $num = round($num, 2);
        $parts = explode('.', $num);
        $whole = $parts[0];
        $decimal = isset($parts[1]) ? str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : '00';

        $lastThree = substr($whole, -3);
        $restUnits = substr($whole, 0, -3);
        if ($restUnits != '' && $restUnits != '-') {
            $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
            $whole = $restUnits . "," . $lastThree;
        }
        return $whole . "." . $decimal;
    }
}

if (!function_exists('convertToWord')) {
    function convertToWord($number) {
        $no = (int) floor($number);
        $point = (int) round(($number - $no) * 100);
        if ($point === 100) { $no = $no + 1; $point = 0; }
        $digits_1 = strlen((string) $no);
        $i = 0;
        $str = array();
        $words = array('0' => '', '1' => 'One', '2' => 'Two',
            '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
            '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
            '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
            '13' => 'Thirteen', '14' => 'Fourteen',
            '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
            '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
            '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
            '60' => 'Sixty', '70' => 'Seventy',
            '80' => 'Eighty', '90' => 'Ninety');
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        $n = $no;
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($n % $divider);
            $n = floor($n / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] .
                        " " . $digits[$counter] . $plural . " " . $hundred :
                        $words[floor($number / 10) * 10]
                        . " " . $words[$number % 10] . " "
                        . $digits[$counter] . $plural . " " . $hundred;
            } else {
                $str[] = null;
            }
        }
        $str = array_reverse($str);
        $result = trim(implode('', $str));
        if ($result === '') { $result = 'Zero'; }
        $pointsWords = '';
        if ($point) {
            $pointsWords = ($point < 21) ? $words[$point] : ($words[floor($point / 10) * 10] . " " . $words[$point % 10]);
            $pointsWords = trim($pointsWords);
        }
        return $result . " Rupees" . ($pointsWords !== '' ? (" and " . $pointsWords . " Paise") : "") . " Only";
    }
}

/**
 * Run the report query (identical logic for PDF / Excel / email).
 *
 * @return array ['invoices' => [...], 'svcMap' => [invoice_id => [descs]]]
 */
function fetchInvoiceReportData($db, $invoiceType, $fromDate, $toDate, $clientName) {
    if ($invoiceType === 'pending') {
        $sql = "SELECT id.invoice_id, id.customer_id, id.profarma_prefix, id.invoice_no, id.final_prefix, id.final_invoice_no,
                       id.invoice_date, id.final_amount, id.igst, id.cgst, id.sgst, id.tax_type, id.invoice_type, id.invoice_status, ci.customer_name, ci.gstin_no
                FROM invoice_details id
                LEFT JOIN customer_info ci ON id.customer_id = ci.id
                WHERE id.is_deleted = 'No' AND (id.invoice_status <> 'Rcv' OR id.invoice_status IS NULL) AND CAST(id.final_amount AS DECIMAL(12,2)) > 0";
        $params = [];
    } else {
        $sql = "SELECT id.invoice_id, id.customer_id, id.profarma_prefix, id.invoice_no, id.final_prefix, id.final_invoice_no,
                       id.invoice_date, id.final_amount, id.igst, id.cgst, id.sgst, id.tax_type, id.invoice_type, id.invoice_status, ci.customer_name, ci.gstin_no
                FROM invoice_details id
                LEFT JOIN customer_info ci ON id.customer_id = ci.id
                WHERE id.invoice_type = :invoiceType AND id.is_deleted = 'No'";
        $params = [':invoiceType' => $invoiceType];
    }

    $invRole = trim($_SESSION['role'] ?? '');
    $isMarketingHead = in_array($invRole, array('Marketing Head', 'Marketing Executive'), true)
        || strtolower(str_replace(' ', '', $invRole)) === 'marketinghead'
        || (isset($_SESSION['username']) && strtolower(trim($_SESSION['username'])) === 'marketingh');
    $isOfficeStaff = in_array($invRole, array('Office Staff', 'Office Staff-A', 'Office Staff-B'), true)
        || in_array(strtolower(str_replace(array(' ', '-'), '', $invRole)), array('officestaff', 'officestaffa', 'officestaffb'), true);
    $isRestrictedRole = $isMarketingHead || $isOfficeStaff;

    if ($fromDate != '') {
        $sql .= " AND id.invoice_date >= :fromDate";
        $params[':fromDate'] = date("Y-m-d", strtotime(str_replace('.', '-', $fromDate)));
    }
    if ($toDate != '') {
        $sql .= " AND id.invoice_date <= :toDate";
        $params[':toDate'] = date("Y-m-d", strtotime(str_replace('.', '-', $toDate)));
    }
    if ($clientName != '') {
        $params[':clientName'] = '%' . $clientName . '%';
        if ($isRestrictedRole && $fromDate == '' && $toDate == '') {
            $params[':exactClient'] = $clientName;
            $sql .= " AND (ci.customer_name = :exactClient OR id.invoice_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')) AND ci.customer_name LIKE :clientName";
        } else {
            $sql .= " AND ci.customer_name LIKE :clientName";
        }
    } else {
        if ($isRestrictedRole && $fromDate == '' && $toDate == '') {
            $sql .= " AND id.invoice_date >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')";
        }
    }

    $sql .= " ORDER BY id.invoice_date DESC, id.invoice_id DESC";

    $statement = $db->prepare($sql);
    foreach ($params as $key => &$val) {
        $statement->bindParam($key, $val);
    }
    unset($val);
    $statement->execute();
    $invoices = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Batch load services
    $invoiceIds = array_column($invoices, 'invoice_id');
    $svcMap = [];
    if (!empty($invoiceIds)) {
        $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));
        $svcSql = "SELECT invoice_ids, service_desc FROM invoice_services WHERE invoice_ids IN ($placeholders)";
        $stmtSvc = $db->prepare($svcSql);
        $stmtSvc->execute($invoiceIds);
        foreach ($stmtSvc->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $svcMap[$row['invoice_ids']][] = $row['service_desc'];
        }
    }

    return ['invoices' => $invoices, 'svcMap' => $svcMap];
}

/* ============================================================
 *  Minimal native XLSX writer (ZipArchive + inline-string XML)
 * ============================================================ */
if (!class_exists('SimpleXlsx')) {
class SimpleXlsx {
    private $rows = [];          // each row: array of ['v'=>value, 's'=>styleIdx, 't'=>'n'|'s']
    private $merges = [];        // e.g. "A1:K1"
    private $colWidths = [];     // colIndex(1-based) => width
    private $rowHeights = [];    // rowIndex(1-based) => height
    private $freezeYSplit = 0;   // number of top rows to freeze
    private $freezeCell = '';    // top-left cell of the scrolling area, e.g. "A6"
    private $autoFilterRef = '';
    private $pageOrientation = '';
    private $fitToWidth = 1;
    private $fitToHeight = 0;

    public function setColWidth($col, $w) { $this->colWidths[$col] = $w; }
    public function setRowHeight($row, $h) { $this->rowHeights[$row] = $h; }
    public function merge($ref) { $this->merges[] = $ref; }
    public function addRow(array $cells) { $this->rows[] = $cells; }
    public function setAutoFilter($ref) { $this->autoFilterRef = trim((string)$ref); }
    public function setPageLayout($orientation = 'landscape', $fitToWidth = 1, $fitToHeight = 0) {
        $this->pageOrientation = strtolower((string)$orientation) === 'portrait' ? 'portrait' : 'landscape';
        $this->fitToWidth = max(0, (int)$fitToWidth);
        $this->fitToHeight = max(0, (int)$fitToHeight);
    }

    /** Freeze every row above $rowNumber so it stays visible while scrolling. */
    public function freezePanesAbove($rowNumber) {
        $this->freezeYSplit = $rowNumber - 1;
        $this->freezeCell = 'A' . $rowNumber;
    }

    public static function col($index) { // 1-based -> A, B, ... AA
        $s = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $s = chr(65 + $mod) . $s;
            $index = (int)(($index - $mod) / 26);
        }
        return $s;
    }

    private static function esc($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function sheetXml() {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        if ($this->pageOrientation !== '') {
            $xml .= '<sheetPr><pageSetUpPr fitToPage="1" autoPageBreaks="0"/></sheetPr>';
        }

        // Freeze panes (must appear before <cols>/<sheetData> per the schema).
        if ($this->freezeYSplit > 0) {
            $xml .= '<sheetViews><sheetView workbookViewId="0">'
                  . '<pane ySplit="' . $this->freezeYSplit . '" topLeftCell="' . $this->freezeCell . '" activePane="bottomLeft" state="frozen"/>'
                  . '<selection pane="bottomLeft" activeCell="' . $this->freezeCell . '" sqref="' . $this->freezeCell . '"/>'
                  . '</sheetView></sheetViews>';
        }

        if (!empty($this->colWidths)) {
            $xml .= '<cols>';
            foreach ($this->colWidths as $c => $w) {
                $xml .= '<col min="' . $c . '" max="' . $c . '" width="' . $w . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        $r = 0;
        foreach ($this->rows as $cells) {
            $r++;
            $htAttr = isset($this->rowHeights[$r]) ? ' ht="' . $this->rowHeights[$r] . '" customHeight="1"' : '';
            $xml .= '<row r="' . $r . '"' . $htAttr . '>';
            $c = 0;
            foreach ($cells as $cell) {
                $c++;
                if ($cell === null) { continue; }
                $ref = self::col($c) . $r;
                $style = isset($cell['s']) ? ' s="' . $cell['s'] . '"' : '';
                $type = $cell['t'] ?? 's';
                $val = $cell['v'];
                if ($type === 'n') {
                    $num = is_numeric($val) ? $val : 0;
                    $xml .= '<c r="' . $ref . '"' . $style . '><v>' . $num . '</v></c>';
                } else {
                    if ($val === '' || $val === null) {
                        $xml .= '<c r="' . $ref . '"' . $style . '/>';
                    } else {
                        $xml .= '<c r="' . $ref . '"' . $style . ' t="inlineStr"><is><t xml:space="preserve">'
                              . self::esc($val) . '</t></is></c>';
                    }
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';

        if ($this->autoFilterRef !== '') {
            $xml .= '<autoFilter ref="' . self::esc($this->autoFilterRef) . '"/>';
        }

        if (!empty($this->merges)) {
            $xml .= '<mergeCells count="' . count($this->merges) . '">';
            foreach ($this->merges as $m) {
                $xml .= '<mergeCell ref="' . $m . '"/>';
            }
            $xml .= '</mergeCells>';
        }

        if ($this->pageOrientation !== '') {
            $xml .= '<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>';
            $xml .= '<pageSetup orientation="' . $this->pageOrientation
                  . '" fitToWidth="' . $this->fitToWidth
                  . '" fitToHeight="' . $this->fitToHeight
                  . '" paperSize="9"/>';
        }

        $xml .= '</worksheet>';
        return $xml;
    }

    private function stylesXml() {
        // numFmt 164 = #,##0.00
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00"/></numFmts>'
        . '<fonts count="12">'
          . '<font><sz val="11"/><name val="Calibri"/></font>'                                   // 0 default
          . '<font><b/><sz val="11"/><name val="Calibri"/></font>'                               // 1 bold
          . '<font><b/><sz val="18"/><color rgb="FF163C6E"/><name val="Calibri"/></font>'        // 2 title (company) - AITF dark blue
          . '<font><b/><sz val="13"/><color rgb="FF163C6E"/><name val="Calibri"/></font>'        // 3 subtitle (report type + filters) - AITF dark blue
          . '<font><sz val="10"/><color rgb="FF808080"/><name val="Calibri"/></font>'            // 4 filter (subtle grey, legacy)
          . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'        // 5 bold white
          . '<font><b/><sz val="11"/><color rgb="FF163C6E"/><name val="Calibri"/></font>'        // 6 bold AITF dark blue (summary text)
          . '<font><i/><sz val="11"/><name val="Calibri"/></font>'                               // 7 italic
          . '<font><b/><sz val="11"/><color rgb="FFCF1F1E"/><name val="Calibri"/></font>'        // 8 bold AITF red
          . '<font><b/><sz val="12"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'        // 9 bold white 12
          . '<font><b/><sz val="11"/><color rgb="FF006100"/><name val="Calibri"/></font>'        // 10 bold dark green (PAID text)
          . '<font><b/><sz val="11"/><color rgb="FFCF1F1E"/><name val="Calibri"/></font>'        // 11 bold AITF red (UNPAID text)
        . '</fonts>'
        . '<fills count="7">'
          . '<fill><patternFill patternType="none"/></fill>'                                      // 0
          . '<fill><patternFill patternType="gray125"/></fill>'                                   // 1 (reserved)
          . '<fill><patternFill patternType="solid"><fgColor rgb="FF163C6E"/></patternFill></fill>' // 2 AITF dark blue (header + section titles)
          . '<fill><patternFill patternType="solid"><fgColor rgb="FFEAF3FF"/></patternFill></fill>' // 3 light blue
          . '<fill><patternFill patternType="solid"><fgColor rgb="FFDBEAFE"/></patternFill></fill>' // 4 paid fill (blue, summary)
          . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFC7CE"/></patternFill></fill>' // 5 red fill (unpaid rows)
          . '<fill><patternFill patternType="solid"><fgColor rgb="FFC6EFCE"/></patternFill></fill>' // 6 green fill (paid rows)
        . '</fills>'
        . '<borders count="3">'
          . '<border><left/><right/><top/><bottom/><diagonal/></border>'                          // 0 none
          . '<border><left style="thin"><color rgb="FFB0C4DE"/></left><right style="thin"><color rgb="FFB0C4DE"/></right>'
            . '<top style="thin"><color rgb="FFB0C4DE"/></top><bottom style="thin"><color rgb="FFB0C4DE"/></bottom><diagonal/></border>' // 1 thin
          . '<border><left/><right/><top/><bottom style="medium"><color rgb="FFCF1F1E"/></bottom><diagonal/></border>' // 2 AITF red bottom accent
        . '</borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="39">'
          // 0 default
          . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
          // 1 title (center)
          . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 2 subtitle
          . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center"/></xf>'
          // 3 filter
          . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center"/></xf>'
          // 4 table header
          . '<xf numFmtId="0" fontId="5" fillId="2" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
          // 5 data text left
          . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
          // 6 data bold text left
          . '<xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
          // 7 data center
          . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 8 amount number bold right
          . '<xf numFmtId="164" fontId="1" fillId="0" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 9 tax number right
          . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 10 paid status (blue bold center)
          . '<xf numFmtId="0" fontId="6" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 11 unpaid status (red bold center)
          . '<xf numFmtId="0" fontId="8" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 12 summary label (bold, light-blue fill)
          . '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
          // 13 summary value (blue bold number, light-blue fill)
          . '<xf numFmtId="164" fontId="6" fillId="3" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 14 grand total label (white bold on blue)
          . '<xf numFmtId="0" fontId="5" fillId="2" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
          // 15 grand total value (white bold number on blue)
          . '<xf numFmtId="164" fontId="5" fillId="2" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 16 section title (white bold 12 on blue, center)
          . '<xf numFmtId="0" fontId="9" fillId="2" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 17 amount in words (italic wrap)
          . '<xf numFmtId="0" fontId="7" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
          // 18 summary plain label (bold, no fill)
          . '<xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
          // 19 summary plain value (bold number, no fill)
          . '<xf numFmtId="164" fontId="1" fillId="0" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 20 count value plain center (bold)
          . '<xf numFmtId="0" fontId="1" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 21 data center on paid fill
          . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 22 sub header label center bold blue on light-blue
          . '<xf numFmtId="0" fontId="6" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 23 tax total value bold right on light-blue
          . '<xf numFmtId="164" fontId="1" fillId="3" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 24 PAID status cell (summary) — blue bold text on light-blue fill, center
          . '<xf numFmtId="0" fontId="6" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 25 UNPAID status cell (summary) — red bold text on light-red fill, center
          . '<xf numFmtId="0" fontId="8" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'

          /* ----- PAID data-row variants (light-green fill 6) ----- */
          // 26 center on green (Sr / Date / dash)
          . '<xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 27 bold left wrap on green (Client)
          . '<xf numFmtId="0" fontId="1" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
          // 28 left wrap on green (GSTIN / Invoice No / Particulars)
          . '<xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
          // 29 amount bold right number on green
          . '<xf numFmtId="164" fontId="1" fillId="6" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 30 tax right number on green
          . '<xf numFmtId="164" fontId="0" fillId="6" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 31 PAID status — dark-green bold center on green
          . '<xf numFmtId="0" fontId="10" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'

          /* ----- UNPAID data-row variants (light-red fill 5) ----- */
          // 32 center on red (Sr / Date / dash)
          . '<xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 33 bold left wrap on red (Client)
          . '<xf numFmtId="0" fontId="1" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
          // 34 left wrap on red (GSTIN / Invoice No / Particulars)
          . '<xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
          // 35 amount bold right number on red
          . '<xf numFmtId="164" fontId="1" fillId="5" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 36 tax right number on red
          . '<xf numFmtId="164" fontId="0" fillId="5" borderId="1" xfId="0" applyAlignment="1" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>'
          // 37 UNPAID status — AITF-red bold center on red
          . '<xf numFmtId="0" fontId="11" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
          // 38 AITF red bottom accent line (used on the spacer row above the header)
          . '<xf numFmtId="0" fontId="0" fillId="0" borderId="2" xfId="0"/>'
        . '</cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '</styleSheet>';
    }

    /** Build the .xlsx package and return it as a raw byte string. */
    public function toString($sheetName = 'Report') {
        $GLOBALS['invoiceExportStage'] = 'creating Excel ZIP package';
        invoiceReportLog('excel.zip.start', ['sheet_name' => $sheetName]);
        if (!class_exists('ZipArchive')) {
            invoiceReportLog('excel.ziparchive_missing', [
                'loaded_extensions' => implode(',', get_loaded_extensions()),
            ]);
            throw new RuntimeException('ZipArchive extension is not available.');
        }

        $tmpDir = sys_get_temp_dir();
        if (!is_dir($tmpDir) || !is_writable($tmpDir)) {
            invoiceReportLog('excel.temp_dir_not_writable', [
                'tmp_dir' => $tmpDir,
                'is_dir' => is_dir($tmpDir) ? 'yes' : 'no',
                'is_writable' => is_writable($tmpDir) ? 'yes' : 'no',
            ]);
            throw new RuntimeException('Temporary directory is not writable.');
        }

        $tmp = tempnam($tmpDir, 'xlsx');
        if ($tmp === false) {
            invoiceReportLog('excel.tempnam_failed', ['tmp_dir' => $tmpDir]);
            throw new RuntimeException('Could not create temporary XLSX file.');
        }

        $zip = new ZipArchive();
        $openResult = $zip->open($tmp, ZipArchive::OVERWRITE);
        if ($openResult !== true) {
            invoiceReportLog('excel.zip_open_failed', [
                'tmp_file' => $tmp,
                'zip_status' => $openResult,
            ]);
            @unlink($tmp);
            throw new RuntimeException('Could not open XLSX zip package.');
        }

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::esc($sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>');

        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');

        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml());
        if ($zip->close() !== true) {
            invoiceReportLog('excel.zip_close_failed', ['tmp_file' => $tmp]);
            @unlink($tmp);
            throw new RuntimeException('Could not close XLSX zip package.');
        }

        $data = file_get_contents($tmp);
        @unlink($tmp);
        if ($data === false) {
            invoiceReportLog('excel.read_tmp_failed', ['tmp_file' => $tmp]);
            throw new RuntimeException('Could not read generated XLSX file.');
        }
        invoiceReportLog('excel.zip.done', ['bytes' => strlen($data)]);
        return $data;
    }
}
}

/**
 * Build the invoice report as a finished .xlsx and return the raw bytes.
 *
 * @param array  $data        result of fetchInvoiceReportData()
 * @param string $invoiceType 'proforma' | 'final'
 * @param string $fromDate    filter (display only)
 * @param string $toDate      filter (display only)
 * @param string $clientName  filter (display only)
 */
function buildInvoiceReportXlsxBytes($data, $invoiceType, $fromDate, $toDate, $clientName) {
    $invoices = $data['invoices'];
    $svcMap   = $data['svcMap'];

    $GLOBALS['invoiceExportStage'] = 'assembling Excel worksheet rows';
    invoiceReportLog('excel.rows.start', ['invoice_count' => count($invoices)]);
    $xlsx = new SimpleXlsx();

    // Keep the title + column-header rows (1-4) visible while scrolling the data.
    $xlsx->freezePanesAbove(5);

    // Column widths (A..K)
    $xlsx->setColWidth(1, 6);    // Sr
    $xlsx->setColWidth(2, 30);   // Client Name
    $xlsx->setColWidth(3, 20);   // GSTIN
    $xlsx->setColWidth(4, 18);   // Invoice No
    $xlsx->setColWidth(5, 13);   // Date
    $xlsx->setColWidth(6, 38);   // Particulars
    $xlsx->setColWidth(7, 16);   // Amount
    $xlsx->setColWidth(8, 14);   // CGST
    $xlsx->setColWidth(9, 14);   // SGST
    $xlsx->setColWidth(10, 14);  // IGST
    $xlsx->setColWidth(11, 15);  // Payment Status

    $typeLabel = ($invoiceType == 'proforma') ? 'PROFORMA' : 'FINAL';

    // Build the filter text that rides along with the report title in parentheses.
    $filters = '';
    if (!empty($fromDate) || !empty($toDate)) {
        $filters = 'For Period: ' . ($fromDate !== '' ? $fromDate : 'Start') . ' to ' . ($toDate !== '' ? $toDate : 'End');
    }
    if (!empty($clientName)) {
        $filters .= ($filters !== '' ? ', ' : '') . 'Client: ' . strtoupper($clientName);
    }
    if ($filters === '') { $filters = 'All Records'; }

    // --- Title block (centered across the report width) ---
    // Row 1: company name only (largest, bold)
    $xlsx->addRow([['v' => 'AITF MANAGEMENT CERTIFICATION LIMITED', 's' => 1, 't' => 's']]);
    $xlsx->merge('A1:K1');
    $xlsx->setRowHeight(1, 28);

    // Row 2: report type + filters in parentheses, e.g. "FINAL TAX INVOICE REPORT (All Records)"
    $xlsx->addRow([['v' => $typeLabel . ' TAX INVOICE REPORT (' . $filters . ')', 's' => 2, 't' => 's']]);
    $xlsx->merge('A2:K2');
    $xlsx->setRowHeight(2, 20);

    // Row 3: thin spacer carrying a subtle AITF-red accent line (spans full width A3:K3)
    $accentRow = [];
    for ($ac = 0; $ac < 11; $ac++) {
        $accentRow[] = ['v' => '', 's' => 38, 't' => 's'];
    }
    $xlsx->addRow($accentRow);
    $xlsx->setRowHeight(3, 8);

    // --- Table header (row 4) ---
    $headers = ['Sr.', 'Client Name', 'GSTIN No', 'Invoice No.', 'Date', 'Particulars',
                'Amount (Rs.)', 'CGST @9%', 'SGST @9%', 'IGST @18%', 'Payment Status'];
    $headerRow = [];
    foreach ($headers as $h) {
        $headerRow[] = ['v' => $h, 's' => 4, 't' => 's'];
    }
    $xlsx->addRow($headerRow);
    $xlsx->setRowHeight(4, 26);

    // --- Data rows ---
    $totalSum = 0; $totalCgst = 0; $totalSgst = 0; $totalIgst = 0;
    $paidCount = 0; $unpaidCount = 0; $paidAmount = 0;
    $count = 1;

    if (empty($invoices)) {
        // "No records found" spans the whole table (header is row 4, so this is row 5).
        $xlsx->addRow([['v' => 'No records found', 's' => 7, 't' => 's']]);
        $xlsx->merge('A5:K5');
    } else {
        foreach ($invoices as $inv) {
            $invoiceNo = ($invoiceType == 'proforma')
                ? $inv['profarma_prefix'] . '-' . $inv['invoice_no']
                : $inv['final_prefix'] . '-' . $inv['final_invoice_no'];

            $particularsArray = isset($svcMap[$inv['invoice_id']]) ? $svcMap[$inv['invoice_id']] : [];
            $particulars = implode(', ', $particularsArray);

            $amount = cleanAmount($inv['final_amount']);
            $totalSum += $amount;

            $cgstVal = taxAmount($inv['cgst']);
            $sgstVal = taxAmount($inv['sgst']);
            $igstVal = taxAmount($inv['igst']);

            if ($cgstVal !== null) $totalCgst += $cgstVal;
            if ($sgstVal !== null) $totalSgst += $sgstVal;
            if ($igstVal !== null) $totalIgst += $igstVal;

            $isPaid = (isset($inv['invoice_status']) && $inv['invoice_status'] == 'Rcv');
            if ($isPaid) {
                $paidCount++;
                $paidAmount += $amount;
                // PAID -> whole row light-green (styles 26-31)
                $sCenter = 26;  // Sr / Date / dash
                $sClient = 27;  // Client (bold)
                $sText   = 28;  // GSTIN / Invoice No / Particulars
                $sAmount = 29;  // Amount (bold)
                $sTax    = 30;  // CGST / SGST / IGST
                $sStatus = 31;  // PAID (dark-green bold)
                $statusText = 'PAID';
            } else {
                $unpaidCount++;
                // UNPAID -> whole row light-red (styles 32-37)
                $sCenter = 32;  // Sr / Date / dash
                $sClient = 33;  // Client (bold)
                $sText   = 34;  // GSTIN / Invoice No / Particulars
                $sAmount = 35;  // Amount (bold)
                $sTax    = 36;  // CGST / SGST / IGST
                $sStatus = 37;  // UNPAID (dark-red bold)
                $statusText = 'UNPAID';
            }

            $row = [];
            $row[] = ['v' => $count++, 's' => $sCenter, 't' => 'n'];
            $row[] = ['v' => $inv['customer_name'], 's' => $sClient, 't' => 's'];
            $row[] = ['v' => $inv['gstin_no'], 's' => $sText, 't' => 's'];
            $row[] = ['v' => $invoiceNo, 's' => $sText, 't' => 's'];
            $row[] = ['v' => simpleDateConversion($inv['invoice_date']), 's' => $sCenter, 't' => 's'];
            $row[] = ['v' => $particulars, 's' => $sText, 't' => 's'];
            $row[] = ['v' => round($amount, 2), 's' => $sAmount, 't' => 'n'];
            $row[] = ($cgstVal !== null) ? ['v' => round($cgstVal, 2), 's' => $sTax, 't' => 'n'] : ['v' => '-', 's' => $sCenter, 't' => 's'];
            $row[] = ($sgstVal !== null) ? ['v' => round($sgstVal, 2), 's' => $sTax, 't' => 'n'] : ['v' => '-', 's' => $sCenter, 't' => 's'];
            $row[] = ($igstVal !== null) ? ['v' => round($igstVal, 2), 's' => $sTax, 't' => 'n'] : ['v' => '-', 's' => $sCenter, 't' => 's'];
            $row[] = ['v' => $statusText, 's' => $sStatus, 't' => 's'];
            $xlsx->addRow($row);
        }
    }

    // Spacer row
    $xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]);

    /* --- REPORT SUMMARY --- */
    $invoiceCount = count($invoices);
    $totalTaxSum  = $totalCgst + $totalSgst + $totalIgst;
    $grandTotal   = $totalSum + $totalTaxSum;
    $amountInWords = convertToWord($grandTotal);

    // Rows so far: company(1) report+filters(2) spacer(3) header(4) + data rows + spacer
    $dataRowCount = empty($invoices) ? 1 : count($invoices);
    $curRow = 4 + $dataRowCount + 1; // last spacer row index

    $summaryTitle = strtoupper($invoiceType) . ' INVOICE REPORT SUMMARY';

    // Summary title row
    $curRow++;
    $xlsx->addRow([['v' => $summaryTitle, 's' => 16, 't' => 's']]);
    $xlsx->merge('A' . $curRow . ':D' . $curRow);

    $summaryLines = [
        ['TOTAL NUMBER OF INVOICES', $invoiceCount, 'int'],
        ['TOTAL INVOICE AMOUNT (TAXABLE)', round($totalSum, 2), 'money_hi'],
        ['TOTAL CGST @9%', round($totalCgst, 2), 'money'],
        ['TOTAL SGST @9%', round($totalSgst, 2), 'money'],
        ['TOTAL IGST @18%', round($totalIgst, 2), 'money'],
        ['TOTAL TAX AMOUNT', round($totalTaxSum, 2), 'money_plain'],
    ];

    foreach ($summaryLines as $line) {
        $curRow++;
        list($label, $value, $kind) = $line;
        if ($kind === 'int') {
            $lblStyle = 18; $valCell = ['v' => $value, 's' => 20, 't' => 'n'];
        } elseif ($kind === 'money_hi') {
            $lblStyle = 18; $valCell = ['v' => $value, 's' => 19, 't' => 'n'];
        } elseif ($kind === 'money_plain') {
            $lblStyle = 18; $valCell = ['v' => $value, 's' => 19, 't' => 'n'];
        } else { // money (light-blue)
            $lblStyle = 12; $valCell = ['v' => $value, 's' => 13, 't' => 'n'];
        }
        $xlsx->addRow([
            ['v' => $label, 's' => $lblStyle, 't' => 's'],
            null, null,
            $valCell,
        ]);
        $xlsx->merge('A' . $curRow . ':C' . $curRow);
    }

    // Grand total row
    $curRow++;
    $xlsx->addRow([
        ['v' => 'GRAND TOTAL (INCL. TAX)', 's' => 14, 't' => 's'],
        null, null,
        ['v' => round($grandTotal, 2), 's' => 15, 't' => 'n'],
    ]);
    $xlsx->merge('A' . $curRow . ':C' . $curRow);

    // Amount in words row
    $curRow++;
    $xlsx->addRow([['v' => 'AMOUNT IN WORDS: ' . strtoupper($amountInWords), 's' => 17, 't' => 's']]);
    $xlsx->merge('A' . $curRow . ':D' . $curRow);

    // Spacer
    $curRow++;
    $xlsx->addRow([['v' => '', 's' => 0, 't' => 's']]);

    /* --- PAYMENT STATUS SUMMARY --- */
    $unpaidAmount = $totalSum - $paidAmount;

    $curRow++;
    $xlsx->addRow([['v' => 'PAYMENT STATUS SUMMARY', 's' => 16, 't' => 's']]);
    $xlsx->merge('A' . $curRow . ':C' . $curRow);

    $curRow++;
    $xlsx->addRow([
        ['v' => 'Status', 's' => 22, 't' => 's'],
        ['v' => 'No. of Invoices', 's' => 22, 't' => 's'],
        ['v' => 'Invoice Amount (Rs.)', 's' => 22, 't' => 's'],
    ]);

    $curRow++;
    $xlsx->addRow([
        ['v' => 'PAID', 's' => 24, 't' => 's'],
        ['v' => $paidCount, 's' => 20, 't' => 'n'],
        ['v' => round($paidAmount, 2), 's' => 19, 't' => 'n'],
    ]);

    $curRow++;
    $xlsx->addRow([
        ['v' => 'UNPAID', 's' => 25, 't' => 's'],
        ['v' => $unpaidCount, 's' => 20, 't' => 'n'],
        ['v' => round($unpaidAmount, 2), 's' => 19, 't' => 'n'],
    ]);

    invoiceReportLog('excel.rows.done', ['invoice_count' => count($invoices), 'last_row' => $curRow]);
    return $xlsx->toString($typeLabel . ' Invoices');
}
