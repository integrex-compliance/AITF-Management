<?php
$favicoBaseHead = '';
if (isset($_SERVER['HTTP_HOST'])) {
    $scriptNameHead = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (strpos($scriptNameHead, '/public_html/') !== false) {
        $favicoBaseHead = '/public_html/';
    } else {
        $favicoBaseHead = '/';
    }
}
$favicoVersionHead = '4';
?>
<link rel="shortcut icon" href="<?php echo $favicoBaseHead; ?>aitfImages/favico/favicon.ico?v=<?php echo $favicoVersionHead; ?>" type="image/x-icon">
<link rel="icon" type="image/x-icon" href="<?php echo $favicoBaseHead; ?>aitfImages/favico/favicon.ico?v=<?php echo $favicoVersionHead; ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $favicoBaseHead; ?>aitfImages/favico/favicon.png?v=<?php echo $favicoVersionHead; ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $favicoBaseHead; ?>aitfImages/favico/apple-touch-icon.png?v=<?php echo $favicoVersionHead; ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link media="all" type="text/css" rel="stylesheet" href="css/bootstrap/css/bootstrap.min.css">
<script src="js/moment.min.js"></script>
<script src="css/bootstrap/js/jquery.min.js"></script>
<script src="css/bootstrap/js/bootstrap.min.js"></script>
<script src="css/bootstrap/js/jquery-ui.js"></script>

<link href="css/jquery-ui.css" rel="stylesheet">
<link media="all" type="text/css" rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="css/app.css">
<link rel="stylesheet" href="css/basic.css">
<link rel="stylesheet" href="css/float-label.css">
<link rel="stylesheet" href="css/editable-table.css">
<link rel="stylesheet" href="css/spinner.css">
<link href="css/jquery.flexdatalist.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script src="js/form-fancy-control.js"></script>
<script src="js/jquery.flexdatalist.js"></script>
<script src="js/app.js"></script>
<script type="text/javascript" src="./tinymce/tinymce.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* AITF Conflict Dialogue & Alert Box Premium Styling */
.aitf-conflict-swal {
    border-radius: 18px !important;
    padding: 26px 24px 22px !important;
    box-shadow: 0 20px 45px rgba(15, 23, 42, 0.22), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    border: 1px solid #fecdd3 !important;
    max-width: 440px !important;
    background: #ffffff !important;
}
.aitf-conflict-swal .swal2-icon.swal2-error {
    width: 58px !important;
    height: 58px !important;
    margin: 2px auto 14px !important;
    border: 3px solid #ef4444 !important;
    color: #ef4444 !important;
}
.aitf-conflict-swal .swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
    background-color: #ef4444 !important;
    height: 3.5px !important;
    top: 25px !important;
}
.aitf-conflict-swal .swal2-title {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    padding: 0 !important;
    margin: 0 0 10px 0 !important;
    letter-spacing: -0.3px !important;
    line-height: 1.3 !important;
}
.aitf-conflict-swal .swal2-html-container {
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
}
.aitf-conflict-card {
    background: #fff1f2;
    border: 1.5px solid #fecdd3;
    border-radius: 12px;
    padding: 16px 14px;
    text-align: center;
    margin: 6px 0 16px 0;
    box-shadow: 0 2px 6px rgba(225, 29, 72, 0.04);
}
.aitf-conflict-card-title {
    font-size: 15px;
    font-weight: 700;
    color: #9f1239;
    line-height: 1.45;
    margin-bottom: 8px;
}
.aitf-conflict-card-name {
    color: #be123c;
    text-decoration: underline;
    text-underline-offset: 3px;
    font-weight: 800;
}
.aitf-conflict-card-sub {
    font-size: 13.5px;
    font-weight: 600;
    color: #475569;
    background: #ffffff;
    border-radius: 8px;
    padding: 7px 14px;
    display: inline-block;
    border: 1px dashed #cbd5e1;
}
.aitf-conflict-btn {
    background-color: #0F2742 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    padding: 10px 30px !important;
    border-radius: 8px !important;
    letter-spacing: 0.3px !important;
    box-shadow: 0 4px 12px rgba(15, 39, 66, 0.25) !important;
    border: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}
.aitf-conflict-btn:hover {
    background-color: #1a3c61 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 16px rgba(15, 39, 66, 0.35) !important;
}
</style>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
(function () {
    try {
        if (localStorage.getItem('aitf_theme') === 'dark') {
            document.documentElement.classList.add('theme-dark');
        }
    } catch (e) {}
})();
</script>
