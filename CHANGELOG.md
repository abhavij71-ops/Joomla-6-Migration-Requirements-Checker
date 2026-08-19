# Changelog

All notable changes to this project are documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project uses [Semantic Versioning](https://semver.org/).

> نسخه‌ی فارسی در ادامه‌ی همین فایل آمده است.

## [2.1.0] - 2026-08-19

### Added
- **Access lock is now on by default.** If `NVD_ACCESS_KEY` is left empty, the tool auto-generates a random key on first run, stores it in `.nvd6-lock.php` next to the script (a `.php` file, so a direct request to it just executes and returns empty instead of leaking its contents), and shows the key plus a ready `?key=...` link on that one response only.
- **CSRF protection** on both POST forms (the self-destruct button and the database connection test), via a per-visitor token stored in a cookie and checked with `hash_equals()`.
- Website links added throughout the tool and docs: navidiranian.com, navidiranian.co.ir, joomlafarsi.co.ir, cmssupport.ir.

### Changed
- Bumped tool version from `2.0.0` to `2.1.0`.
- The PHP-CLI version probe (`exec('php -v')`) now only runs when Deep Tests (`?deep=1`) are explicitly launched, instead of on every page load — avoids triggering host security monitoring for an unnecessary shell command.
- The access-key comparison now uses `hash_equals()` instead of `!==`, avoiding a timing side-channel.
- Contact phone numbers updated to international format: +98 939 556 6652 (mobile/WhatsApp) and +98 21 9130 3662 (head office), both rendered as `tel:` links.
- `.gitignore` now excludes the auto-generated `.nvd6-lock.php` lock file.

## [2.0.0] - 2026-07-24

### Added
- Full bilingual UI: **English** and **Persian (فارسی)**, switchable at runtime via `?lang=en` / `?lang=fa` buttons in the header, with the choice remembered in a cookie.
- Automatic `dir`/`lang` switching (`rtl`/`fa` ↔ `ltr`/`en`) and a matching font stack for each language.
- `T()` translation helper used throughout the script so every label, note, expected value, fix suggestion, section heading, button, and the footer/support-ticket text is available in both languages.

### Changed
- Bumped tool version from `1.0.0` to `2.0.0` to reflect the new multilingual architecture.
- `nvd_fa_num()` now only converts digits to Persian numerals when the active language is Persian; English mode keeps Western digits.
- The `.note` callout box now uses the logical CSS property `border-inline-start` instead of `border-right`, so it flips correctly between RTL and LTR layouts.

### Fixed
- The "copy support text" JS alert messages are now emitted with `json_encode()` instead of being interpolated raw into a single-quoted JS string — the English message contains an apostrophe ("host's support ticket") that would otherwise have broken the inline `<script>` block.

## [1.0.0] - Initial release

### Added
- Single-file, dependency-free PHP script that audits a shared-hosting account against Joomla! 6's official technical requirements.
- Checks: PHP version and EOL status, execution mode (SAPI), web server, database driver, required/recommended PHP extensions, `php.ini` settings, shared-hosting-specific restrictions (`disable_functions`, `open_basedir`, Suhosin, ionCube/SourceGuardian), filesystem permissions and free disk space, HTTPS.
- Optional deep tests (`?deep=1`): connectivity to Joomla's update/download/extension servers, a live `mod_rewrite`/`.htaccess` probe, DNS resolution.
- Optional database connection test (MySQL/MariaDB version, `utf8mb4` support, user privileges, InnoDB availability).
- Weighted readiness score and verdict, ready-made host-support ticket text, self-destruct button, print/PDF export, optional `?key=` access lock.
- Persian-only UI, styled with Navid Iranian Co. branding.

---

# 🇮🇷 فارسی

## [۲.۱.۰] - ۱۴۰۵/۰۵/۲۸ (۲۰۲۶-۰۸-۱۹)

### افزوده‌شده
- **قفل دسترسی از این پس به‌صورت پیش‌فرض فعال است.** اگر `NVD_ACCESS_KEY` خالی بماند، ابزار در اولین اجرا یک کلید تصادفی می‌سازد، آن را در فایل `.nvd6-lock.php` کنار اسکریپت ذخیره می‌کند (چون یک فایل php است، حتی درخواست مستقیم به آن فقط اجرا و خالی برمی‌گردد و متنش فاش نمی‌شود)، و کلید به‌همراه یک لینک آماده‌ی `?key=...` را فقط در همان یک پاسخ نشان می‌دهد.
- **محافظت CSRF** روی هر دو فرم POST (دکمه‌ی خودحذفی و فرم تست اتصال دیتابیس)، با یک توکن اختصاصی هر بازدیدکننده که در کوکی ذخیره و با `hash_equals()` بررسی می‌شود.
- افزودن پیوند به وب‌سایت‌های شرکت در سراسر ابزار و مستندات: navidiranian.com، navidiranian.co.ir، joomlafarsi.co.ir، cmssupport.ir.

### تغییریافته
- ارتقای شماره‌ی نسخه‌ی ابزار از `۲.۰.۰` به `۲.۱.۰`.
- بررسی نسخه‌ی PHP خط فرمان (`exec('php -v')`) اکنون فقط در حالت «تست‌های عمیق» (`?deep=1`) اجرا می‌شود، نه در هر بار بارگذاری صفحه — تا ابزارهای امنیتی هاست به‌خاطر اجرای بی‌مورد یک فرمان shell هشدار ندهند.
- مقایسه‌ی کلید دسترسی اکنون با `hash_equals()` انجام می‌شود نه با `!==`، تا از حمله‌ی زمان‌سنجی (timing attack) جلوگیری شود.
- شماره‌های تماس به قالب بین‌المللی به‌روزرسانی شدند: ۰۹۳۹۵۵۶۶۶۵۲+ (همراه/واتساپ) و ۰۲۱۹۱۳۰۳۶۶۲+ (دفتر مرکزی)، هر دو به‌صورت پیوند `tel:`.
- فایل `.gitignore` اکنون فایل قفل خودکارِ `.nvd6-lock.php` را نادیده می‌گیرد.

## [۲.۰.۰] - ۱۴۰۵/۰۵/۰۲ (۲۰۲۶-۰۷-۲۴)

### افزوده‌شده
- رابط کاربری کاملاً چندزبانه: **انگلیسی** و **فارسی**، با امکان تغییر در لحظه از طریق دکمه‌های `?lang=en` / `?lang=fa` در سربرگ صفحه؛ انتخاب زبان در یک کوکی ذخیره می‌شود.
- تغییر خودکار جهت و زبان صفحه (`rtl`/`fa` در برابر `ltr`/`en`) به همراه فونت متناسب با هر زبان.
- افزودن تابع کمکی ترجمه‌ی `T()` که در سراسر اسکریپت استفاده شده تا تمام برچسب‌ها، توضیحات، مقادیر موردنیاز، پیشنهادهای اصلاح، عنوان بخش‌ها، دکمه‌ها و همچنین متن پاورقی/تیکت پشتیبانی، به هر دو زبان در دسترس باشند.

### تغییریافته
- ارتقای شماره‌ی نسخه‌ی ابزار از `۱.۰.۰` به `۲.۰.۰` برای انعکاس معماری چندزبانه‌ی جدید.
- تابع `nvd_fa_num()` اکنون فقط زمانی ارقام را به فارسی تبدیل می‌کند که زبان فعال فارسی باشد؛ در حالت انگلیسی همان ارقام لاتین باقی می‌مانند.
- جعبه‌ی یادداشت (`.note`) اکنون از ویژگی منطقی CSS به نام `border-inline-start` به‌جای `border-right` استفاده می‌کند تا در چیدمان راست‌به‌چپ و چپ‌به‌راست هر دو درست نمایش داده شود.

### رفع‌شده
- پیام‌های هشدار جاوااسکریپت مربوط به «کپی متن پشتیبانی» اکنون با `json_encode()` تولید می‌شوند نه با جای‌گذاری مستقیم داخل رشته‌ی تک‌کوتیشن جاوااسکریپت — متن انگلیسی شامل یک آپاستروف («host's support ticket») بود که در حالت قبلی، بلوک `<script>` را می‌شکست.

## [۱.۰.۰] - نسخه‌ی اولیه

### افزوده‌شده
- اسکریپت تک‌فایلی PHP و بدون هیچ وابستگی که یک هاست اشتراکی را بر اساس الزامات فنی رسمی جوملا! ۶ بررسی می‌کند.
- بررسی‌ها شامل: نسخه‌ی PHP و وضعیت پایان پشتیبانی، حالت اجرا (SAPI)، وب‌سرور، درایور دیتابیس، افزونه‌های الزامی و توصیه‌شده‌ی PHP، تنظیمات `php.ini`، محدودیت‌های خاص هاست اشتراکی (`disable_functions`، `open_basedir`، Suhosin، ionCube/SourceGuardian)، مجوزهای فایل‌سیستم و فضای آزاد دیسک، HTTPS.
- تست‌های عمیق اختیاری (`?deep=1`): اتصال به سرورهای به‌روزرسانی/دانلود/افزونه‌ی جوملا، آزمون واقعی `mod_rewrite`/`.htaccess`، تفکیک DNS.
- تست اتصال دیتابیس اختیاری (نسخه‌ی MySQL/MariaDB، پشتیبانی از `utf8mb4`، سطح دسترسی کاربر، فعال بودن InnoDB).
- امتیاز آمادگی وزن‌دار و نتیجه‌گیری نهایی، متن آماده‌ی تیکت پشتیبانی هاست، دکمه‌ی خودحذفی، خروجی چاپ/PDF، امکان قفل دسترسی اختیاری با `?key=`.
- رابط کاربری فقط فارسی، با هویت بصری شرکت نوید ایرانیان.
