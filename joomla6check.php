<?php
/**
 * =============================================================================
 *  بررسی‌کننده پیش‌نیازهای جوملا! ۶ روی هاست اشتراکی
 *  Joomla! 6 Shared-Hosting Readiness Checker
 * -----------------------------------------------------------------------------
 *  نسخه‌ی ابزار : 2.1.0
 *  توسعه‌دهنده  : شرکت نوید ایرانیان  |  Navid Iranian Co.
 *  خدمات        : طراحی وب‌سایت · سئو · میزبانی وب · ثبت دامنه · دیجیتال مارکتینگ
 *  وب‌سایت      : navidiranian.com · navidiranian.co.ir · joomlafarsi.co.ir · cmssupport.ir
 *  تلفن         : +98 939 556 6652   |   +98 21 9130 3662
 * -----------------------------------------------------------------------------
 *  © 1405 / 2026 — کلیه حقوق برای شرکت نوید ایرانیان محفوظ است.
 * -----------------------------------------------------------------------------
 *  روش استفاده / Usage:
 *    ۱) این فایل را در پوشه‌ی اصلی هاست (public_html) آپلود کنید.
 *       Upload this file to your hosting root (public_html).
 *    ۲) در مرورگر باز کنید / Open in your browser:
 *       https://your-domain.com/joomla6check.php
 *    ۳) گزارش را بخوانید، متن آماده را برای پشتیبانی هاست بفرستید.
 *       Read the report, copy the ready-made text and send it to your host's support.
 *    ۴) پس از پایان کار، حتماً فایل را حذف کنید (دکمه‌ی «حذف این فایل»).
 *       When done, delete this file (use the "Delete this file" button).
 *
 *  زبان / Language: فارسی (پیش‌فرض) و انگلیسی — با ?lang=en یا ?lang=fa قابل تغییر است.
 *                    Persian (default) and English — switch with ?lang=en or ?lang=fa.
 *  سازگاری / Compatibility: PHP 5.6 تا 8.5 / PHP 5.6 to 8.5
 *  (این فایل عمداً با نحو قدیمی PHP نوشته شده تا روی هاست‌های قدیمی هم اجرا شود
 *   و بتواند خودِ «قدیمی بودن نسخه‌ی PHP» را گزارش کند، نه اینکه خطای Syntax بدهد.
 *   Deliberately written in old-style PHP syntax so it still runs on legacy hosts
 *   and can report an outdated PHP version itself, instead of throwing a syntax error.)
 * =============================================================================
 */

/* ---------------------------------------------------------------------------
 |  ۱) تنظیمات / Settings
 --------------------------------------------------------------------------- */

// برای محافظت از گزارش، اینجا یک کلید بگذارید و با ?key=... باز کنید.
// اگر خالی بماند، ابزار خودش در همین پوشه یک کلید تصادفی می‌سازد (به بخش ۱.۲ نگاه کنید).
// To protect the report, set a key here and open with ?key=... .
// If left empty, the tool auto-generates a random key in this folder (see section 1.2 below).
define('NVD_ACCESS_KEY', '');

define('NVD_VERSION',  '2.1.0');
define('NVD_COMPANY',  'شرکت نوید ایرانیان');
define('NVD_COMPANY_EN', 'Navid Iranian Co.');
define('NVD_PHONE1',   '+989395566652');
define('NVD_PHONE2',   '+982191303662');
define('NVD_SITES', array(
    'navidiranian.com'   => 'https://navidiranian.com/',
    'navidiranian.co.ir' => 'https://navidiranian.co.ir/',
    'joomlafarsi.co.ir'  => 'https://joomlafarsi.co.ir/',
    'cmssupport.ir'      => 'https://cmssupport.ir/',
));

@ini_set('display_errors', '0');
@error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
@set_time_limit(120);
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

/* ---------------------------------------------------------------------------
 |  ۱.۱) تشخیص زبان / Language detection
 --------------------------------------------------------------------------- */
$langParam = isset($_GET['lang']) ? strtolower(trim((string)$_GET['lang'])) : '';
if ($langParam !== 'en' && $langParam !== 'fa') {
    $langParam = (isset($_COOKIE['nvd_lang']) && $_COOKIE['nvd_lang'] === 'en') ? 'en' : 'fa';
}
define('NVD_LANG', $langParam);
if (!headers_sent()) {
    @setcookie('nvd_lang', NVD_LANG, time() + 60 * 60 * 24 * 365, '/');
}

/**
 * تابع ترجمه: بین متن فارسی و انگلیسی بر اساس زبان فعلی انتخاب می‌کند.
 * Translation helper: picks Persian or English text based on the current language.
 * $args در صورت وجود با vsprintf روی رشته‌ی انتخاب‌شده اعمال می‌شود (٪s , ٪d ...).
 * If $args is given, it is applied to the chosen string with vsprintf (%s, %d ...).
 */
function T($fa, $en, $args = array()) {
    $s = (NVD_LANG === 'en') ? $en : $fa;
    return empty($args) ? $s : vsprintf($s, $args);
}

function nvd_lang_url($lang) {
    $q = $_GET;
    $q['lang'] = $lang;
    $self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
    return htmlspecialchars($self . '?' . http_build_query($q), ENT_QUOTES, 'UTF-8');
}

/** تولید رشته‌ی هگزادسیمال تصادفی و امن، با سازگاری به عقب تا PHP 5.6 / Secure random hex string, PHP 5.6-compatible */
function nvd_random_hex($bytes) {
    if (function_exists('random_bytes')) {
        try { return bin2hex(random_bytes($bytes)); } catch (Exception $e) { /* fall through */ }
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        $r = @openssl_random_pseudo_bytes($bytes);
        if ($r !== false) return bin2hex($r);
    }
    $s = '';
    for ($i = 0; $i < $bytes; $i++) $s .= chr(mt_rand(0, 255));
    return bin2hex($s);
}

/** توکن CSRF را از کوکی می‌خواند یا در صورت نبود می‌سازد / Reads the CSRF token from a cookie, or creates one */
function nvd_csrf_token() {
    if (isset($_COOKIE['nvd_csrf']) && preg_match('/^[a-f0-9]{32}$/', $_COOKIE['nvd_csrf'])) {
        return $_COOKIE['nvd_csrf'];
    }
    $t = nvd_random_hex(16);
    if (!headers_sent()) {
        @setcookie('nvd_csrf', $t, 0, '/', '', false, true);
    }
    return $t;
}
$NVD_CSRF = nvd_csrf_token();

/* ---------------------------------------------------------------------------
 |  ۱.۲) کلید دسترسی — دستی یا خودکار / Access key — manual or auto-generated
 |  اگر NVD_ACCESS_KEY را خالی بگذارید، این ابزار یک کلید تصادفی می‌سازد و آن را
 |  در فایل .nvd6-lock.php کنار همین اسکریپت نگه می‌دارد (چون فایل php است، حتی
 |  اگر مستقیماً درخواست شود، به‌جای افشای متن، فقط اجرا و خالی برمی‌گردد).
 |  توجه: در اولین بازدید (پیش از ساخته‌شدن قفل) هر کسی که زودتر از شما این آدرس
 |  را باز کند کلید را می‌بیند؛ برای هاست‌های حساس، از قبل NVD_ACCESS_KEY را
 |  به‌صورت دستی تنظیم کنید تا اصلاً به این حالت نیاز نباشد.
 |
 |  If NVD_ACCESS_KEY is left empty, this tool generates a random key and stores
 |  it in .nvd6-lock.php next to this script (being a .php file, even a direct
 |  request to it just executes and returns empty instead of leaking the text).
 |  Note: on the very first visit (before the lock exists) whoever opens this
 |  URL first sees the key; on sensitive hosts, set NVD_ACCESS_KEY manually in
 |  advance so this auto-generation step is never needed.
 --------------------------------------------------------------------------- */
$nvdKeyFile         = __DIR__ . '/.nvd6-lock.php';
$nvdAutoKey         = '';
$nvdKeyIsNew        = false;
$nvdKeyFileWritable = true;

if (NVD_ACCESS_KEY === '') {
    if (@is_file($nvdKeyFile)) {
        $raw = @file_get_contents($nvdKeyFile);
        if ($raw && preg_match('/NVDKEY:([a-f0-9]{32})/', $raw, $m)) {
            $nvdAutoKey = $m[1];
        }
    }
    if ($nvdAutoKey === '') {
        $nvdAutoKey = nvd_random_hex(16);
        $written = @file_put_contents($nvdKeyFile, "<?php exit; /* NVD6-LOCK - do not delete. NVDKEY:" . $nvdAutoKey . " */\n");
        if ($written === false) {
            $nvdKeyFileWritable = false;
        } else {
            @chmod($nvdKeyFile, 0600);
            $nvdKeyIsNew = true;
        }
    }
}
define('NVD_EFFECTIVE_KEY', (NVD_ACCESS_KEY !== '') ? NVD_ACCESS_KEY : ($nvdKeyFileWritable ? $nvdAutoKey : ''));
$nvdNoLockWarning = (NVD_ACCESS_KEY === '' && !$nvdKeyFileWritable);

// قفل دسترسی / Access lock
if (NVD_EFFECTIVE_KEY !== '' && !$nvdKeyIsNew) {
    $k = isset($_GET['key']) ? (string)$_GET['key'] : '';
    if (!hash_equals(NVD_EFFECTIVE_KEY, $k)) {
        header('HTTP/1.1 403 Forbidden');
        echo '<meta charset="utf-8"><div style="font:16px Tahoma;direction:' . (NVD_LANG === 'en' ? 'ltr' : 'rtl') . ';padding:40px">'
           . T('دسترسی مجاز نیست. کلید صحیح را در آدرس وارد کنید.', 'Access denied. Enter the correct key in the URL.')
           . '</div>';
        exit;
    }
}

/* ---------------------------------------------------------------------------
 |  ۲) مقادیر مرجع جوملا ۶ (منبع: manual.joomla.org — Technical Requirements)
 |     Joomla 6 reference values (source: manual.joomla.org — Technical Requirements)
 --------------------------------------------------------------------------- */
$J6 = array(
    'php_min'      => '8.3.0',   'php_rec'      => '8.4.0',
    'mysql_min'    => '8.0.13',  'mysql_rec'    => '8.4',
    'mariadb_min'  => '10.4',    'mariadb_rec'  => '12.0',
    'pgsql_min'    => '12.0',    'pgsql_rec'    => '17.6',
    'memory_rec'   => 256,       'memory_min'   => 128,   // MB
    'upload_rec'   => 64,        'upload_min'   => 16,    // MB
    'exec_min'     => 30,        'exec_rec'     => 120,   // seconds
    'inputvars_min'=> 1000,      'inputvars_rec'=> 5000,
    'disk_min'     => 300,       'disk_rec'     => 1024,  // MB
);

// پایان پشتیبانی امنیتی نسخه‌های PHP / PHP branch security-support end dates
$PHP_EOL_MAP = array(
    '5.6'=>'2018-12-31','7.0'=>'2019-01-10','7.1'=>'2019-12-01','7.2'=>'2020-11-30',
    '7.3'=>'2021-12-06','7.4'=>'2022-11-28','8.0'=>'2023-11-26','8.1'=>'2025-12-31',
    '8.2'=>'2026-12-31','8.3'=>'2027-12-31','8.4'=>'2028-12-31','8.5'=>'2029-12-31',
);

/* ---------------------------------------------------------------------------
 |  ۳) توابع کمکی / Helper functions
 --------------------------------------------------------------------------- */

function nvd_e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function nvd_get($arr, $key, $default = null) {
    return (is_array($arr) && isset($arr[$key])) ? $arr[$key] : $default;
}

/** تبدیل 128M / 1G / -1 به بایت / Convert 128M / 1G / -1 to bytes */
function nvd_bytes($val) {
    $val = trim((string)$val);
    if ($val === '') return 0;
    if ($val === '-1') return -1;
    $last = strtolower(substr($val, -1));
    $num  = (float)$val;
    if ($last === 'g') $num *= 1024 * 1024 * 1024;
    elseif ($last === 'm') $num *= 1024 * 1024;
    elseif ($last === 'k') $num *= 1024;
    return (float)$num;
}

function nvd_mb($bytes) {
    if ($bytes < 0) return -1;
    return round($bytes / 1048576, 1);
}

function nvd_hsize($bytes) {
    if ($bytes < 0) return T('نامحدود', 'Unlimited');
    $u = array('B','KB','MB','GB','TB'); $i = 0;
    while ($bytes >= 1024 && $i < 4) { $bytes /= 1024; $i++; }
    return round($bytes, 1) . ' ' . $u[$i];
}

function nvd_ini($key) {
    $v = @ini_get($key);
    return ($v === false || $v === null) ? '' : (string)$v;
}

function nvd_ini_on($key) {
    $v = strtolower(trim(nvd_ini($key)));
    return ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

function nvd_ext($name) { return extension_loaded($name); }

function nvd_func($name) {
    if (!function_exists($name)) return false;
    $disabled = array_map('trim', explode(',', strtolower(nvd_ini('disable_functions'))));
    return !in_array(strtolower($name), $disabled, true);
}

function nvd_fa_num($s) {
    if (NVD_LANG === 'en') return (string)$s;
    $en = array('0','1','2','3','4','5','6','7','8','9');
    $fa = array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹');
    return str_replace($en, $fa, (string)$s);
}

function nvd_yesno($b) { return $b ? T('فعال', 'Enabled') : T('غیرفعال', 'Disabled'); }

/**
 * ساخت یک آیتم بررسی / Build one check item
 * $status : pass | warn | fail | info
 */
function nvd_item($label, $status, $actual, $expected, $note = '', $weight = 1, $critical = false, $fix = '') {
    return array(
        'label'    => $label,
        'status'   => $status,
        'actual'   => $actual,
        'expected' => $expected,
        'note'     => $note,
        'weight'   => $weight,
        'critical' => $critical,
        'fix'      => $fix,
    );
}

/* ---------------------------------------------------------------------------
 |  ۴) جمع‌آوری اطلاعات پایه / Collecting base info
 --------------------------------------------------------------------------- */

$phpVersion   = PHP_VERSION;
$phpBranch    = implode('.', array_slice(explode('.', $phpVersion), 0, 2));
$sapi         = php_sapi_name();
$serverSoft   = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : T('نامشخص', 'Unknown');
$isLiteSpeed  = (stripos($serverSoft, 'litespeed') !== false || stripos($sapi, 'litespeed') !== false);
$isApache     = (stripos($serverSoft, 'apache') !== false);
$isNginx      = (stripos($serverSoft, 'nginx') !== false);
$isIIS        = (stripos($serverSoft, 'iis') !== false);
$docRoot      = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
$isHttps      = (
    (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' && $_SERVER['HTTPS'] !== '') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
    (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
);
$hostName     = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$deep         = (isset($_GET['deep']) && $_GET['deep'] === '1');

$sections = array();

/* ===========================================================================
 |  بخش A — هسته، نسخه‌ها و موتور اجرا / Section A — Core, versions & runtime
 =========================================================================== */
$A = array();

// A1 - نسخه PHP / PHP version
$phpOk  = version_compare($phpVersion, $J6['php_min'], '>=');
$phpRec = version_compare($phpVersion, $J6['php_rec'], '>=');
$A[] = nvd_item(
    T('نسخه PHP', 'PHP Version'),
    $phpOk ? ($phpRec ? 'pass' : 'warn') : 'fail',
    $phpVersion,
    T('حداقل %s — پیشنهادی %s یا بالاتر', 'Minimum %s — recommended %s or higher', array($J6['php_min'], $J6['php_rec'])),
    $phpOk
        ? ($phpRec ? T('نسخه PHP کاملاً مناسب جوملا ۶ است.', 'This PHP version is fully suitable for Joomla 6.')
                   : T('جوملا ۶ نصب می‌شود، اما نسخه ۸.۴ یا ۸.۵ سرعت و امنیت بیشتری می‌دهد.', 'Joomla 6 will install, but PHP 8.4 or 8.5 gives better speed and security.'))
        : T('جوملا ۶ روی این نسخه اصلاً نصب نمی‌شود. از کنترل‌پنل (cPanel → MultiPHP Manager یا Select PHP Version) نسخه را ارتقا دهید.',
            'Joomla 6 will not install at all on this version. Upgrade it from your control panel (cPanel → MultiPHP Manager or Select PHP Version).'),
    6, true,
    $phpOk ? '' : T('ارتقای PHP به نسخه %s یا بالاتر', 'Upgrade PHP to version %s or higher', array($J6['php_rec']))
);

// A2 - پشتیبانی امنیتی نسخه PHP / PHP branch security support
$eolDate = nvd_get($PHP_EOL_MAP, $phpBranch, null);
if ($eolDate !== null) {
    $expired = (strtotime($eolDate) < time());
    $A[] = nvd_item(
        T('پشتیبانی امنیتی نسخه PHP', 'PHP Version Security Support'),
        $expired ? 'fail' : 'pass',
        T('PHP %s — تا %s', 'PHP %s — until %s', array($phpBranch, $eolDate)),
        T('نسخه‌ای که هنوز وصله‌ی امنیتی می‌گیرد', 'A version that still receives security patches'),
        $expired
            ? T('این شاخه از PHP دیگر وصله‌ی امنیتی دریافت نمی‌کند؛ ماندن روی آن یعنی آسیب‌پذیری‌های اصلاح‌نشده.',
                'This PHP branch no longer receives security patches; staying on it means unpatched vulnerabilities.')
            : T('این شاخه هنوز در دوره‌ی پشتیبانی امنیتی رسمی PHP قرار دارد.', 'This branch is still within PHP\'s official security-support window.'),
        3, false,
        $expired ? T('مهاجرت به شاخه‌ای از PHP که هنوز پشتیبانی می‌شود', 'Migrate to a PHP branch that is still supported') : ''
    );
}

// A3 - حالت اجرای PHP / PHP execution mode (SAPI)
$sapiLabel = $sapi;
$sapiGood  = (stripos($sapi, 'fpm') !== false || $isLiteSpeed || stripos($sapi, 'lsapi') !== false);
$A[] = nvd_item(
    T('حالت اجرای PHP (SAPI)', 'PHP Execution Mode (SAPI)'),
    'info',
    $sapiLabel,
    T('php-fpm یا LiteSpeed LSAPI (بهینه‌ترین حالت)', 'php-fpm or LiteSpeed LSAPI (optimal mode)'),
    $sapiGood ? T('حالت اجرای فعلی برای جوملا بهینه است.', 'The current execution mode is optimal for Joomla.')
              : T('در حالت CGI/mod_php ممکن است مالکیت فایل‌ها و کارایی مشکل‌ساز شود؛ اگر هاست گزینه‌ی FPM دارد فعال کنید.',
                  'In CGI/mod_php mode, file ownership and performance can become an issue; enable FPM if your host offers it.'),
    0
);

// A4 - وب‌سرور / Web server
$wsStatus = ($isApache || $isLiteSpeed || $isNginx || $isIIS) ? 'pass' : 'info';
$A[] = nvd_item(
    T('وب‌سرور', 'Web Server'),
    $wsStatus,
    $serverSoft,
    'Apache 2.4+ / LiteSpeed / Nginx 1.26+ / IIS 10+',
    $isNginx ? T('روی Nginx فایل htaccess کار نمی‌کند؛ برای URLهای SEF باید قواعد rewrite در کانفیگ سرور اضافه شود.',
                  'On Nginx, .htaccess does not work; rewrite rules must be added to the server config for SEF URLs.')
             : T('وب‌سرور شناسایی‌شده با جوملا ۶ سازگار است.', 'The detected web server is compatible with Joomla 6.'),
    2
);

// A5 - درایور دیتابیس / Database driver
$drvMysqli   = nvd_ext('mysqli');
$drvPdoMysql = class_exists('PDO') ? in_array('mysql', PDO::getAvailableDrivers(), true) : false;
$drvPdoPgsql = class_exists('PDO') ? in_array('pgsql', PDO::getAvailableDrivers(), true) : false;
$drvNd       = nvd_ext('mysqlnd');
$dbDrvOk     = ($drvMysqli || $drvPdoMysql || $drvPdoPgsql);
$drvList     = array();
if ($drvMysqli)   $drvList[] = 'mysqli';
if ($drvPdoMysql) $drvList[] = 'pdo_mysql';
if ($drvPdoPgsql) $drvList[] = 'pdo_pgsql';
if ($drvNd)       $drvList[] = 'mysqlnd';
$A[] = nvd_item(
    T('درایور اتصال به دیتابیس', 'Database Connection Driver'),
    $dbDrvOk ? 'pass' : 'fail',
    $drvList ? implode(' , ', $drvList) : T('هیچ‌کدام', 'None'),
    T('حداقل یکی از mysqli / pdo_mysql / pdo_pgsql', 'At least one of mysqli / pdo_mysql / pdo_pgsql'),
    $dbDrvOk ? T('امکان اتصال جوملا به دیتابیس فراهم است.', 'Joomla is able to connect to the database.')
             : T('بدون درایور دیتابیس، نصب‌کننده‌ی جوملا در همان گام اول متوقف می‌شود.', 'Without a database driver, the Joomla installer stops at the very first step.'),
    6, true,
    $dbDrvOk ? '' : T('فعال‌سازی افزونه‌ی mysqli یا pdo_mysql', 'Enable the mysqli or pdo_mysql extension')
);

// A6 - جوملای نصب‌شده (در صورت وجود) / Existing Joomla install (if any)
$existingJoomla = '';
$verFile = __DIR__ . '/libraries/src/Version.php';
if (@is_file($verFile)) {
    $src = @file_get_contents($verFile);
    if ($src) {
        $maj = $min = $pat = '';
        if (preg_match('/MAJOR_VERSION\s*=\s*(\d+)/', $src, $m)) $maj = $m[1];
        if (preg_match('/MINOR_VERSION\s*=\s*(\d+)/', $src, $m)) $min = $m[1];
        if (preg_match('/PATCH_VERSION\s*=\s*(\d+)/', $src, $m)) $pat = $m[1];
        if ($maj !== '') $existingJoomla = $maj . '.' . $min . '.' . $pat;
    }
}
if ($existingJoomla !== '') {
    $isJ6 = (int)substr($existingJoomla, 0, 1) >= 6;
    $A[] = nvd_item(
        T('جوملای نصب‌شده در این مسیر', 'Joomla Installed in This Path'),
        'info',
        'Joomla ' . $existingJoomla,
        '—',
        $isJ6 ? T('روی این مسیر جوملا ۶ نصب است؛ گزارش زیر وضعیت میزبانی همین سایت را نشان می‌دهد.',
                  'Joomla 6 is installed at this path; the report below reflects this site\'s hosting status.')
              : T('یک نسخه‌ی قدیمی‌تر جوملا نصب است. پیش از ارتقا به ۶، از سایت و دیتابیس نسخه پشتیبان بگیرید و سازگاری افزونه‌ها را بررسی کنید.',
                  'An older Joomla version is installed. Before upgrading to 6, back up the site and database and check extension compatibility.'),
        0
    );
}

$sections[] = array(
    'id' => 'core', 'title' => T('هسته، نسخه‌ها و موتور اجرا', 'Core, Versions & Runtime'),
    'desc' => T('اولین چیزی که نصب‌کننده‌ی جوملا بررسی می‌کند: نسخه‌ی PHP، وب‌سرور و درایور دیتابیس.',
                'The first thing the Joomla installer checks: PHP version, web server and database driver.'),
    'items' => $A
);

/* ===========================================================================
 |  بخش B — افزونه‌های الزامی PHP / Section B — Required PHP extensions
 =========================================================================== */
$B = array();
$requiredExts = array(
    'json'      => array(T('پردازش JSON — قلب ارتباط داخلی جوملا و API', 'JSON processing — the heart of Joomla\'s internal communication and API'), true),
    'simplexml' => array(T('خواندن فایل‌های XML افزونه‌ها و به‌روزرسانی', 'Reading extension and update XML files'), true),
    'dom'       => array(T('پردازش HTML و XML در ویرایشگر و فیلترها', 'HTML/XML processing in the editor and filters'), true),
    'libxml'    => array(T('کتابخانه‌ی پایه‌ی XML', 'Core XML library'), true),
    'zlib'      => array(T('فشرده‌سازی و باز کردن بسته‌های نصب', 'Compressing and extracting install packages'), true),
    'gd'        => array(T('پردازش تصویر، بندانگشتی و برش عکس', 'Image processing, thumbnails and image cropping'), true),
    'pcre'      => array(T('موتور عبارات باقاعده', 'Regular expression engine'), true),
    'session'   => array(T('مدیریت نشست و ورود کاربران', 'Session management and user login'), true),
    'filter'    => array(T('اعتبارسنجی ورودی‌ها', 'Input validation'), true),
    'hash'      => array(T('هش رمز عبور و توکن‌ها', 'Password and token hashing'), true),
    'ctype'     => array(T('بررسی نوع کاراکتر', 'Character type checking'), true),
);
foreach ($requiredExts as $ext => $meta) {
    $has = nvd_ext($ext);
    $B[] = nvd_item(
        T('افزونه %s', 'Extension %s', array($ext)),
        $has ? 'pass' : 'fail',
        $has ? T('نصب است', 'Installed') : T('نصب نیست', 'Not installed'),
        T('الزامی', 'Required'),
        $meta[0],
        3, true,
        $has ? '' : T('فعال‌سازی افزونه‌ی PHP به نام %s', 'Enable the PHP extension named %s', array($ext))
    );
}
$sections[] = array(
    'id' => 'ext-req', 'title' => T('افزونه‌های الزامی PHP', 'Required PHP Extensions'),
    'desc' => T('نبود هرکدام از این‌ها یعنی توقف کامل نصب. در cPanel از بخش Select PHP Version → Extensions فعال می‌شوند.',
                'Missing any of these stops the install completely. Enable them in cPanel under Select PHP Version → Extensions.'),
    'items' => $B
);

/* ===========================================================================
 |  بخش C — افزونه‌های توصیه‌شده / Section C — Recommended extensions
 =========================================================================== */
$C = array();
$recommendedExts = array(
    'mbstring'  => array(T('پشتیبانی صحیح از متن فارسی و عربی — عملاً برای سایت فارسی حیاتی است', 'Correct Persian/Arabic text support — essentially vital for a Persian site'), 3),
    'iconv'     => array(T('تبدیل کدگذاری متن‌ها', 'Text encoding conversion'), 2),
    'curl'      => array(T('به‌روزرسانی هسته، نصب افزونه از مخزن، ارسال ایمیل با API', 'Core updates, installing extensions from the repository, sending email via API'), 3),
    'zip'       => array(T('نصب بسته‌های افزونه و قالب با فرمت ZIP', 'Installing ZIP-format extension and template packages'), 3),
    'openssl'   => array(T('اتصال HTTPS، SMTP امن و توکن‌های رمزنگاری‌شده', 'HTTPS connections, secure SMTP and encrypted tokens'), 3),
    'fileinfo'  => array(T('تشخیص نوع فایل در مدیریت رسانه — بدون آن آپلود عکس رد می‌شود', 'File-type detection in media manager — without it, image uploads are rejected'), 3),
    'intl'      => array(T('تاریخ، زبان و مرتب‌سازی چندزبانه', 'Multilingual date, language and sorting support'), 2),
    'exif'      => array(T('خواندن اطلاعات تصویر و چرخش خودکار عکس‌ها', 'Reading image metadata and auto-rotating photos'), 1),
    'sodium'    => array(T('رمزنگاری مدرن برای ورود دو مرحله‌ای و WebAuthn', 'Modern cryptography for two-factor login and WebAuthn'), 2),
    'zend opcache' => array(T('کش کد PHP — تا ۳ برابر افزایش سرعت سایت', 'PHP code cache — up to 3x faster site loading'), 2),
);
foreach ($recommendedExts as $ext => $meta) {
    $has = nvd_ext($ext) || ($ext === 'zend opcache' && nvd_ext('Zend OPcache'));
    $C[] = nvd_item(
        T('افزونه %s', 'Extension %s', array($ext)),
        $has ? 'pass' : 'warn',
        $has ? T('نصب است', 'Installed') : T('نصب نیست', 'Not installed'),
        T('توصیه‌شده', 'Recommended'),
        $meta[0],
        $meta[1], false,
        $has ? '' : T('فعال‌سازی افزونه‌ی PHP به نام %s', 'Enable the PHP extension named %s', array($ext))
    );
}
// imagick اختیاری / imagick optional
$C[] = nvd_item(
    T('افزونه imagick', 'Extension imagick'),
    nvd_ext('imagick') ? 'pass' : 'info',
    nvd_ext('imagick') ? T('نصب است', 'Installed') : T('نصب نیست', 'Not installed'),
    T('اختیاری', 'Optional'),
    T('کیفیت بهتر در تغییر اندازه‌ی تصویر نسبت به GD؛ نبود آن مشکلی ایجاد نمی‌کند.', 'Better image-resize quality than GD; its absence causes no problem.'),
    0
);
$sections[] = array(
    'id' => 'ext-rec', 'title' => T('افزونه‌های توصیه‌شده', 'Recommended Extensions'),
    'desc' => T('جوملا بدون این‌ها نصب می‌شود، اما بخشی از قابلیت‌ها (آپلود تصویر، به‌روزرسانی، متن فارسی) لنگ می‌زند.',
                'Joomla installs without these, but some features (image upload, updates, Persian text) will be crippled.'),
    'items' => $C
);

/* ===========================================================================
 |  بخش D — تنظیمات php.ini / Section D — php.ini settings
 =========================================================================== */
$D = array();
$iniFixes = array();

// memory_limit
$memRaw = nvd_ini('memory_limit');
$memB   = nvd_bytes($memRaw);
$memMB  = ($memB < 0) ? 99999 : nvd_mb($memB);
if ($memMB >= $J6['memory_rec'])      { $st = 'pass'; $nt = T('حافظه برای نصب، به‌روزرسانی و کار با افزونه‌های سنگین کافی است.', 'Memory is sufficient for installation, updates and heavy extensions.'); }
elseif ($memMB >= $J6['memory_min'])  { $st = 'warn'; $nt = T('برای نصب کافی است، اما هنگام به‌روزرسانی هسته یا افزونه‌های بزرگ خطای حافظه می‌گیرید.', 'Enough for installation, but you will hit memory errors during core updates or with large extensions.'); }
else                                  { $st = 'fail'; $nt = T('با این مقدار، نصب یا به‌روزرسانی جوملا در میانه‌ی کار متوقف می‌شود.', 'With this value, Joomla installation or update will stop midway.'); }
if ($st !== 'pass') $iniFixes[] = 'memory_limit = 256M';
$D[] = nvd_item('memory_limit', $st, ($memB < 0 ? T('نامحدود', 'Unlimited') : $memRaw), T('%s یا بیشتر', '%s or more', array('256M')), $nt, 5,
    ($st === 'fail'), ($st === 'pass' ? '' : 'memory_limit = 256M'));

// max_execution_time
$maxExec = (int)nvd_ini('max_execution_time');
if ($maxExec === 0)                    { $st = 'pass'; $nt = T('بدون محدودیت زمانی — مناسب عملیات طولانی مثل به‌روزرسانی هسته.', 'No time limit — suitable for long operations like core updates.'); }
elseif ($maxExec >= $J6['exec_rec'])   { $st = 'pass'; $nt = T('زمان اجرا برای به‌روزرسانی و نصب افزونه کافی است.', 'Execution time is sufficient for updates and extension installs.'); }
elseif ($maxExec >= $J6['exec_min'])   { $st = 'warn'; $nt = T('برای نصب کافی است، اما ارتقای هسته یا ایمپورت دیتابیس ممکن است Timeout بدهد.', 'Enough for installation, but core upgrades or database imports may time out.'); }
else                                   { $st = 'fail'; $nt = T('زمان اجرا کمتر از حداقل جوملاست؛ نصب نیمه‌کاره رها می‌شود.', 'Execution time is below Joomla\'s minimum; installation will be left half-finished.'); }
if ($st !== 'pass') $iniFixes[] = 'max_execution_time = 120';
$D[] = nvd_item('max_execution_time', $st, ($maxExec === 0 ? T('نامحدود', 'Unlimited') : T('%s ثانیه', '%s seconds', array($maxExec))), T('حداقل %s — پیشنهادی %s', 'Minimum %s — recommended %s', array(30, 120)), $nt, 4,
    ($st === 'fail'), ($st === 'pass' ? '' : 'max_execution_time = 120'));

// upload_max_filesize
$upB  = nvd_bytes(nvd_ini('upload_max_filesize'));
$upMB = nvd_mb($upB);
if ($upMB >= $J6['upload_rec'])     { $st = 'pass'; $nt = T('برای نصب قالب‌ها و افزونه‌های حجیم کافی است.', 'Sufficient for installing large templates and extensions.'); }
elseif ($upMB >= $J6['upload_min']) { $st = 'warn'; $nt = T('بسته‌های بزرگ (مثل قالب‌های تجاری با دموی کامل) آپلود نمی‌شوند.', 'Large packages (such as commercial templates with full demo data) will not upload.'); }
else                                { $st = 'fail'; $nt = T('حتی بسته‌ی خود جوملا هم با این محدودیت آپلود نمی‌شود.', 'Even the Joomla package itself will not upload with this limit.'); }
if ($st !== 'pass') $iniFixes[] = 'upload_max_filesize = 64M';
$D[] = nvd_item('upload_max_filesize', $st, nvd_ini('upload_max_filesize'), T('%s یا بیشتر', '%s or more', array('64M')), $nt, 4,
    ($st === 'fail'), ($st === 'pass' ? '' : 'upload_max_filesize = 64M'));

// post_max_size
$postB  = nvd_bytes(nvd_ini('post_max_size'));
$postMB = nvd_mb($postB);
if ($postB > 0 && $postB < $upB)      { $st = 'fail'; $nt = T('post_max_size از upload_max_filesize کمتر است؛ آپلود فایل بزرگ بی‌صدا شکست می‌خورد.', 'post_max_size is smaller than upload_max_filesize; large file uploads fail silently.'); }
elseif ($postMB >= $J6['upload_rec']) { $st = 'pass'; $nt = T('حجم مجاز ارسال فرم متناسب با آپلود است.', 'The allowed form-submission size matches uploads.'); }
else                                  { $st = 'warn'; $nt = T('برای فرم‌های سنگین مدیریت (مثل تنظیم سطح دسترسی) کم است.', 'Too low for heavy admin forms (such as setting access permissions).'); }
if ($st !== 'pass') $iniFixes[] = 'post_max_size = 64M';
$D[] = nvd_item('post_max_size', $st, nvd_ini('post_max_size'), T('مساوی یا بیشتر از upload_max_filesize و حداقل %s', 'Equal to or greater than upload_max_filesize, minimum %s', array('64M')), $nt, 4,
    ($st === 'fail'), ($st === 'pass' ? '' : 'post_max_size = 64M'));

// max_input_vars
$miv = (int)nvd_ini('max_input_vars');
if ($miv === 0) $miv = 1000;
if ($miv >= $J6['inputvars_rec'])      { $st = 'pass'; $nt = T('فرم‌های بزرگ مدیریت جوملا کامل ذخیره می‌شوند.', 'Large Joomla admin forms save completely.'); }
elseif ($miv >= $J6['inputvars_min'])  { $st = 'warn'; $nt = T('خطرناک‌ترین تنظیم پنهان: در صفحه‌ی سطوح دسترسی یا منوهای بزرگ، بخشی از داده‌ها بدون هیچ پیام خطایی ذخیره نمی‌شود.', 'The most dangerous hidden setting: on access-level or large menu pages, part of the data silently fails to save with no error message.'); }
else                                   { $st = 'fail'; $nt = T('مقدار بسیار پایین است؛ ذخیره‌ی تنظیمات جوملا ناقص انجام می‌شود.', 'The value is far too low; saving Joomla settings will be incomplete.'); }
if ($st !== 'pass') $iniFixes[] = 'max_input_vars = 5000';
$D[] = nvd_item('max_input_vars', $st, $miv, T('%s یا بیشتر', '%s or more', array(5000)), $nt, 4, false,
    ($st === 'pass' ? '' : 'max_input_vars = 5000'));

// file_uploads
$fu = nvd_ini_on('file_uploads');
$D[] = nvd_item('file_uploads', $fu ? 'pass' : 'fail', nvd_yesno($fu), T('فعال (On)', 'Enabled (On)'),
    $fu ? T('آپلود فایل در مدیر رسانه و نصب افزونه ممکن است.', 'File upload in Media Manager and extension installs are possible.')
        : T('بدون این گزینه نه افزونه نصب می‌شود، نه تصویری آپلود می‌شود.', 'Without this option, neither extensions install nor images upload.'),
    5, true, $fu ? '' : 'file_uploads = On');
if (!$fu) $iniFixes[] = 'file_uploads = On';

// session.auto_start
$sas = nvd_ini_on('session.auto_start');
$D[] = nvd_item('session.auto_start', $sas ? 'fail' : 'pass', nvd_yesno($sas), T('غیرفعال (Off)', 'Disabled (Off)'),
    $sas ? T('شروع خودکار نشست با مدیریت نشست جوملا تداخل می‌کند و باعث خطای ورود می‌شود.', 'Auto-starting sessions conflicts with Joomla\'s session handling and causes login errors.')
         : T('مدیریت نشست کاملاً در اختیار جوملاست.', 'Session management is fully under Joomla\'s control.'),
    3, true, $sas ? 'session.auto_start = 0' : '');
if ($sas) $iniFixes[] = 'session.auto_start = 0';

// output_buffering
$ob = nvd_ini('output_buffering');
$obOn = ($ob !== '' && $ob !== '0' && strtolower($ob) !== 'off');
$D[] = nvd_item('output_buffering', $obOn ? 'warn' : 'pass', ($ob === '' ? 'Off' : $ob), T('غیرفعال (Off)', 'Disabled (Off)'),
    $obOn ? T('بافر خروجی می‌تواند در ریدایرکت‌ها و دانلود فایل از جوملا اختلال ایجاد کند.', 'Output buffering can interfere with Joomla redirects and file downloads.')
          : T('تنظیم مطابق توصیه‌ی جوملاست.', 'This setting matches Joomla\'s recommendation.'),
    2, false, $obOn ? 'output_buffering = Off' : '');
if ($obOn) $iniFixes[] = 'output_buffering = Off';

// display_errors
$de = nvd_ini_on('display_errors');
$D[] = nvd_item('display_errors', $de ? 'warn' : 'pass', nvd_yesno($de), T('غیرفعال روی سایت زنده', 'Disabled on a live site'),
    $de ? T('نمایش خطاها مسیر فایل‌ها و اطلاعات سرور را لو می‌دهد؛ روی سایت منتشرشده باید خاموش باشد.', 'Displaying errors leaks file paths and server info; it must be off on a published site.')
        : T('خطاها به بازدیدکننده نشان داده نمی‌شود.', 'Errors are not shown to visitors.'),
    2, false, $de ? 'display_errors = Off' : '');
if ($de) $iniFixes[] = 'display_errors = Off';

// allow_url_fopen
$aufo = nvd_ini_on('allow_url_fopen');
$hasCurl = nvd_ext('curl');
$D[] = nvd_item('allow_url_fopen', ($aufo || $hasCurl) ? 'pass' : 'fail', nvd_yesno($aufo), T('فعال یا وجود cURL', 'Enabled, or cURL present'),
    ($aufo || $hasCurl) ? T('جوملا برای به‌روزرسانی و دریافت داده از بیرون راه ارتباطی دارد.', 'Joomla has a way to fetch updates and external data.')
                        : T('نه allow_url_fopen فعال است نه cURL؛ به‌روزرسانی هسته و نصب افزونه از مخزن کار نمی‌کند.', 'Neither allow_url_fopen nor cURL is available; core updates and repository extension installs will not work.'),
    3, false, ($aufo || $hasCurl) ? '' : T('فعال‌سازی allow_url_fopen (یا فعال‌سازی cURL)', 'Enable allow_url_fopen (or enable cURL)'));

// date.timezone
$tz = nvd_ini('date.timezone');
$D[] = nvd_item('date.timezone', ($tz !== '') ? 'pass' : 'warn', ($tz !== '' ? $tz : T('تعیین نشده', 'Not set')), T('مثلاً %s', 'e.g. %s', array('Asia/Tehran')),
    ($tz !== '') ? T('منطقه‌ی زمانی سرور مشخص است.', 'The server timezone is set.')
                 : T('بدون تعیین منطقه‌ی زمانی، تاریخ انتشار مقالات و زمان‌بندی وظایف اشتباه ثبت می‌شود.', 'Without a timezone set, article publish dates and scheduled tasks are recorded incorrectly.'),
    1, false, ($tz !== '') ? '' : 'date.timezone = Asia/Tehran');
if ($tz === '') $iniFixes[] = 'date.timezone = Asia/Tehran';

// default_charset
$dc = strtolower(nvd_ini('default_charset'));
$D[] = nvd_item('default_charset', ($dc === 'utf-8') ? 'pass' : 'warn', ($dc !== '' ? $dc : T('تعیین نشده', 'Not set')), 'UTF-8',
    ($dc === 'utf-8') ? T('کدگذاری پیش‌فرض برای متن فارسی درست است.', 'The default encoding is correct for Persian text.')
                      : T('برای جلوگیری از به‌هم‌ریختگی متن فارسی، UTF-8 تنظیم شود.', 'Set UTF-8 to prevent Persian text from becoming garbled.'),
    1, false, ($dc === 'utf-8') ? '' : 'default_charset = "UTF-8"');

// expose_php
$ep = nvd_ini_on('expose_php');
$D[] = nvd_item('expose_php', $ep ? 'warn' : 'pass', nvd_yesno($ep), T('غیرفعال (امنیتی)', 'Disabled (security)'),
    $ep ? T('سرور نسخه‌ی دقیق PHP را در هدر پاسخ اعلام می‌کند و کار مهاجم را آسان‌تر می‌کند.', 'The server announces the exact PHP version in response headers, making an attacker\'s job easier.')
        : T('نسخه‌ی PHP در هدرها فاش نمی‌شود.', 'PHP version is not disclosed in headers.'),
    1, false, $ep ? 'expose_php = Off' : '');

// OPcache
$opOn = nvd_ini_on('opcache.enable') || (function_exists('opcache_get_status'));
$D[] = nvd_item('OPcache', $opOn ? 'pass' : 'warn', nvd_yesno($opOn), T('فعال (کارایی)', 'Enabled (performance)'),
    $opOn ? T('کد PHP کش می‌شود؛ سرعت بارگذاری صفحات جوملا به‌مراتب بهتر است.', 'PHP code is cached; Joomla page load speed is far better.')
          : T('بدون OPcache هر درخواست دوباره کل کد جوملا را کامپایل می‌کند؛ سایت کند می‌ماند.', 'Without OPcache, every request recompiles all of Joomla\'s code; the site stays slow.'),
    2, false, $opOn ? '' : T('فعال‌سازی OPcache در تنظیمات PHP هاست', 'Enable OPcache in your host\'s PHP settings'));

$sections[] = array(
    'id' => 'ini', 'title' => T('تنظیمات php.ini', 'php.ini Settings'),
    'desc' => T('این مقادیر در کنترل‌پنل هاست (cPanel → MultiPHP INI Editor یا فایل php.ini کاربر) قابل تغییرند.',
                'These values can be changed in your host\'s control panel (cPanel → MultiPHP INI Editor or your user php.ini file).'),
    'items' => $D
);

/* ===========================================================================
 |  بخش E — محدودیت‌های خاص هاست اشتراکی / Section E — Shared-hosting limits
 =========================================================================== */
$E = array();

// disable_functions
$disabledRaw  = nvd_ini('disable_functions');
$disabledList = array_filter(array_map('trim', explode(',', $disabledRaw)));
$watch = array(
    'ini_set'        => array('fail', T('جوملا برای تنظیم زمان اجرا و حافظه در حین به‌روزرسانی به آن نیاز دارد.', 'Joomla needs this to set execution time and memory during updates.')),
    'set_time_limit' => array('fail', T('بدون آن، به‌روزرسانی هسته و ایمپورت‌های طولانی نیمه‌کاره متوقف می‌شود.', 'Without it, core updates and long imports stop half-finished.')),
    'error_reporting'=> array('warn', T('مدیریت سطح خطا در جوملا محدود می‌شود.', 'Joomla\'s error-level management becomes limited.')),
    'getenv'         => array('warn', T('برخی افزونه‌ها برای خواندن متغیرهای محیطی به آن نیاز دارند.', 'Some extensions need it to read environment variables.')),
    'fopen'          => array('fail', T('خواندن و نوشتن فایل غیرممکن می‌شود.', 'Reading and writing files becomes impossible.')),
    'file_get_contents' => array('warn', T('دریافت فایل و به‌روزرسانی از راه دور مختل می‌شود.', 'Remote file fetching and updates are disrupted.')),
    'symlink'        => array('warn', T('برخی ابزارهای پشتیبان‌گیری از آن استفاده می‌کنند.', 'Some backup tools rely on it.')),
    'proc_open'      => array('warn', T('ابزارهای پشتیبان‌گیری و CLI به آن نیاز دارند.', 'Backup tools and CLI utilities need it.')),
    'exec'           => array('warn', T('برای اجرای کرون داخلی و برخی ابزارهای بهینه‌سازی تصویر لازم است.', 'Needed for internal cron execution and some image-optimization tools.')),
);
$blocked = array();
$blockedSeverity = 'pass';
foreach ($watch as $fn => $meta) {
    if (in_array($fn, $disabledList, true)) {
        $blocked[$fn] = $meta;
        if ($meta[0] === 'fail') $blockedSeverity = 'fail';
        elseif ($blockedSeverity !== 'fail') $blockedSeverity = 'warn';
    }
}
$E[] = nvd_item(
    T('توابع غیرفعال‌شده (disable_functions)', 'Disabled Functions (disable_functions)'),
    $blockedSeverity,
    $disabledRaw !== '' ? $disabledRaw : T('هیچ تابعی غیرفعال نیست', 'No function is disabled'),
    T('ini_set و set_time_limit نباید غیرفعال باشند', 'ini_set and set_time_limit must not be disabled'),
    empty($blocked)
        ? T('هیچ‌کدام از توابع حیاتی جوملا مسدود نشده است.', 'None of Joomla\'s critical functions are blocked.')
        : T('این توابعِ موردنیاز جوملا مسدود شده‌اند: %s — از پشتیبانی هاست بخواهید حداقل ini_set و set_time_limit را آزاد کند.',
            'These functions Joomla needs are blocked: %s — ask your host\'s support to unblock at least ini_set and set_time_limit.',
            array(implode(' , ', array_keys($blocked)))),
    3, false,
    empty($blocked) ? '' : T('آزادسازی توابع: %s', 'Unblock functions: %s', array(implode(', ', array_keys($blocked))))
);

// open_basedir
$obd = nvd_ini('open_basedir');
$E[] = nvd_item('open_basedir', ($obd === '') ? 'pass' : 'warn', ($obd !== '' ? $obd : T('محدودیتی ندارد', 'No restriction')),
    T('بدون محدودیت یا شامل مسیر سایت و پوشه‌ی موقت', 'Unrestricted, or including the site path and temp folder'),
    ($obd === '') ? T('دسترسی فایل‌سیستم محدود نشده است.', 'Filesystem access is not restricted.')
                  : T('اگر پوشه‌ی موقت سیستم و مسیر سایت در این لیست نباشند، آپلود فایل و باز کردن بسته‌ها شکست می‌خورد.',
                      'If the system temp folder and site path are not in this list, file uploads and package extraction will fail.'),
    2, false, ($obd === '') ? '' : T('افزودن مسیر سایت و /tmp به open_basedir', 'Add the site path and /tmp to open_basedir'));

// Suhosin
$E[] = nvd_item(T('افزونه Suhosin', 'Suhosin Extension'), nvd_ext('suhosin') ? 'warn' : 'pass',
    nvd_ext('suhosin') ? T('فعال', 'Enabled') : T('غیرفعال', 'Disabled'), T('غیرفعال یا با محدودیت‌های بالا', 'Disabled, or with high limits'),
    nvd_ext('suhosin') ? T('Suhosin می‌تواند تعداد متغیرهای فرم را محدود کند و ذخیره‌ی تنظیمات جوملا را ناقص کند.', 'Suhosin can limit the number of form fields and cause incomplete saving of Joomla settings.')
                       : T('محدودیت اضافه‌ای روی فرم‌ها اعمال نمی‌شود.', 'No extra restriction is applied to forms.'),
    1);

// ionCube / Zend Guard
$hasLoader = nvd_ext('ionCube Loader') || nvd_ext('Zend Guard Loader') || nvd_ext('SourceGuardian');
$E[] = nvd_item(T('لودر افزونه‌های تجاری (ionCube / SourceGuardian)', 'Commercial Extension Loader (ionCube / SourceGuardian)'), 'info',
    $hasLoader ? T('نصب است', 'Installed') : T('نصب نیست', 'Not installed'), T('اختیاری', 'Optional'),
    $hasLoader ? T('امکان اجرای افزونه‌های تجاری رمزگذاری‌شده وجود دارد.', 'Encrypted commercial extensions can run.')
               : T('اگر قصد استفاده از افزونه‌ی تجاری رمزگذاری‌شده دارید، نصب لودر را از هاست بخواهید.', 'If you plan to use an encrypted commercial extension, ask your host to install the loader.'),
    0);

// PHP CLI برای کرون / PHP CLI for cron
// این بررسی واقعاً exec() را فقط در حالت «تست‌های عمیق» اجرا می‌کند تا ابزارهای امنیتی هاست
// با اجرای بی‌مورد یک فرمان shell در هر بارگذاری صفحه هشدار ندهند.
// This check only actually runs exec() in "Deep Tests" mode, so host security tools don't
// flag an unnecessary shell command on every single page load.
$cliVersion = '';
$cliChecked = false;
if ($deep && nvd_func('exec')) {
    $cliChecked = true;
    $out = array();
    @exec('php -v 2>&1', $out);
    if (!empty($out[0]) && preg_match('/PHP\s+([\d\.]+)/i', $out[0], $m)) $cliVersion = $m[1];
}
$E[] = nvd_item(T('PHP خط فرمان (برای کرون‌جاب)', 'PHP Command Line (for cron jobs)'),
    ($cliVersion !== '') ? (version_compare($cliVersion, $J6['php_min'], '>=') ? 'pass' : 'warn') : 'info',
    ($cliVersion !== '' ? 'PHP ' . $cliVersion : ($cliChecked ? T('قابل تشخیص نیست', 'Cannot be detected') : T('برای بررسی، «تست‌های عمیق» را اجرا کنید', 'Run "Deep Tests" to check'))),
    T('هم‌نسخه با PHP وب', 'Same version as web PHP'),
    ($cliVersion !== '')
        ? (version_compare($cliVersion, $J6['php_min'], '>=')
            ? T('کرون‌جاب سیستمی برای زمان‌بند وظایف جوملا قابل استفاده است.', 'A system cron job can be used for Joomla\'s task scheduler.')
            : T('نسخه‌ی CLI از نسخه‌ی وب قدیمی‌تر است؛ در کرون‌جاب حتماً مسیر کامل باینری PHP صحیح را بنویسید.', 'The CLI version is older than the web version; be sure to use the correct full PHP binary path in the cron job.'))
        : ($cliChecked
            ? T('اجرای دستور روی این هاست مجاز نیست. اگر کرون‌جاب ندارید، از زمان‌بند «Lazy Scheduler» یا «Web Cron» داخل خود جوملا ۶ استفاده کنید.',
                'Running commands is not allowed on this host. If you have no cron job, use Joomla 6\'s built-in "Lazy Scheduler" or "Web Cron".')
            : T('برای جلوگیری از اجرای بی‌مورد exec() روی سرور شما، این بررسی فقط در حالت «تست‌های عمیق» انجام می‌شود.',
                'To avoid running exec() on your server unnecessarily, this check only runs in "Deep Tests" mode.')),
    1);

// mail()
$E[] = nvd_item(T('تابع mail()', 'mail() Function'), nvd_func('mail') ? 'pass' : 'warn',
    nvd_func('mail') ? T('در دسترس', 'Available') : T('غیرفعال', 'Disabled'), T('در دسترس یا استفاده از SMTP', 'Available, or use SMTP'),
    nvd_func('mail') ? T('ارسال ایمیل با تابع داخلی ممکن است، اما برای تحویل بهتر، SMTP اختصاصی توصیه می‌شود.', 'Sending email with the built-in function is possible, but a dedicated SMTP is recommended for better deliverability.')
                     : T('ایمیل‌های جوملا (ثبت‌نام، بازیابی رمز) ارسال نمی‌شود؛ در تنظیمات جوملا SMTP را فعال کنید.', 'Joomla emails (registration, password recovery) will not send; enable SMTP in Joomla\'s global configuration.'),
    2, false, nvd_func('mail') ? '' : T('پیکربندی SMTP در تنظیمات کلی جوملا', 'Configure SMTP in Joomla\'s global configuration'));

$sections[] = array(
    'id' => 'limits', 'title' => T('محدودیت‌های هاست اشتراکی', 'Shared-Hosting Limits'),
    'desc' => T('همان تنظیماتی که میزبان‌ها برای امنیت اعمال می‌کنند و اغلب باعث خطاهای مبهم جوملا می‌شوند.',
                'The very settings hosts apply for security, which often cause confusing Joomla errors.'),
    'items' => $E
);

/* ===========================================================================
 |  بخش F — فایل‌سیستم و مجوزها / Section F — Filesystem & permissions
 =========================================================================== */
$F = array();
$here = __DIR__;

// نوشتن فایل / File write
$testFile = $here . '/nvd_write_test_' . mt_rand(1000, 9999) . '.tmp';
$canWriteFile = @file_put_contents($testFile, 'ok') !== false;
$fileOwner = $canWriteFile ? @fileowner($testFile) : null;
$filePerm  = $canWriteFile ? substr(sprintf('%o', @fileperms($testFile)), -4) : '';
if ($canWriteFile) @unlink($testFile);

$F[] = nvd_item(T('امکان ایجاد فایل در مسیر سایت', 'Ability to Create Files in the Site Path'), $canWriteFile ? 'pass' : 'fail',
    $canWriteFile ? T('موفق', 'Successful') : T('ناموفق', 'Failed'), T('قابل نوشتن', 'Writable'),
    $canWriteFile ? T('جوملا می‌تواند فایل configuration.php را بسازد.', 'Joomla can create the configuration.php file.')
                  : T('بدون اجازه‌ی نوشتن، نصب‌کننده در گام آخر شکست می‌خورد. مجوز پوشه را روی 755 و مالکیت را روی کاربر هاست تنظیم کنید.',
                      'Without write permission, the installer fails at the last step. Set folder permissions to 755 and ownership to the host user.'),
    6, true, $canWriteFile ? '' : T('اصلاح مجوز و مالکیت پوشه‌ی سایت', 'Fix the site folder\'s permissions and ownership'));

// ساخت پوشه / Folder creation
$testDir = $here . '/nvd_dir_test_' . mt_rand(1000, 9999);
$canMkdir = @mkdir($testDir, 0755);
if ($canMkdir) @rmdir($testDir);
$F[] = nvd_item(T('امکان ایجاد پوشه', 'Ability to Create Folders'), $canMkdir ? 'pass' : 'fail',
    $canMkdir ? T('موفق', 'Successful') : T('ناموفق', 'Failed'), T('مجاز', 'Allowed'),
    $canMkdir ? T('ساخت پوشه‌های tmp، logs، images و cache ممکن است.', 'The tmp, logs, images and cache folders can be created.')
              : T('جوملا نمی‌تواند پوشه‌های موردنیازش را بسازد؛ نصب ناقص می‌ماند.', 'Joomla cannot create the folders it needs; the install remains incomplete.'),
    5, true, $canMkdir ? '' : T('اصلاح مجوز پوشه‌ی سایت', 'Fix the site folder\'s permissions'));

// مالکیت / Ownership
$dirOwner = @fileowner($here);
$ownerMatch = ($fileOwner !== null && $dirOwner !== false && $fileOwner === $dirOwner);
$F[] = nvd_item(T('تطابق مالک فایل‌های ساخته‌شده', 'Ownership Match of Created Files'), $ownerMatch ? 'pass' : ($canWriteFile ? 'warn' : 'info'),
    $canWriteFile ? T('UID فایل: %s | UID پوشه: %s | مجوز: %s', 'File UID: %s | Folder UID: %s | Permissions: %s', array($fileOwner, $dirOwner, $filePerm)) : T('قابل بررسی نیست', 'Cannot be checked'),
    T('یکسان بودن مالک فایل و پوشه', 'File and folder owner should match'),
    $ownerMatch ? T('فایل‌هایی که جوملا می‌سازد با همان کاربر هاست ساخته می‌شوند — بدون مشکل مجوز در آینده.', 'Files Joomla creates are owned by the same hosting user — no permission issues down the line.')
                : T('مالک فایل ساخته‌شده با مالک پوشه فرق دارد (حالت mod_php/nobody). بعداً برای حذف یا ویرایش فایل‌ها به مشکل می‌خورید؛ از هاست بخواهید PHP را روی FPM/suEXEC اجرا کند.',
                    'The created file\'s owner differs from the folder\'s owner (mod_php/nobody mode). You will run into trouble deleting or editing files later; ask your host to run PHP under FPM/suEXEC.'),
    2);

// مسیر نشست / Session save path
$sessPath = session_save_path();
if ($sessPath === '') $sessPath = sys_get_temp_dir();
$sessOk = ($sessPath !== '' && @is_writable($sessPath));
$F[] = nvd_item(T('مسیر ذخیره‌ی نشست', 'Session Save Path'), $sessOk ? 'pass' : 'warn',
    ($sessPath !== '' ? $sessPath : T('نامشخص', 'Unknown')) . ($sessOk ? T(' (قابل نوشتن)', ' (writable)') : T(' (غیرقابل نوشتن)', ' (not writable)')),
    T('قابل نوشتن', 'Writable'),
    $sessOk ? T('ورود کاربران و پنل مدیریت بدون مشکل کار می‌کند.', 'User login and the admin panel work without issue.')
            : T('اگر مسیر نشست قابل نوشتن نباشد، بعد از ورود به پنل مدیریت بلافاصله خارج می‌شوید. راه‌حل: در تنظیمات جوملا نوع نشست را روی Database بگذارید.',
                'If the session path is not writable, you get logged out immediately after entering the admin panel. Fix: set the session handler to Database in Joomla settings.'),
    3, false, $sessOk ? '' : T('اصلاح مجوز مسیر session یا تغییر Session Handler به Database', 'Fix the session path permissions or change the Session Handler to Database'));

// پوشه موقت آپلود / Upload temp folder
$tmpDir = nvd_ini('upload_tmp_dir');
if ($tmpDir === '') $tmpDir = sys_get_temp_dir();
$tmpOk = @is_writable($tmpDir);
$F[] = nvd_item(T('پوشه‌ی موقت آپلود', 'Upload Temp Folder'), $tmpOk ? 'pass' : 'warn',
    $tmpDir . ($tmpOk ? T(' (قابل نوشتن)', ' (writable)') : T(' (غیرقابل نوشتن)', ' (not writable)')), T('قابل نوشتن', 'Writable'),
    $tmpOk ? T('فایل‌های آپلودی به‌درستی دریافت می‌شوند.', 'Uploaded files are received correctly.')
           : T('آپلود فایل و نصب افزونه شکست می‌خورد؛ در تنظیمات جوملا یک مسیر موقت داخل سایت تعریف کنید.', 'File upload and extension installation will fail; define a temp path inside the site in Joomla settings.'),
    3, false, $tmpOk ? '' : T('تعریف upload_tmp_dir قابل نوشتن', 'Define a writable upload_tmp_dir'));

// فضای دیسک / Disk space
$freeB = function_exists('disk_free_space') ? @disk_free_space($here) : false;
if ($freeB !== false && $freeB !== null) {
    $freeMB = nvd_mb($freeB);
    if ($freeMB >= $J6['disk_rec'])      { $st = 'pass'; $nt = T('فضا برای جوملا، رسانه‌ها و نسخه‌ی پشتیبان کافی است.', 'Space is sufficient for Joomla, media and a backup copy.'); }
    elseif ($freeMB >= $J6['disk_min'])  { $st = 'warn'; $nt = T('برای نصب کافی است، اما جای کافی برای پشتیبان‌گیری و رشد سایت ندارید.', 'Enough for installation, but not enough room for backups and site growth.'); }
    else                                 { $st = 'fail'; $nt = T('فضای آزاد کمتر از حداقل موردنیاز نصب جوملاست.', 'Free space is below Joomla\'s minimum installation requirement.'); }
    $F[] = nvd_item(T('فضای آزاد دیسک', 'Free Disk Space'), $st, nvd_hsize($freeB), T('حداقل %s — پیشنهادی %s', 'Minimum %s — recommended %s', array('300MB', '1GB')),
        $nt, 3, ($st === 'fail'), ($st === 'pass' ? '' : T('ارتقای فضای هاست', 'Upgrade hosting disk space')));
}

// تعداد فایل (inode) / File count (inode)
$F[] = nvd_item(T('تعداد فایل مجاز (inode)', 'Allowed File Count (inode)'), 'info', T('قابل اندازه‌گیری از داخل PHP نیست', 'Cannot be measured from within PHP'),
    T('حداقل ۲۵٬۰۰۰ inode آزاد', 'At least 25,000 free inodes'),
    T('جوملا ۶ به‌تنهایی حدود ۷٬۰۰۰ فایل دارد و با چند افزونه و کش به‌راحتی از ۲۰٬۰۰۰ عبور می‌کند. سقف inode پلن هاست را از پشتیبانی بپرسید.',
        'Joomla 6 alone has about 7,000 files, and with a few extensions and cache it easily passes 20,000. Ask your host\'s support about your plan\'s inode cap.'),
    0);

// فایل‌های موجود در مسیر / Existing files in the path
$hasIndex  = @is_file($here . '/index.php');
$hasConfig = @is_file($here . '/configuration.php');
if ($hasIndex || $hasConfig) {
    $F[] = nvd_item(T('محتوای فعلی پوشه‌ی نصب', 'Current Contents of the Install Folder'), 'warn',
        ($hasConfig ? T('configuration.php موجود است', 'configuration.php exists') : T('index.php موجود است', 'index.php exists')),
        T('پوشه‌ی خالی برای نصب تازه', 'An empty folder for a fresh install'),
        T('در این مسیر از قبل یک سایت وجود دارد. نصب تازه‌ی جوملا روی آن، فایل‌های موجود را خراب می‌کند. یا پوشه را خالی کنید یا در زیرپوشه‌ی جداگانه نصب کنید.',
            'A site already exists at this path. A fresh Joomla install here will damage the existing files. Either empty the folder or install in a separate subfolder.'),
        1);
}

// HTTPS
$F[] = nvd_item(T('اتصال HTTPS', 'HTTPS Connection'), $isHttps ? 'pass' : 'warn',
    $isHttps ? T('فعال', 'Enabled') : T('غیرفعال (HTTP)', 'Disabled (HTTP)'), T('گواهی SSL معتبر', 'A valid SSL certificate'),
    $isHttps ? T('ارتباط رمزگذاری‌شده است؛ ورود به پنل مدیریت امن انجام می‌شود.', 'The connection is encrypted; admin panel logins are done securely.')
             : T('بدون SSL، رمز عبور مدیر به‌صورت متن ساده منتقل می‌شود و گوگل سایت را «ناامن» علامت می‌زند. گواهی رایگان Let\'s Encrypt را از کنترل‌پنل فعال کنید.',
                 'Without SSL, the admin password travels as plain text and Google marks the site as "Not Secure". Enable a free Let\'s Encrypt certificate from your control panel.'),
    3, false, $isHttps ? '' : T('فعال‌سازی گواهی SSL روی دامنه', 'Enable an SSL certificate on the domain'));

$sections[] = array(
    'id' => 'fs', 'title' => T('فایل‌سیستم، مجوزها و فضا', 'Filesystem, Permissions & Space'),
    'desc' => T('بیشترین خطاهای «نصب ناموفق» جوملا از همین‌جا می‌آید: مجوز نوشتن، مالکیت فایل و مسیر نشست.',
                'Most "installation failed" Joomla errors come from right here: write permission, file ownership and the session path.'),
    'items' => $F
);

/* ===========================================================================
 |  بخش G — تست‌های عمیق (اختیاری، با کلیک کاربر) / Section G — Deep tests (opt-in)
 =========================================================================== */
$G = array();

function nvd_http_head($url, $timeout = 5) {
    if (function_exists('curl_init')) {
        $ch = @curl_init($url);
        if ($ch) {
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_NOBODY, true);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            @curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            @curl_setopt($ch, CURLOPT_USERAGENT, 'NavidIranian-J6-Checker/2.0');
            @curl_exec($ch);
            $code = (int)@curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = @curl_error($ch);
            @curl_close($ch);
            return array('code' => $code, 'error' => $err);
        }
    }
    if (nvd_ini_on('allow_url_fopen')) {
        $ctx = @stream_context_create(array('http' => array('timeout' => $timeout, 'method' => 'HEAD')));
        $h = @get_headers($url, 0, $ctx);
        if ($h && isset($h[0]) && preg_match('/\s(\d{3})\s/', $h[0], $m)) {
            return array('code' => (int)$m[1], 'error' => '');
        }
    }
    return array('code' => 0, 'error' => T('ابزار ارتباط شبکه در دسترس نیست', 'No network communication tool available'));
}

function nvd_rmdir_all($dir) {
    if (!@is_dir($dir)) return;
    $items = @scandir($dir);
    if ($items) {
        foreach ($items as $it) {
            if ($it === '.' || $it === '..') continue;
            $p = $dir . '/' . $it;
            if (@is_dir($p)) nvd_rmdir_all($p); else @unlink($p);
        }
    }
    @rmdir($dir);
}

if ($deep) {
    // G1..G3 — دسترسی به سرورهای جوملا / Access to Joomla's servers
    $targets = array(
        T('به‌روزرسانی هسته (update.joomla.org)', 'Core Updates (update.joomla.org)')   => 'https://update.joomla.org/core/list.xml',
        T('دانلود بسته‌ها (downloads.joomla.org)', 'Package Downloads (downloads.joomla.org)')  => 'https://downloads.joomla.org/',
        T('مخزن افزونه‌ها (extensions.joomla.org)', 'Extensions Directory (extensions.joomla.org)') => 'https://extensions.joomla.org/',
    );
    foreach ($targets as $label => $url) {
        $r  = nvd_http_head($url);
        $ok = ($r['code'] >= 200 && $r['code'] < 400);
        $G[] = nvd_item(T('دسترسی به %s', 'Access to %s', array($label)), $ok ? 'pass' : 'fail',
            $ok ? T('پاسخ %s', 'Response %s', array($r['code'])) : (T('ناموفق', 'Failed') . ($r['error'] ? ' — ' . $r['error'] : '')),
            T('پاسخ 200', 'Response 200'),
            $ok ? T('ارتباط خروجی سرور با این سرویس برقرار است.', 'The server has outbound connectivity to this service.')
                : T('سرور به این آدرس دسترسی ندارد (فیلترینگ خروجی، فایروال یا نبود DNS). نتیجه: به‌روزرسانی خودکار و نصب افزونه از داخل پنل کار نمی‌کند و باید بسته‌ها را دستی آپلود کنید.',
                    'The server cannot reach this address (outbound filtering, firewall, or missing DNS). Result: automatic updates and installing extensions from the panel will not work, and packages must be uploaded manually.'),
            3, false, $ok ? '' : T('باز کردن دسترسی خروجی HTTPS به دامنه‌های joomla.org', 'Open outbound HTTPS access to joomla.org domains'));
    }

    // G4 — تست واقعی mod_rewrite / htaccess / Real mod_rewrite / .htaccess test
    $probeDir = $here . '/_nvd_probe_' . mt_rand(10000, 99999);
    $rwStatus = 'info'; $rwActual = T('قابل انجام نیست', 'Cannot be performed'); $rwNote = '';
    if (@mkdir($probeDir, 0755)) {
        @file_put_contents($probeDir . '/ok.txt', 'NVD_REWRITE_OK');
        $ht = "Options +FollowSymLinks\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteRule ^probe\\.txt$ ok.txt [L]\n</IfModule>\n";
        @file_put_contents($probeDir . '/.htaccess', $ht);
        $base  = ($isHttps ? 'https://' : 'http://') . $hostName;
        $path  = isset($_SERVER['SCRIPT_NAME']) ? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') : '';
        $probeUrl = $base . $path . '/' . basename($probeDir) . '/probe.txt';
        $body = '';
        if (function_exists('curl_init')) {
            $ch = @curl_init($probeUrl);
            @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            @curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $body = (string)@curl_exec($ch);
            @curl_close($ch);
        } elseif (nvd_ini_on('allow_url_fopen')) {
            $ctx  = @stream_context_create(array('http' => array('timeout' => 8)));
            $body = (string)@file_get_contents($probeUrl, false, $ctx);
        }
        if (strpos($body, 'NVD_REWRITE_OK') !== false) {
            $rwStatus = 'pass'; $rwActual = T('فعال و آزمایش‌شده', 'Enabled and tested');
            $rwNote   = T('فایل htaccess خوانده می‌شود و mod_rewrite کار می‌کند؛ URLهای سئوپسند جوملا بدون index.php فعال خواهند شد.',
                           'The .htaccess file is read and mod_rewrite works; Joomla\'s SEF URLs without index.php will function.');
        } elseif ($body !== '') {
            $rwStatus = 'fail'; $rwActual = T('بازنویسی آدرس انجام نشد', 'URL rewriting did not occur');
            $rwNote   = T('درخواست پاسخ گرفت اما قانون بازنویسی اجرا نشد. یعنی یا mod_rewrite خاموش است یا AllowOverride اجازه‌ی htaccess نمی‌دهد. بدون آن گزینه‌ی «حذف index.php از آدرس» در جوملا خطای 404 می‌دهد.',
                           'The request got a response but the rewrite rule did not run. That means either mod_rewrite is off or AllowOverride does not permit .htaccess. Without it, Joomla\'s "Remove index.php from URL" option gives a 404 error.');
        } else {
            $rwStatus = 'warn'; $rwActual = T('تست ناتمام ماند', 'Test remained incomplete');
            $rwNote   = T('سرور نتوانست به خودش درخواست بزند (معمولاً بسته بودن ارتباط خروجی). این تست را می‌توانید دستی انجام دهید: htaccess.txt جوملا را به .htaccess تغییر نام دهید و SEF را فعال کنید.',
                           'The server could not request itself (usually blocked outbound connectivity). You can perform this test manually: rename Joomla\'s htaccess.txt to .htaccess and enable SEF.');
        }
        nvd_rmdir_all($probeDir);
    } else {
        $rwNote = T('امکان ساخت پوشه‌ی موقت برای تست وجود نداشت.', 'A temporary folder for testing could not be created.');
    }
    $G[] = nvd_item(T('mod_rewrite و اجرای فایل .htaccess', 'mod_rewrite and .htaccess Execution'), $rwStatus, $rwActual, T('فعال برای URLهای سئوپسند', 'Enabled for SEF URLs'),
        $rwNote, 3, false, ($rwStatus === 'pass' ? '' : T('فعال‌سازی mod_rewrite و AllowOverride All', 'Enable mod_rewrite and AllowOverride All')));

    // G5 — DNS
    $dnsOk = function_exists('gethostbyname') ? (gethostbyname('downloads.joomla.org') !== 'downloads.joomla.org') : false;
    $G[] = nvd_item(T('تفکیک نام دامنه (DNS)', 'Domain Name Resolution (DNS)'), $dnsOk ? 'pass' : 'warn',
        $dnsOk ? T('موفق', 'Successful') : T('ناموفق یا مسدود', 'Failed or blocked'), T('قابلیت resolve دامنه‌های بیرونی', 'Ability to resolve external domains'),
        $dnsOk ? T('سرور می‌تواند نام دامنه‌های بیرونی را ترجمه کند.', 'The server can resolve external domain names.')
               : T('سرور نمی‌تواند دامنه‌های بیرونی را ترجمه کند؛ هر قابلیتی که به اینترنت وابسته است (به‌روزرسانی، کپچا، نقشه) از کار می‌افتد.',
                   'The server cannot resolve external domains; any feature relying on the internet (updates, CAPTCHA, maps) stops working.'),
        2);

    $sections[] = array(
        'id' => 'net', 'title' => T('تست‌های عمیق: شبکه و بازنویسی آدرس', 'Deep Tests: Network & URL Rewriting'),
        'desc' => T('این بخش با اجرای درخواست واقعی سنجیده شد — نه با حدس زدن از روی تنظیمات.',
                    'This section was measured with a real request — not guessed from settings.'),
        'items' => $G
    );
}

/* ===========================================================================
 |  بخش H — تست اتصال دیتابیس (فرم اختیاری) / Section H — Database connection test
 =========================================================================== */
$dbResult = null;
$dbCsrfOk = hash_equals($NVD_CSRF, isset($_POST['nvd_csrf']) ? (string)$_POST['nvd_csrf'] : '');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nvd_db_test']) && $dbCsrfOk) {
    $dbHost = trim(nvd_get($_POST, 'db_host', 'localhost'));
    $dbUser = trim(nvd_get($_POST, 'db_user', ''));
    $dbPass = (string)nvd_get($_POST, 'db_pass', '');
    $dbName = trim(nvd_get($_POST, 'db_name', ''));
    $dbPort = (int)nvd_get($_POST, 'db_port', 3306);
    if ($dbPort <= 0) $dbPort = 3306;

    $rows = array();
    $connOk = false; $srvVer = ''; $isMaria = false;

    if (class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers(), true)) {
        try {
            $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ($dbName !== '' ? ';dbname=' . $dbName : '');
            $pdo = new PDO($dsn, $dbUser, $dbPass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 6));
            $connOk = true;
            $srvVer  = (string)$pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            $isMaria = (stripos($srvVer, 'mariadb') !== false);
            $cleanVer = preg_replace('/[^0-9\.].*$/', '', $srvVer);

            $rows[] = nvd_item(T('اتصال به دیتابیس', 'Database Connection'), 'pass', T('برقرار شد', 'Established'), T('اتصال موفق', 'Successful connection'),
                T('نام کاربری، رمز و نام دیتابیس درست است — همین مقادیر را در نصب‌کننده‌ی جوملا وارد کنید.', 'Username, password and database name are correct — use these same values in the Joomla installer.'), 5, true);

            if ($isMaria) {
                $ok  = version_compare($cleanVer, $J6['mariadb_min'], '>=');
                $rec = version_compare($cleanVer, $J6['mariadb_rec'], '>=');
                $rows[] = nvd_item(T('نسخه MariaDB', 'MariaDB Version'), $ok ? ($rec ? 'pass' : 'warn') : 'fail', $srvVer,
                    T('حداقل %s — پیشنهادی %s', 'Minimum %s — recommended %s', array($J6['mariadb_min'], $J6['mariadb_rec'])),
                    $ok ? T('نسخه‌ی دیتابیس برای جوملا ۶ مجاز است.', 'The database version is acceptable for Joomla 6.') : T('جوملا ۶ روی این نسخه نصب نمی‌شود.', 'Joomla 6 will not install on this version.'), 5, !$ok);
            } else {
                $ok  = version_compare($cleanVer, $J6['mysql_min'], '>=');
                $rec = version_compare($cleanVer, $J6['mysql_rec'], '>=');
                $rows[] = nvd_item(T('نسخه MySQL', 'MySQL Version'), $ok ? ($rec ? 'pass' : 'warn') : 'fail', $srvVer,
                    T('حداقل %s — پیشنهادی %s', 'Minimum %s — recommended %s', array($J6['mysql_min'], $J6['mysql_rec'])),
                    $ok ? T('نسخه‌ی دیتابیس برای جوملا ۶ مجاز است.', 'The database version is acceptable for Joomla 6.') : T('جوملا ۶ روی این نسخه نصب نمی‌شود.', 'Joomla 6 will not install on this version.'), 5, !$ok);
            }

            // utf8mb4
            $cs = $pdo->query("SHOW CHARACTER SET LIKE 'utf8mb4'")->fetchAll();
            $rows[] = nvd_item(T('پشتیبانی utf8mb4', 'utf8mb4 Support'), !empty($cs) ? 'pass' : 'fail',
                !empty($cs) ? T('پشتیبانی می‌شود', 'Supported') : T('پشتیبانی نمی‌شود', 'Not supported'), 'utf8mb4',
                !empty($cs) ? T('متن فارسی، عربی و ایموجی بدون مشکل ذخیره می‌شود.', 'Persian, Arabic and emoji text saves without issue.')
                            : T('بدون utf8mb4 ذخیره‌ی برخی کاراکترها با خطا مواجه می‌شود.', 'Without utf8mb4, saving certain characters will error out.'), 3);

            // آزمون ساخت جدول / Table creation test
            if ($dbName !== '') {
                $tbl = 'nvd_test_' . mt_rand(1000, 9999);
                $canCreate = false;
                try {
                    $pdo->exec("CREATE TABLE `$tbl` (id INT PRIMARY KEY AUTO_INCREMENT, t VARCHAR(20)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                    $pdo->exec("INSERT INTO `$tbl` (t) VALUES ('ok')");
                    $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN t2 VARCHAR(10) NULL");
                    $pdo->exec("DROP TABLE `$tbl`");
                    $canCreate = true;
                } catch (Exception $ex) {
                    @$pdo->exec("DROP TABLE IF EXISTS `$tbl`");
                }
                $rows[] = nvd_item(T('سطح دسترسی کاربر دیتابیس', 'Database User Privilege Level'), $canCreate ? 'pass' : 'fail',
                    $canCreate ? T('CREATE / INSERT / ALTER / DROP مجاز است', 'CREATE / INSERT / ALTER / DROP are allowed') : T('ناکافی', 'Insufficient'),
                    T('دسترسی کامل روی همین دیتابیس', 'Full access on this database'),
                    $canCreate ? T('کاربر دیتابیس همه‌ی مجوزهای موردنیاز نصب جوملا را دارد.', 'The database user has all the privileges Joomla\'s installer needs.')
                               : T('کاربر اجازه‌ی ساخت جدول ندارد؛ در کنترل‌پنل گزینه‌ی ALL PRIVILEGES را برای این کاربر فعال کنید.', 'The user is not allowed to create tables; enable ALL PRIVILEGES for this user in the control panel.'), 5, !$canCreate);
            }

            // InnoDB
            $eng = $pdo->query("SHOW ENGINES")->fetchAll(PDO::FETCH_ASSOC);
            $innodb = false;
            foreach ($eng as $e) {
                $en = isset($e['Engine']) ? strtolower($e['Engine']) : '';
                $su = isset($e['Support']) ? strtoupper($e['Support']) : '';
                if ($en === 'innodb' && ($su === 'YES' || $su === 'DEFAULT')) $innodb = true;
            }
            $rows[] = nvd_item(T('موتور InnoDB', 'InnoDB Engine'), $innodb ? 'pass' : 'fail',
                $innodb ? T('فعال', 'Enabled') : T('غیرفعال', 'Disabled'), T('فعال', 'Enabled'),
                $innodb ? T('جداول جوملا با پشتیبانی از تراکنش و کلید خارجی ساخته می‌شوند.', 'Joomla tables are created with transaction and foreign-key support.')
                        : T('جوملا ۶ به InnoDB نیاز دارد؛ از هاست بخواهید آن را فعال کند.', 'Joomla 6 requires InnoDB; ask your host to enable it.'), 4, !$innodb);

        } catch (Exception $ex) {
            $rows[] = nvd_item(T('اتصال به دیتابیس', 'Database Connection'), 'fail', T('ناموفق', 'Failed'), T('اتصال موفق', 'Successful connection'),
                T('پیام سرور: %s — معمولاً یعنی نام کاربری/رمز اشتباه است، یا نام دیتابیس با پیشوند حساب هاست وارد نشده (مثلاً user_dbname).',
                    'Server message: %s — this usually means the username/password is wrong, or the database name is missing the hosting account\'s prefix (e.g. user_dbname).',
                    array($ex->getMessage())), 5, true);
        }
    } else {
        $rows[] = nvd_item(T('درایور PDO MySQL', 'PDO MySQL Driver'), 'fail', T('در دسترس نیست', 'Not available'), 'pdo_mysql',
            T('برای تست اتصال، افزونه‌ی pdo_mysql باید فعال باشد.', 'The pdo_mysql extension must be enabled to test the connection.'), 3, true);
    }
    $dbResult = $rows;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nvd_db_test']) && !$dbCsrfOk) {
    $dbResult = array(nvd_item(
        T('درخواست نامعتبر است (CSRF)', 'Invalid Request (CSRF)'), 'fail',
        T('رد شد', 'Rejected'), '-',
        T('این فرم منقضی شده یا از منبع دیگری ارسال شده است. صفحه را تازه‌سازی کرده و دوباره تلاش کنید.',
            'This form has expired or was submitted from another source. Refresh the page and try again.'),
        0
    ));
}

/* ===========================================================================
 |  ۵) امتیازدهی و نتیجه‌گیری / Scoring & verdict
 =========================================================================== */
$totalW = 0; $gotW = 0;
$fails = array(); $warns = array(); $critFails = array();
$allItems = array();
foreach ($sections as $sec) {
    foreach ($sec['items'] as $it) $allItems[] = $it;
}
if ($dbResult) { foreach ($dbResult as $it) $allItems[] = $it; }

foreach ($allItems as $it) {
    if ($it['status'] === 'info' || (int)$it['weight'] === 0) continue;
    $totalW += $it['weight'];
    if ($it['status'] === 'pass')      $gotW += $it['weight'];
    elseif ($it['status'] === 'warn')  $gotW += $it['weight'] * 0.5;

    if ($it['status'] === 'fail') {
        $fails[] = $it;
        if ($it['critical']) $critFails[] = $it;
    } elseif ($it['status'] === 'warn') {
        $warns[] = $it;
    }
}
$score = ($totalW > 0) ? (int)round(($gotW / $totalW) * 100) : 0;

if (count($critFails) > 0) {
    $verdict      = T('آماده‌ی نصب نیست', 'Not Ready for Installation');
    $verdictClass = 'v-fail';
    $verdictText  = T('حداقل یک پیش‌نیاز حیاتی برقرار نیست. تا رفع موارد قرمز، نصب جوملا ۶ روی این هاست شکست می‌خورد.',
                       'At least one critical requirement is not met. Joomla 6 installation on this host will fail until the red items are fixed.');
} elseif (count($fails) > 0) {
    $verdict      = T('نصب می‌شود، اما ناقص', 'Will Install, But Incomplete');
    $verdictClass = 'v-warn';
    $verdictText  = T('نصب انجام می‌شود ولی بخشی از قابلیت‌ها (آپلود، به‌روزرسانی یا سئو) درست کار نخواهد کرد.',
                       'Installation will complete, but some features (upload, updates or SEO) will not work correctly.');
} elseif (count($warns) > 0) {
    $verdict      = T('آماده با نکات قابل بهبود', 'Ready, With Room for Improvement');
    $verdictClass = 'v-warn';
    $verdictText  = T('می‌توانید جوملا ۶ را نصب کنید. موارد نارنجی را برای پایداری و سرعت بیشتر اصلاح کنید.',
                       'You can install Joomla 6. Fix the orange items for more stability and speed.');
} else {
    $verdict      = T('کاملاً آماده', 'Fully Ready');
    $verdictClass = 'v-pass';
    $verdictText  = T('همه‌ی پیش‌نیازهای جوملا ۶ روی این هاست برقرار است. با خیال راحت نصب کنید.',
                       'All Joomla 6 prerequisites are met on this host. Install with confidence.');
}

// متن آماده برای پشتیبانی هاست / Ready-made text for host support
$ticket  = T('با سلام', 'Hello') . "\n\n";
$ticket .= T('قصد راه‌اندازی جوملا ۶ روی این هاست را دارم. بر اساس گزارش بررسی، لطفاً موارد زیر را اعمال بفرمایید:',
             'I am setting up Joomla 6 on this host. Based on the readiness report, please apply the following:') . "\n\n";
$tn = 1;
foreach ($fails as $it) {
    if ($it['fix'] !== '') { $ticket .= $tn . ') ' . $it['fix'] . "\n"; $tn++; }
}
foreach ($warns as $it) {
    if ($it['fix'] !== '') { $ticket .= $tn . ') ' . $it['fix'] . "\n"; $tn++; }
}
if ($tn === 1) $ticket .= T('موردی برای اصلاح یافت نشد؛ سرور کاملاً آماده است.', 'No items need fixing; the server is fully ready.') . "\n";
$ticket .= "\n" . T('دامنه: %s', 'Domain: %s', array($hostName)) . "\n";
$ticket .= T('نسخه فعلی PHP: %s', 'Current PHP version: %s', array($phpVersion)) . "\n";
$ticket .= T('با تشکر', 'Thank you');

$iniFixes = array_values(array_unique($iniFixes));

/* ---------------------------------------------------------------------------
 |  ۶) حذف امن این فایل / Safely delete this file
 --------------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nvd_selfdestruct'])) {
    if (!hash_equals($NVD_CSRF, isset($_POST['nvd_csrf']) ? (string)$_POST['nvd_csrf'] : '')) {
        $deleteError = T('درخواست نامعتبر است (CSRF)؛ صفحه را تازه‌سازی کرده و دوباره تلاش کنید.', 'Invalid request (CSRF); refresh the page and try again.');
    } else {
        $me = __FILE__;
        if (@unlink($me)) {
            @unlink($nvdKeyFile);
            $delLang = NVD_LANG;
            echo '<!DOCTYPE html><html lang="' . ($delLang === 'en' ? 'en' : 'fa') . '" dir="' . ($delLang === 'en' ? 'ltr' : 'rtl') . '"><head><meta charset="utf-8">'
               . '<title>' . T('حذف شد', 'Deleted') . '</title><style>body{font-family:Vazirmatn,Tahoma,sans-serif;background:#0B2540;color:#fff;'
               . 'display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center}'
               . 'div{max-width:520px;padding:32px}h1{font-size:22px;margin:0 0 12px}p{color:#9FB3C8;line-height:2}</style></head><body><div>'
               . '<h1>' . T('فایل بررسی با موفقیت حذف شد', 'The checker file was successfully deleted') . '</h1>'
               . '<p>' . T('دیگر هیچ اطلاعاتی از سرور شما در دسترس عموم نیست.', 'No information about your server is publicly accessible anymore.') . '<br>'
               . T('موفق باشید — %s', 'Good luck — %s', array(nvd_e(NVD_COMPANY_EN) . ' (' . nvd_e(NVD_COMPANY) . ')')) . '</p></div></body></html>';
            exit;
        }
        $deleteError = T('حذف خودکار ممکن نشد؛ فایل را دستی از طریق File Manager پاک کنید.', 'Automatic deletion failed; delete the file manually via File Manager.');
    }
}

/* ---------------------------------------------------------------------------
 |  ۷) خروجی HTML / HTML output
 --------------------------------------------------------------------------- */
$statusMeta = array(
    'pass' => array('label' => T('قبول', 'Pass'),   'cls' => 'st-pass'),
    'warn' => array('label' => T('هشدار', 'Warning'),  'cls' => 'st-warn'),
    'fail' => array('label' => T('مردود', 'Fail'),  'cls' => 'st-fail'),
    'info' => array('label' => T('اطلاع', 'Info'),  'cls' => 'st-info'),
);
$selfUrl = htmlspecialchars(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '', ENT_QUOTES, 'UTF-8');
$activeKey = isset($_GET['key']) ? (string)$_GET['key'] : ($nvdKeyIsNew ? $nvdAutoKey : '');
$nvdKeyQS  = ($activeKey !== '') ? '&key=' . urlencode($activeKey) : '';
$htmlLang = (NVD_LANG === 'en') ? 'en' : 'fa';
$htmlDir  = (NVD_LANG === 'en') ? 'ltr' : 'rtl';
$bodyLangClass = (NVD_LANG === 'en') ? 'lang-en' : 'lang-fa';
?>
<!DOCTYPE html>
<html lang="<?php echo $htmlLang; ?>" dir="<?php echo $htmlDir; ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?php echo T('بررسی پیش‌نیازهای جوملا ۶', 'Joomla 6 Readiness Check'); ?> | <?php echo nvd_e(NVD_COMPANY); ?></title>
<style>
:root{
  --navy:#0B2540; --navy-2:#123A5C; --fz:#16BDB3; --fz-dark:#0E8C85;
  --gold:#D4A03C; --bg:#EDF1F5; --card:#FFFFFF; --line:#DCE4EC;
  --ink:#12212F; --muted:#5D7183;
  --pass:#12A150; --warn:#DF8600; --fail:#DC2A4B; --info:#2C6BD8;
  --r:14px;
}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  margin:0;background:var(--bg);color:var(--ink);
  font-family:Vazirmatn,"IRANSans","Segoe UI",Tahoma,sans-serif;
  font-size:15px;line-height:1.9;-webkit-font-smoothing:antialiased;
}
body.lang-en{font-family:"Segoe UI",system-ui,-apple-system,Roboto,Arial,sans-serif}
.wrap{max-width:1080px;margin:0 auto;padding:0 18px}
code,.mono{font-family:ui-monospace,"SFMono-Regular",Menlo,Consolas,monospace;direction:ltr;unicode-bidi:embed}

/* ---------- سربرگ / header ---------- */
.top{
  background:var(--navy);color:#fff;padding:34px 0 96px;position:relative;overflow:hidden;
  border-bottom:3px solid var(--gold);
}
.top:before{
  content:"";position:absolute;inset:0;opacity:.13;
  background-image:
    repeating-linear-gradient(45deg,transparent 0 22px,rgba(22,189,179,.8) 22px 23px),
    repeating-linear-gradient(-45deg,transparent 0 22px,rgba(212,160,60,.55) 22px 23px);
}
.top>*{position:relative}
.brandbar{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.brand{display:flex;align-items:center;gap:12px}
.mark{
  width:46px;height:46px;flex:none;border-radius:13px;background:linear-gradient(140deg,var(--fz),var(--fz-dark));
  display:flex;align-items:center;justify-content:center;font-weight:800;font-size:19px;color:#04252B;
  box-shadow:0 6px 18px rgba(22,189,179,.35)
}
.brand b{display:block;font-size:16px}
.brand span{display:block;font-size:12px;color:#9FB8CC;letter-spacing:.04em}
.tag{font-size:12px;color:#9FB8CC;border:1px solid rgba(255,255,255,.18);padding:5px 12px;border-radius:999px}
.langsw{display:flex;gap:6px}
.langsw a{font-size:12px;font-weight:700;color:#9FB8CC;border:1px solid rgba(255,255,255,.18);padding:5px 12px;border-radius:999px;text-decoration:none;transition:.16s}
.langsw a.active{background:var(--fz);border-color:var(--fz);color:#04252B}
.langsw a:hover{border-color:var(--fz);color:#fff}
.title{margin:26px 0 6px;font-size:27px;font-weight:800;letter-spacing:-.02em}
.subtitle{margin:0;color:#A9C1D4;max-width:640px}

/* ---------- کارت نتیجه / verdict card ---------- */
.verdict{margin-top:-70px;background:var(--card);border:1px solid var(--line);border-radius:20px;
  padding:26px;display:flex;gap:26px;align-items:center;flex-wrap:wrap;box-shadow:0 18px 40px rgba(11,37,64,.10)}
.gauge{--p:0;width:132px;height:132px;flex:none;border-radius:50%;display:grid;place-items:center;
  background:conic-gradient(var(--gaugecolor) calc(var(--p)*1%),#E6ECF2 0);position:relative}
.gauge:after{content:"";position:absolute;inset:11px;background:var(--card);border-radius:50%}
.gauge b{position:relative;font-size:31px;font-weight:800;line-height:1}
.gauge i{position:relative;font-style:normal;font-size:11px;color:var(--muted);display:block;margin-top:2px}
.vbody{flex:1;min-width:260px}
.vbody h2{margin:0 0 6px;font-size:22px}
.vbody p{margin:0;color:var(--muted)}
.v-pass h2{color:var(--pass)} .v-warn h2{color:var(--warn)} .v-fail h2{color:var(--fail)}
.counts{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
.pill{border-radius:999px;padding:5px 14px;font-size:13px;font-weight:600;border:1px solid}
.p-pass{color:var(--pass);border-color:rgba(18,161,80,.3);background:rgba(18,161,80,.07)}
.p-warn{color:var(--warn);border-color:rgba(223,134,0,.3);background:rgba(223,134,0,.07)}
.p-fail{color:var(--fail);border-color:rgba(220,42,75,.3);background:rgba(220,42,75,.07)}

/* ---------- دکمه‌ها / buttons ---------- */
.actions{display:flex;gap:10px;flex-wrap:wrap;margin:22px 0 6px}
.btn{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--line);background:var(--card);
  color:var(--ink);padding:10px 17px;border-radius:11px;font:inherit;font-size:14px;font-weight:600;
  cursor:pointer;text-decoration:none;transition:.16s}
.btn:hover{border-color:var(--fz);color:var(--fz-dark);transform:translateY(-1px)}
.btn-p{background:var(--navy);color:#fff;border-color:var(--navy)}
.btn-p:hover{background:var(--navy-2);color:#fff}
.btn-d{color:var(--fail);border-color:rgba(220,42,75,.35)}
.btn-d:hover{background:var(--fail);color:#fff;border-color:var(--fail)}

/* ---------- بخش‌ها / sections ---------- */
.sec{background:var(--card);border:1px solid var(--line);border-radius:var(--r);margin:18px 0;overflow:hidden}
.sec>header{padding:17px 20px;border-bottom:1px solid var(--line);background:linear-gradient(180deg,#FAFCFE,#F3F7FA)}
.sec h3{margin:0;font-size:17px;display:flex;align-items:center;gap:9px}
.sec h3 em{width:7px;height:20px;border-radius:4px;background:var(--fz);font-style:normal;flex:none}
.sec header p{margin:5px 0 0;font-size:13px;color:var(--muted)}
.row{display:grid;grid-template-columns:1.15fr 1fr 1fr 92px;gap:14px;padding:15px 20px;border-top:1px solid #EEF2F6;align-items:start}
.row:first-of-type{border-top:0}
.row:hover{background:#FBFDFE}
.rl{font-weight:700}
.rl small{display:block;font-weight:400;font-size:12.5px;color:var(--muted);margin-top:4px;line-height:1.8}
.rv{font-size:13px}
.rv span{display:block;font-size:11px;color:var(--muted);margin-bottom:2px}
.rv code{background:#F1F5F9;border:1px solid #E2E8F0;border-radius:7px;padding:2px 7px;display:inline-block;font-size:12.5px;word-break:break-all}
.badge{justify-self:start;font-size:12px;font-weight:700;padding:5px 12px;border-radius:8px;white-space:nowrap}
.st-pass{background:rgba(18,161,80,.1);color:var(--pass)}
.st-warn{background:rgba(223,134,0,.12);color:var(--warn)}
.st-fail{background:rgba(220,42,75,.1);color:var(--fail)}
.st-info{background:rgba(44,107,216,.09);color:var(--info)}

/* ---------- جعبه‌های کمکی / helper boxes ---------- */
.box{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:20px;margin:18px 0}
.box h3{margin:0 0 6px;font-size:17px}
.box p.hint{margin:0 0 14px;color:var(--muted);font-size:13.5px}
pre.snip{background:#0B2540;color:#CFE3F2;border-radius:11px;padding:16px;overflow:auto;font-size:13px;
  direction:ltr;text-align:left;margin:0;line-height:1.9;font-family:ui-monospace,Menlo,Consolas,monospace}
textarea.snip{width:100%;min-height:190px;background:#F7FAFC;border:1px solid var(--line);border-radius:11px;
  padding:14px;font:inherit;font-size:13.5px;line-height:2;color:var(--ink);resize:vertical}
.grid2{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px}
label.fld{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
input.inp{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:10px;font:inherit;font-size:14px;background:#F9FBFD}
input.inp:focus{outline:2px solid rgba(22,189,179,.35);border-color:var(--fz)}
.note{border-inline-start:4px solid var(--gold);background:#FFF9EE;padding:12px 15px;border-radius:9px;font-size:13.5px;color:#6B5320;margin-top:14px}

/* ---------- پاورقی / footer ---------- */
.foot{background:var(--navy);color:#C6D8E6;margin-top:34px;padding:40px 0 0;border-top:3px solid var(--gold)}
.fgrid{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:30px}
.foot h4{color:#fff;font-size:15px;margin:0 0 12px}
.foot p{margin:0 0 10px;font-size:13.5px;line-height:2.1;color:#9FB8CC}
.foot ul{list-style:none;margin:0;padding:0}
.foot li{font-size:13.5px;padding:5px 0;color:#9FB8CC;display:flex;gap:8px}
.foot li:before{content:"◆";color:var(--fz);font-size:9px;line-height:2.4}
.tel{display:flex;align-items:center;gap:9px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);
  border-radius:11px;padding:10px 14px;margin-bottom:9px;color:#fff;text-decoration:none;transition:.16s}
.tel:hover{background:var(--fz);border-color:var(--fz);color:#04252B}
.tel b{font-size:15px;letter-spacing:.03em;direction:ltr}
.tel span{font-size:11px;color:#9FB8CC}
.tel:hover span{color:#04353B}
.sitelinks{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0 0}
.sitelinks a{font-size:12.5px;font-weight:600;color:#C6D8E6;background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);border-radius:999px;padding:6px 13px;text-decoration:none;
  direction:ltr;transition:.16s}
.sitelinks a:hover{background:var(--fz);border-color:var(--fz);color:#04252B}
.copy{margin-top:34px;border-top:1px solid rgba(255,255,255,.1);padding:16px 0;display:flex;
  justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:12.5px;color:#7F97AC}
@media (max-width:820px){
  .row{grid-template-columns:1fr;gap:7px}
  .badge{justify-self:end;margin-top:-30px}
  .fgrid{grid-template-columns:1fr}
  .title{font-size:22px}
}
@media print{
  body{background:#fff}.actions,.box form,.btn,.langsw{display:none}.sec,.box{break-inside:avoid}
}
</style>
</head>
<body class="<?php echo $bodyLangClass; ?>">

<header class="top">
  <div class="wrap">
    <div class="brandbar">
      <div class="brand">
        <div class="mark">نـ</div>
        <div>
          <b><?php echo nvd_e(NVD_COMPANY); ?></b>
          <span><?php echo T('طراحی وب‌سایت · سئو · میزبانی وب · ثبت دامنه', 'Web Design · SEO · Web Hosting · Domain Registration'); ?></span>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <div class="langsw">
          <a href="<?php echo nvd_lang_url('fa'); ?>" class="<?php echo NVD_LANG === 'fa' ? 'active' : ''; ?>">فارسی</a>
          <a href="<?php echo nvd_lang_url('en'); ?>" class="<?php echo NVD_LANG === 'en' ? 'active' : ''; ?>">English</a>
        </div>
        <div class="tag"><?php echo T('نسخه ابزار %s · جوملا ۶', 'Tool version %s · Joomla 6', array(nvd_fa_num(NVD_VERSION))); ?></div>
      </div>
    </div>
    <h1 class="title"><?php echo T('بررسی پیش‌نیازهای نصب و راه‌اندازی جوملا! ۶', 'Joomla! 6 Installation Readiness Check'); ?></h1>
    <p class="subtitle">
      <?php echo T('این ابزار سرور شما را در برابر الزامات رسمی جوملا ۶ می‌سنجد و دقیقاً می‌گوید چه چیزی باید تغییر کند.',
                    'This tool measures your server against Joomla 6\'s official requirements and tells you exactly what needs to change.'); ?>
      <?php echo T('دامنه‌ی بررسی‌شده:', 'Domain checked:'); ?> <code style="color:#CFE3F2"><?php echo nvd_e($hostName); ?></code>
    </p>
  </div>
</header>

<main class="wrap">

  <section class="verdict <?php echo $verdictClass; ?>">
    <div class="gauge" style="--p:<?php echo (int)$score; ?>;--gaugecolor:<?php
        echo $score >= 90 ? 'var(--pass)' : ($score >= 65 ? 'var(--warn)' : 'var(--fail)'); ?>">
      <b><?php echo nvd_fa_num($score); ?><small style="font-size:15px">٪</small></b>
      <i><?php echo T('آمادگی سرور', 'Server Readiness'); ?></i>
    </div>
    <div class="vbody">
      <h2><?php echo nvd_e($verdict); ?></h2>
      <p><?php echo nvd_e($verdictText); ?></p>
      <div class="counts">
        <span class="pill p-fail"><?php echo T('مردود:', 'Fail:'); ?> <?php echo nvd_fa_num(count($fails)); ?></span>
        <span class="pill p-warn"><?php echo T('هشدار:', 'Warning:'); ?> <?php echo nvd_fa_num(count($warns)); ?></span>
        <span class="pill p-pass"><?php echo T('بررسی‌شده: %s مورد', 'Checked: %s items', array(nvd_fa_num(count($allItems)))); ?></span>
      </div>
    </div>
  </section>

  <div class="actions">
    <?php if (!$deep): ?>
      <a class="btn btn-p" href="?deep=1&lang=<?php echo NVD_LANG . $nvdKeyQS; ?>#net"><?php echo T('اجرای تست‌های عمیق (شبکه و mod_rewrite)', 'Run Deep Tests (network & mod_rewrite)'); ?></a>
    <?php else: ?>
      <a class="btn" href="<?php echo $selfUrl . '?lang=' . NVD_LANG . $nvdKeyQS; ?>"><?php echo T('بازگشت به حالت سریع', 'Back to Quick Mode'); ?></a>
    <?php endif; ?>
    <button class="btn" onclick="nvdCopy()"><?php echo T('کپی متن آماده برای پشتیبانی هاست', 'Copy Ready-Made Text for Host Support'); ?></button>
    <button class="btn" onclick="window.print()"><?php echo T('چاپ / ذخیره PDF', 'Print / Save as PDF'); ?></button>
    <a class="btn" href="#dbtest"><?php echo T('تست اتصال دیتابیس', 'Database Connection Test'); ?></a>
    <form method="post" style="display:inline" onsubmit="return confirm('<?php echo T('این فایل برای همیشه حذف می‌شود. مطمئن هستید؟', 'This file will be permanently deleted. Are you sure?'); ?>')">
      <input type="hidden" name="nvd_selfdestruct" value="1">
      <input type="hidden" name="nvd_csrf" value="<?php echo nvd_e($NVD_CSRF); ?>">
      <button class="btn btn-d" type="submit"><?php echo T('حذف این فایل از سرور', 'Delete This File From Server'); ?></button>
    </form>
  </div>
  <?php if (isset($deleteError)): ?>
    <div class="note"><?php echo nvd_e($deleteError); ?></div>
  <?php endif; ?>
  <?php if ($nvdKeyIsNew): ?>
    <div class="note" style="border-color:var(--fail);background:#FFF3F3;color:#7A1B2E">
      <?php echo T('یک کلید دسترسی تصادفی برای این گزارش ساخته شد و در فایل <code>.nvd6-lock.php</code> کنار همین اسکریپت ذخیره شد. از این پس، این صفحه فقط با همین کلید در آدرس باز می‌شود — لینک زیر را همین حالا ذخیره یا بوکمارک کنید، چون بعد از بستن این صفحه دیگر جایی نمایش داده نمی‌شود:',
                    'A random access key was generated for this report and stored in <code>.nvd6-lock.php</code> next to this script. From now on, this page only opens with that key in the URL — save or bookmark the link below now, since it will not be shown again after you leave this page:'); ?>
      <br><code class="mono" style="display:inline-block;margin-top:8px"><?php echo nvd_e(($isHttps ? 'https://' : 'http://') . $hostName . $selfUrl . '?key=' . $nvdAutoKey); ?></code>
    </div>
  <?php endif; ?>
  <?php if (!empty($nvdNoLockWarning)): ?>
    <div class="note" style="border-color:var(--fail);background:#FFF3F3;color:#7A1B2E">
      <?php echo T('ساخت خودکار قفل دسترسی ممکن نشد (پوشه غیرقابل نوشتن است)؛ این گزارش برای هر کسی که آدرس را بداند قابل مشاهده است. برای محدود کردن دسترسی، مقدار <code>NVD_ACCESS_KEY</code> را در بالای فایل به‌صورت دستی تنظیم کنید.',
                    'The automatic access lock could not be created (the folder is not writable); this report is visible to anyone who knows the URL. To restrict access, set <code>NVD_ACCESS_KEY</code> manually near the top of the file.'); ?>
    </div>
  <?php endif; ?>

<?php foreach ($sections as $sec): ?>
  <section class="sec" id="<?php echo nvd_e($sec['id']); ?>">
    <header>
      <h3><em></em><?php echo nvd_e($sec['title']); ?></h3>
      <p><?php echo nvd_e($sec['desc']); ?></p>
    </header>
    <?php foreach ($sec['items'] as $it):
      $m = $statusMeta[$it['status']]; ?>
      <div class="row">
        <div class="rl"><?php echo nvd_e($it['label']); ?><small><?php echo nvd_e($it['note']); ?></small></div>
        <div class="rv"><span><?php echo T('وضعیت فعلی', 'Current Status'); ?></span><code><?php echo nvd_e($it['actual']); ?></code></div>
        <div class="rv"><span><?php echo T('مقدار موردنیاز', 'Required Value'); ?></span><code><?php echo nvd_e($it['expected']); ?></code></div>
        <div class="badge <?php echo $m['cls']; ?>"><?php echo $m['label']; ?></div>
      </div>
    <?php endforeach; ?>
  </section>
<?php endforeach; ?>

  <!-- ============ تست دیتابیس / database test ============ -->
  <section class="box" id="dbtest">
    <h3><?php echo T('تست اتصال دیتابیس', 'Database Connection Test'); ?></h3>
    <p class="hint">
      <?php echo T('همان اطلاعاتی را وارد کنید که می‌خواهید در نصب‌کننده‌ی جوملا استفاده کنید. نسخه‌ی دیتابیس، پشتیبانی از utf8mb4 و سطح دسترسی کاربر بررسی می‌شود. هیچ اطلاعاتی ذخیره یا ارسال نمی‌شود.',
                    'Enter the same details you plan to use in the Joomla installer. The database version, utf8mb4 support and user privilege level are checked. No information is stored or transmitted.'); ?>
    </p>
    <form method="post">
      <input type="hidden" name="nvd_db_test" value="1">
      <input type="hidden" name="nvd_csrf" value="<?php echo nvd_e($NVD_CSRF); ?>">
      <div class="grid2">
        <div><label class="fld"><?php echo T('میزبان دیتابیس', 'Database Host'); ?></label>
          <input class="inp" name="db_host" dir="ltr" value="<?php echo nvd_e(nvd_get($_POST,'db_host','localhost')); ?>"></div>
        <div><label class="fld"><?php echo T('پورت', 'Port'); ?></label>
          <input class="inp" name="db_port" dir="ltr" value="<?php echo nvd_e(nvd_get($_POST,'db_port','3306')); ?>"></div>
        <div><label class="fld"><?php echo T('نام کاربری', 'Username'); ?></label>
          <input class="inp" name="db_user" dir="ltr" value="<?php echo nvd_e(nvd_get($_POST,'db_user','')); ?>"></div>
        <div><label class="fld"><?php echo T('رمز عبور', 'Password'); ?></label>
          <input class="inp" name="db_pass" type="password" dir="ltr"></div>
        <div><label class="fld"><?php echo T('نام دیتابیس', 'Database Name'); ?></label>
          <input class="inp" name="db_name" dir="ltr" value="<?php echo nvd_e(nvd_get($_POST,'db_name','')); ?>"></div>
        <div style="display:flex;align-items:flex-end">
          <button class="btn btn-p" type="submit" style="width:100%;justify-content:center"><?php echo T('اجرای تست', 'Run Test'); ?></button></div>
      </div>
    </form>

    <?php if ($dbResult): ?>
      <div style="margin-top:18px;border:1px solid var(--line);border-radius:12px;overflow:hidden">
        <?php foreach ($dbResult as $it): $m = $statusMeta[$it['status']]; ?>
          <div class="row">
            <div class="rl"><?php echo nvd_e($it['label']); ?><small><?php echo nvd_e($it['note']); ?></small></div>
            <div class="rv"><span><?php echo T('نتیجه', 'Result'); ?></span><code><?php echo nvd_e($it['actual']); ?></code></div>
            <div class="rv"><span><?php echo T('مقدار موردنیاز', 'Required Value'); ?></span><code><?php echo nvd_e($it['expected']); ?></code></div>
            <div class="badge <?php echo $m['cls']; ?>"><?php echo $m['label']; ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- ============ تنظیمات پیشنهادی php.ini / suggested php.ini fixes ============ -->
  <?php if (!empty($iniFixes)): ?>
  <section class="box">
    <h3><?php echo T('تنظیمات php.ini که باید اصلاح شود', 'php.ini Settings That Need Fixing'); ?></h3>
    <p class="hint">
      <?php echo T('این خطوط را در cPanel از مسیر <b>MultiPHP INI Editor</b> (یا فایل php.ini کنار سایت) اعمال کنید. اگر دسترسی ندارید، همین متن را برای پشتیبانی هاست بفرستید.',
                    'Apply these lines in cPanel via <b>MultiPHP INI Editor</b> (or the php.ini file next to your site). If you don\'t have access, send this text to your host\'s support.'); ?>
    </p>
    <pre class="snip"><?php foreach ($iniFixes as $f) echo nvd_e($f) . "\n"; ?></pre>
    <div class="note">
      <?php echo T('روی هاست‌های LiteSpeed و اجرای PHP در حالت CGI/FPM، دستورهای <code>php_value</code> در فایل <code>.htaccess</code> کار نمی‌کنند و باید از <code>php.ini</code> استفاده شود.',
                    'On LiteSpeed hosts and PHP running in CGI/FPM mode, <code>php_value</code> directives in <code>.htaccess</code> do not work and <code>php.ini</code> must be used instead.'); ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============ متن آماده برای پشتیبانی / ready text for support ============ -->
  <section class="box">
    <h3><?php echo T('متن آماده برای ارسال به پشتیبانی هاست', 'Ready-Made Text to Send to Host Support'); ?></h3>
    <p class="hint"><?php echo T('این متن از روی نتایج همین گزارش ساخته شده است. کپی کنید و در تیکت پشتیبانی بفرستید.', 'This text is generated from this report\'s results. Copy it and send it in a support ticket.'); ?></p>
    <textarea class="snip" id="nvdTicket" readonly><?php echo nvd_e($ticket); ?></textarea>
    <div style="margin-top:12px"><button class="btn btn-p" onclick="nvdCopy()"><?php echo T('کپی متن', 'Copy Text'); ?></button></div>
  </section>

  <!-- ============ خلاصه الزامات / requirements summary ============ -->
  <section class="box">
    <h3><?php echo T('الزامات رسمی جوملا ۶ در یک نگاه', 'Joomla 6 Official Requirements at a Glance'); ?></h3>
    <p class="hint"><?php echo T('مرجع: مستندات رسمی جوملا (manual.joomla.org) — بخش Technical Requirements.', 'Source: official Joomla documentation (manual.joomla.org) — Technical Requirements section.'); ?></p>
    <div class="grid2">
      <div class="note" style="border-color:var(--fz);background:#F2FBFA;color:#0E5B57">
        <b>PHP</b><br><?php echo T('حداقل ۸.۳.۰ · پیشنهادی ۸.۴ به بالا', 'Minimum 8.3.0 · Recommended 8.4 or higher'); ?><br>
        <?php echo T('افزونه‌های الزامی: json، simplexml، dom، zlib، gd و یکی از mysqlnd / pdo_mysql / pdo_pgsql',
                      'Required extensions: json, simplexml, dom, zlib, gd and one of mysqlnd / pdo_mysql / pdo_pgsql'); ?>
      </div>
      <div class="note" style="border-color:var(--fz);background:#F2FBFA;color:#0E5B57">
        <b><?php echo T('دیتابیس', 'Database'); ?></b><br><?php echo T('MySQL از ۸.۰.۱۳ (پیشنهادی ۸.۴)', 'MySQL from 8.0.13 (recommended 8.4)'); ?><br>
        <?php echo T('MariaDB از ۱۰.۴ (پیشنهادی ۱۲)', 'MariaDB from 10.4 (recommended 12)'); ?><br><?php echo T('PostgreSQL از ۱۲ (پیشنهادی ۱۷.۶)', 'PostgreSQL from 12 (recommended 17.6)'); ?>
      </div>
      <div class="note" style="border-color:var(--fz);background:#F2FBFA;color:#0E5B57">
        <b><?php echo T('وب‌سرور', 'Web Server'); ?></b><br><?php echo T('Apache 2.4 · Nginx از ۱.۲۶ · IIS 10', 'Apache 2.4 · Nginx from 1.26 · IIS 10'); ?><br>
        <?php echo T('mod_rewrite برای URLهای سئوپسند', 'mod_rewrite for SEF URLs'); ?>
      </div>
      <div class="note" style="border-color:var(--fz);background:#F2FBFA;color:#0E5B57">
        <b><?php echo T('تنظیمات کمینه', 'Minimum Settings'); ?></b><br>memory_limit 256M · upload_max_filesize 64M<br>
        post_max_size 64M · <?php echo T('max_execution_time حداقل ۳۰', 'max_execution_time minimum 30'); ?>
      </div>
    </div>
  </section>

</main>

<!-- ============ پاورقی شرکتی / company footer ============ -->
<footer class="foot">
  <div class="wrap">
    <div class="fgrid">
      <div>
        <h4><?php echo nvd_e(NVD_COMPANY); ?> <span style="color:#7F97AC;font-weight:400">(<?php echo nvd_e(NVD_COMPANY_EN); ?>)</span></h4>
        <p>
          <?php echo T('ما سایت‌ها را نمی‌سازیم که فقط بالا بیایند؛ می‌سازیم که کار کنند. از انتخاب دامنه و میزبانی تا طراحی، سئو و نگهداری ماهانه — همه‌ی مسیر حضور آنلاین کسب‌وکار شما زیر یک سقف مدیریت می‌شود. این ابزار هم بخشی از همان نگاه است: پیش از نصب، مطمئن شوید زیرساخت آماده است.',
                        'We don\'t just build sites that go live; we build sites that work. From choosing a domain and hosting to design, SEO and monthly maintenance — your entire online presence is managed under one roof. This tool is part of that same philosophy: before you install, make sure the infrastructure is ready.'); ?>
        </p>
        <p style="color:#7F97AC;font-size:12.5px">
          <?php echo T('تخصص ما در جوملا، وردپرس و توسعه‌ی اختصاصی؛ با پشتیبانی فارسی و عربی برای بازار ایران و عراق.',
                        'Our expertise: Joomla, WordPress and custom development; with Persian and Arabic support for the Iran and Iraq markets.'); ?>
        </p>
        <div class="sitelinks">
          <?php foreach (NVD_SITES as $siteLabel => $siteUrl): ?>
            <a href="<?php echo nvd_e($siteUrl); ?>" target="_blank" rel="noopener noreferrer"><?php echo nvd_e($siteLabel); ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <h4><?php echo T('خدمات ما', 'Our Services'); ?></h4>
        <ul>
          <li><?php echo T('طراحی و توسعه‌ی وب‌سایت', 'Website Design & Development'); ?></li>
          <li><?php echo T('بهینه‌سازی و سئو (SEO)', 'Search Engine Optimization (SEO)'); ?></li>
          <li><?php echo T('میزبانی وب پرسرعت', 'High-Speed Web Hosting'); ?></li>
          <li><?php echo T('ثبت و انتقال دامنه', 'Domain Registration & Transfer'); ?></li>
          <li><?php echo T('بهینه‌سازی نرخ تبدیل (CRO)', 'Conversion Rate Optimization (CRO)'); ?></li>
          <li><?php echo T('تبلیغات و بازاریابی دیجیتال', 'Digital Advertising & Marketing'); ?></li>
          <li><?php echo T('پشتیبانی و نگهداری سایت', 'Website Support & Maintenance'); ?></li>
          <li><?php echo T('مهاجرت و ارتقای جوملا', 'Joomla Migration & Upgrades'); ?></li>
        </ul>
      </div>
      <div>
        <h4><?php echo T('مشاوره‌ی رایگان', 'Free Consultation'); ?></h4>
        <a class="tel" href="tel:<?php echo nvd_e(NVD_PHONE1); ?>">
          <b><?php echo nvd_e(NVD_PHONE1); ?></b><span><?php echo T('همراه · واتساپ', 'Mobile · WhatsApp'); ?></span>
        </a>
        <a class="tel" href="tel:<?php echo nvd_e(NVD_PHONE2); ?>">
          <b><?php echo nvd_e(NVD_PHONE2); ?></b><span><?php echo T('دفتر مرکزی', 'Head Office'); ?></span>
        </a>
        <p style="font-size:12.5px;margin-top:12px">
          <?php echo T('گزارش این صفحه را برای ما بفرستید؛ در کمتر از یک روز کاری وضعیت هاست شما را بررسی می‌کنیم.',
                        'Send us this page\'s report; we\'ll review your hosting status within one business day.'); ?>
        </p>
      </div>
    </div>
    <div class="copy">
      <span>© <?php echo nvd_fa_num(date('Y')); ?> <?php echo nvd_e(NVD_COMPANY); ?> — <?php echo T('کلیه حقوق محفوظ است.', 'All rights reserved.'); ?></span>
      <span><?php echo nvd_e(NVD_COMPANY_EN); ?> · Joomla! 6 Readiness Checker v<?php echo nvd_e(NVD_VERSION); ?></span>
    </div>
  </div>
</footer>

<script>
function nvdCopy(){
  var t = document.getElementById('nvdTicket');
  t.select(); t.setSelectionRange(0, 99999);
  var done = false;
  try { done = document.execCommand('copy'); } catch(e){}
  if (!done && navigator.clipboard) { navigator.clipboard.writeText(t.value); done = true; }
  alert(done ? <?php echo json_encode(T('متن کپی شد. آن را در تیکت پشتیبانی هاست بفرستید.', 'Text copied. Paste it into your host\'s support ticket.')); ?> : <?php echo json_encode(T('کپی نشد؛ متن را دستی انتخاب کنید.', 'Copy failed; select the text manually.')); ?>);
}
</script>
</body>
</html>
