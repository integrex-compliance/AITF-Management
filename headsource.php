<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-18544303561"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-18544303561');
</script>
<script>
  gtag('config', 'G-ZH2QWSFTB0');
</script>
<?php
// Smart Title Resolver if not explicitly provided
if (!isset($pageTitle) || trim($pageTitle) === '') {
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    $cleanPath = trim(parse_url($reqUri, PHP_URL_PATH) ?? '', '/');
    $pathSegments = explode('/', $cleanPath);
    $lastSegment = strtolower(end($pathSegments) ?: '');
    $scriptBase = strtolower(basename($_SERVER['SCRIPT_NAME'] ?? '', '.php'));
    
    $lookupKey = ($lastSegment !== '' && $lastSegment !== 'public_html' && $lastSegment !== 'index.php') ? $lastSegment : $scriptBase;
    $lookupKey = preg_replace('/\.php$/', '', $lookupKey);

    $titleMap = [
        'about-us' => 'About AITF | Assessment & Institute for Technical Forum',
        'aboutus' => 'About AITF | Assessment & Institute for Technical Forum',
        'about' => 'About AITF | Assessment & Institute for Technical Forum',
        'career' => 'Career & Opportunity | AITF',
        'careers' => 'Career & Opportunity | AITF',
        'contact-us' => 'Contact Us | AITF Management Certification',
        'contactus' => 'Contact Us | AITF Management Certification',
        'contact' => 'Contact Us | AITF Management Certification',
        'certification-process' => 'ISO Certification Process | AITF',
        'certificationprocess' => 'ISO Certification Process | AITF',
        'expected-outcome' => 'Expected Outcomes | AITF',
        'expectedoutcome' => 'Expected Outcomes | AITF',
        'complaints-appeals' => 'Complaints & Appeals | AITF',
        'complaintsappeals' => 'Complaints & Appeals | AITF',
        'impartiality' => 'Impartiality Policy | AITF',
        'certificate-validation' => 'Certificate Validation | AITF',
        'validation' => 'Certificate Validation | AITF',
        'application-form' => 'Online ISO Certification Application | AITF',
        'application' => 'Online ISO Certification Application | AITF',
        'certification-guidelines' => 'Certification Guideline | AITF',
        'certificationguideline' => 'Certification Guideline | AITF',
        'auditor-guidelines' => 'Auditor Guidelines | AITF',
        'auditorguideline' => 'Auditor Guidelines | AITF',
        'logo-usage-guidelines' => 'Logo Usage Guidelines | AITF',
        'useoflogo' => 'Logo Usage Guidelines | AITF',
        'transition-guidelines' => 'Transition Guidelines | AITF',
        'transitionguideline' => 'Transition Guidelines | AITF',
        'nc-closure' => 'NC Closure Process | AITF',
        'ncclosure' => 'NC Closure Process | AITF',
        'nc_closing' => 'NC Closing Form | AITF',
        'fill_nc_closing_form' => 'NC Closing Form | AITF',
        'info-updation' => 'Information Updation Form | AITF',
        'info_updation_form' => 'Information Updation Form | AITF',
        'services' => 'Our Services | AITF',
        'service' => 'Our Services | AITF',
        'training' => 'Training & Auditor Programs | AITF',
        'news' => 'News & Circulars | AITF',
        'circuler' => 'Circulars & Notifications | AITF',
        'faq' => 'Frequently Asked Questions | AITF',
        'policy' => 'Terms & Conditions | AITF',
        'terms-and-conditions' => 'Terms & Conditions | AITF',
        'qms_iso_9001' => 'ISO 9001 QMS Certification | AITF',
        'ems_iso_9001' => 'ISO 14001 EMS Certification | AITF',
        'isms_iso_27001' => 'ISO 27001 ISMS Certification | AITF',
        'bcms_iso_22301' => 'ISO 22301 BCMS Certification | AITF',
        'md_qms_iso' => 'ISO 13485 MD-QMS Certification | AITF',
        'fsms_iso_22000' => 'ISO 22000 FSMS Certification | AITF'
    ];

    if (isset($titleMap[$lookupKey])) {
        $pageTitle = $titleMap[$lookupKey];
    }
}

$favicoBase = '';
if (isset($_SERVER['HTTP_HOST'])) {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (strpos($scriptName, '/public_html/') !== false) {
        $favicoBase = '/public_html/';
    } else {
        $favicoBase = '/';
    }
}
$favicoVersion = '5';

// Auto-sync with headSource.php on case-sensitive Linux filesystems
$altHeadSourcePath = __DIR__ . '/headSource.php';
if (file_exists($altHeadSourcePath) && realpath($altHeadSourcePath) !== realpath(__FILE__)) {
    @copy(__FILE__, $altHeadSourcePath);
}
?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Site Metas -->
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : 'AITF - Assessment &amp; Institute for Technical Forum'; ?></title>
    <meta name="keywords" content="<?php echo isset($pageKeywords) ? htmlspecialchars($pageKeywords, ENT_QUOTES, 'UTF-8') : ''; ?>">
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') : ''; ?>">
    <meta name="author" content="">
    <?php if (isset($canonicalUrl)): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <!-- Site Icons -->
    <link rel="shortcut icon" href="<?php echo $favicoBase; ?>aitfImages/favico/favicon.ico?v=<?php echo $favicoVersion; ?>" type="image/x-icon" />
    <link rel="icon" type="image/x-icon" href="<?php echo $favicoBase; ?>aitfImages/favico/favicon.ico?v=<?php echo $favicoVersion; ?>" />
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $favicoBase; ?>aitfImages/favico/favicon.png?v=<?php echo $favicoVersion; ?>" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $favicoBase; ?>aitfImages/favico/apple-touch-icon.png?v=<?php echo $favicoVersion; ?>" />
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": "https://www.aitf.co.in/#website",
          "url": "https://www.aitf.co.in/",
          "name": "AITF",
          "description": "Assessment & Institute for Technical Forum"
        },
        {
          "@type": "Organization",
          "@id": "https://www.aitf.co.in/#organization",
          "name": "AITF Management Certification Limited",
          "url": "https://www.aitf.co.in/",
          "logo": "https://www.aitf.co.in/aitfImages/favico/favicon.png"
        }
      ]
    }
    </script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/outer/bootstrap.min.css" />
    
    <!-- Pogo Slider CSS -->
    <link rel="stylesheet" href="css/outer/pogo-slider.min.css" />
    <!-- Site CSS -->
    <link rel="stylesheet" href="css/outer/style.css" />
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="css/outer/responsive.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/outer/custom.css" />

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

<!-- ALL JS FILES -->
    <script src="js/outer/jquery.min.js"></script>
	<script src="js/outer/popper.min.js"></script>
    <script src="js/outer/bootstrap.min.js"></script>
    <!-- ALL PLUGINS -->
    <script src="js/outer/jquery.magnific-popup.min.js" defer></script>
    <script src="js/outer/jquery.pogo-slider.min.js" defer></script>
    <script src="js/outer/slider-index.js" defer></script>
    <script src="js/outer/form-validator.min.js" defer></script>
    <script src="js/outer/contact-form-script.js" defer></script>
    <script src="js/outer/isotope.min.js" defer></script>
    <script src="js/outer/images-loded.min.js" defer></script>
    <script src="js/outer/custom.js" defer></script>
