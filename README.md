# Joomla! 6 Shared-Hosting Readiness Checker

A single-file PHP script that audits a shared-hosting account against Joomla! 6's official technical requirements — PHP version, required/recommended extensions, `php.ini` settings, filesystem permissions, database compatibility, and more — then produces a clear pass/warn/fail report plus a ready-to-send message for your host's support team.

Built and maintained by **Navid Iranian Co.** (شرکت نوید ایرانیان) — web design, SEO, hosting and digital marketing.
🌐 [navidiranian.com](https://navidiranian.com/) · [navidiranian.co.ir](https://navidiranian.co.ir/) · [joomlafarsi.co.ir](https://joomlafarsi.co.ir/) · [cmssupport.ir](https://cmssupport.ir/)
📞 [+98 939 556 6652](tel:+989395566652) (Mobile/WhatsApp) · [+98 21 9130 3662](tel:+982191303662) (Head Office)

🌐 **Bilingual**: the tool's UI is available in **English** and **Persian (فارسی)**, switchable at runtime with no reinstall.

> راهنمای فارسی در ادامه‌ی همین صفحه آمده است — به بخش [«فارسی»](#-فارسی) بروید.

---

## ✨ Features

- **Zero dependencies** — one PHP file, no Composer, no build step.
- **PHP 5.6 → 8.5 compatible** — deliberately written in old-style syntax so it still runs (and can *diagnose*) legacy hosts instead of crashing with a parse error.
- Checks against Joomla 6's official requirements (source: [manual.joomla.org](https://manual.joomla.org/) — Technical Requirements):
  - PHP version, EOL/security-support window, execution mode (SAPI), web server
  - Required extensions: `json`, `simplexml`, `dom`, `libxml`, `zlib`, `gd`, `pcre`, `session`, `filter`, `hash`, `ctype`
  - Recommended extensions: `mbstring`, `iconv`, `curl`, `zip`, `openssl`, `fileinfo`, `intl`, `exif`, `sodium`, OPcache, `imagick`
  - `php.ini` values: `memory_limit`, `max_execution_time`, `upload_max_filesize`, `post_max_size`, `max_input_vars`, `file_uploads`, `session.auto_start`, `output_buffering`, `display_errors`, `allow_url_fopen`, `date.timezone`, `default_charset`, `expose_php`, OPcache
  - Shared-hosting landmines: `disable_functions`, `open_basedir`, Suhosin, ionCube/SourceGuardian loaders, PHP CLI version, `mail()`
  - Filesystem: file/folder creation, ownership match, session save path, upload temp dir, free disk space, HTTPS
  - **Optional deep tests** (`?deep=1`): live connectivity to `update.joomla.org` / `downloads.joomla.org` / `extensions.joomla.org`, a real `mod_rewrite`/`.htaccess` probe, DNS resolution
  - **Optional database test**: connects with credentials you enter, checks MySQL/MariaDB version, `utf8mb4` support, `CREATE`/`ALTER`/`DROP` privileges, InnoDB availability — nothing is stored or transmitted anywhere
- **Weighted readiness score** (0–100%) with a clear verdict: *Not Ready*, *Will Install But Incomplete*, *Ready With Improvements*, or *Fully Ready*.
- **Ready-made support-ticket text**, auto-generated from the failed/warned items, one click to copy.
- **Self-destruct button** — deletes the script from the server when you're done (recommended: never leave this file publicly accessible). Protected by a CSRF token, so it can't be triggered by a cross-site request.
- **Print/PDF export** of the full report.
- **Access lock, on by default** — set `NVD_ACCESS_KEY` yourself, or leave it empty and the tool auto-generates a random key on first run, storing it in `.nvd6-lock.php` next to the script and requiring `?key=...` on every request after that.
- The database-test form is also CSRF-protected, and the CLI-PHP version probe (which shells out to `php -v`) only runs when you explicitly launch Deep Tests, not on every page load.

## 🚀 Usage

1. Download [`joomla6check.php`](joomla6check.php) and upload it to your hosting root (e.g. `public_html`).
2. Open it in your browser:
   ```
   https://your-domain.com/joomla6check.php
   ```
3. Read the report. Switch language anytime with the **English / فارسی** buttons, or via URL: `?lang=en` / `?lang=fa`.
4. Optionally run the deep network/`mod_rewrite` tests: `?deep=1`.
5. Optionally test a database connection using the on-page form.
6. Copy the ready-made ticket text and send it to your host's support if anything needs fixing.
7. **When you're done, click "Delete This File From Server"** (or delete it manually). Never leave a diagnostic script like this publicly reachable long-term.

### Locking the report

By default the report is locked. If you leave `NVD_ACCESS_KEY` empty, the tool generates a random key on its first run and saves it to `.nvd6-lock.php` in the same folder; that first response shows you the key and a ready-made link with `?key=...` — save it, since it won't be shown again. Every request after that requires the same key.

> On a host where anyone could plausibly open the URL before you do, set the key yourself in advance instead of relying on auto-generation:

```php
define('NVD_ACCESS_KEY', 'something-only-you-know');
```

Then the report only opens with `?key=something-only-you-know` in the URL. If the folder isn't writable and no key is set, the tool falls back to no lock and shows a warning banner on every load telling you to set one manually.

## 🖥 Requirements

- PHP 5.6 or later on the server you're checking (that's the whole point — it will tell you if your PHP is too old).
- No extensions are required to *run* the checker itself; it degrades gracefully and reports what's missing.

## 🌍 Adding another language

Every user-facing string goes through a small helper:

```php
T('متن فارسی', 'English text');          // static string
T('حداقل %s', 'Minimum %s', array($v));  // with vsprintf-style placeholders
```

To add a third language, extend the `T()` helper (e.g. accept a language code and a lookup table) and add the corresponding switch link in the header. Contributions welcome — see below.

## 🤝 Contributing

Issues and pull requests are welcome. Please keep the file dependency-free and PHP 5.6-compatible.

## 📄 License

Released under the [MIT License](LICENSE).

## 🏢 About

**Navid Iranian Co. (شرکت نوید ایرانیان)** — website design, SEO, web hosting, domain registration and digital marketing, with Persian/Arabic support for the Iran and Iraq markets.
🌐 [navidiranian.com](https://navidiranian.com/) · [navidiranian.co.ir](https://navidiranian.co.ir/) · [joomlafarsi.co.ir](https://joomlafarsi.co.ir/) · [cmssupport.ir](https://cmssupport.ir/)
📞 [+98 939 556 6652](tel:+989395566652) · [+98 21 9130 3662](tel:+982191303662)

---

# 🇮🇷 فارسی

ابزاری تک‌فایلی و کاملاً PHP برای بررسی آمادگی هاست اشتراکی جهت نصب **جوملا! ۶**. این اسکریپت سرور را دقیقاً بر اساس الزامات فنی رسمی جوملا ۶ می‌سنجد — نسخه‌ی PHP، افزونه‌های الزامی و توصیه‌شده، تنظیمات `php.ini`، مجوزهای فایل‌سیستم، سازگاری دیتابیس و موارد دیگر — و در پایان یک گزارش شفاف با وضعیت قبول/هشدار/مردود، به همراه متنی آماده برای ارسال به پشتیبانی هاست، تحویل می‌دهد.

ساخته و نگهداری‌شده توسط **شرکت نوید ایرانیان** — طراحی وب‌سایت، سئو، میزبانی وب و دیجیتال مارکتینگ.
🌐 [navidiranian.com](https://navidiranian.com/) · [navidiranian.co.ir](https://navidiranian.co.ir/) · [joomlafarsi.co.ir](https://joomlafarsi.co.ir/) · [cmssupport.ir](https://cmssupport.ir/)
📞 [۰۹۳۹ ۵۵۶ ۶۶۵۲](tel:+989395566652) (همراه/واتساپ) · [۰۲۱ ۹۱۳۰ ۳۶۶۲](tel:+982191303662) (دفتر مرکزی)

🌐 **چندزبانه**: رابط کاربری ابزار به **فارسی** و **انگلیسی** در دسترس است و بدون نیاز به نصب مجدد، در لحظه قابل تغییر است.

## ✨ امکانات

- **بدون هیچ وابستگی** — فقط یک فایل PHP، بدون Composer و بدون نیاز به Build.
- **سازگار با PHP از نسخه‌ی ۵.۶ تا ۸.۵** — عمداً با نحو قدیمی نوشته شده تا حتی روی هاست‌های قدیمی هم اجرا شود و به‌جای خطای Syntax، خودِ قدیمی‌بودن PHP را گزارش کند.
- بررسی بر اساس الزامات رسمی جوملا ۶ (منبع: [manual.joomla.org](https://manual.joomla.org/) — بخش Technical Requirements):
  - نسخه‌ی PHP، پایان پشتیبانی امنیتی، حالت اجرا (SAPI)، وب‌سرور
  - افزونه‌های الزامی: `json`, `simplexml`, `dom`, `libxml`, `zlib`, `gd`, `pcre`, `session`, `filter`, `hash`, `ctype`
  - افزونه‌های توصیه‌شده: `mbstring`, `iconv`, `curl`, `zip`, `openssl`, `fileinfo`, `intl`, `exif`, `sodium`, OPcache, `imagick`
  - تنظیمات `php.ini`: `memory_limit`, `max_execution_time`, `upload_max_filesize`, `post_max_size`, `max_input_vars`, `file_uploads`, `session.auto_start`, `output_buffering`, `display_errors`, `allow_url_fopen`, `date.timezone`, `default_charset`, `expose_php`, OPcache
  - دام‌های رایج هاست اشتراکی: `disable_functions`، `open_basedir`، Suhosin، لودرهای ionCube/SourceGuardian، نسخه‌ی PHP خط فرمان، تابع `mail()`
  - فایل‌سیستم: امکان ساخت فایل/پوشه، تطابق مالکیت، مسیر نشست، پوشه‌ی موقت آپلود، فضای آزاد دیسک، HTTPS
  - **تست‌های عمیق اختیاری** (`?deep=1`): بررسی اتصال زنده به `update.joomla.org` / `downloads.joomla.org` / `extensions.joomla.org`، آزمون واقعی `mod_rewrite`/`.htaccess`، تفکیک DNS
  - **تست اتصال دیتابیس اختیاری**: با اطلاعاتی که وارد می‌کنید متصل می‌شود، نسخه‌ی MySQL/MariaDB، پشتیبانی از `utf8mb4`، مجوزهای `CREATE`/`ALTER`/`DROP` و فعال بودن InnoDB را بررسی می‌کند — هیچ اطلاعاتی جایی ذخیره یا ارسال نمی‌شود
- **امتیاز آمادگی وزن‌دار** (۰ تا ۱۰۰٪) همراه با نتیجه‌گیری شفاف: «آماده‌ی نصب نیست»، «نصب می‌شود اما ناقص»، «آماده با نکات قابل بهبود» یا «کاملاً آماده».
- **متن آماده برای تیکت پشتیبانی**، به‌صورت خودکار از روی موارد مردود/هشدار ساخته می‌شود و با یک کلیک کپی می‌شود.
- **دکمه‌ی خودحذفی** — پس از پایان کار، اسکریپت را از روی سرور پاک می‌کند (توصیه: هرگز این فایل را برای مدت طولانی در دسترس عموم نگذارید). این دکمه با یک توکن CSRF محافظت می‌شود تا از طریق یک سایت دیگر قابل فراخوانی نباشد.
- خروجی **چاپ / PDF** از کل گزارش.
- **قفل دسترسی، به‌صورت پیش‌فرض فعال** — یا خودتان `NVD_ACCESS_KEY` را تنظیم کنید، یا خالی بگذارید تا ابزار در اولین اجرا یک کلید تصادفی بسازد، آن را در فایل `.nvd6-lock.php` کنار اسکریپت ذخیره کند و از آن پس در هر درخواست `?key=...` را الزامی کند.
- فرم تست دیتابیس هم با توکن CSRF محافظت می‌شود، و بررسی نسخه‌ی PHP خط فرمان (که یک فرمان shell اجرا می‌کند) فقط در حالت «تست‌های عمیق» انجام می‌شود، نه در هر بار بارگذاری صفحه.

## 🚀 روش استفاده

۱) فایل [`joomla6check.php`](joomla6check.php) را دانلود و در پوشه‌ی اصلی هاست (مثلاً `public_html`) آپلود کنید.
۲) در مرورگر باز کنید:
```
https://your-domain.com/joomla6check.php
```
۳) گزارش را بخوانید. هر زمان با دکمه‌های **فارسی / English** یا از طریق آدرس (`?lang=fa` / `?lang=en`) زبان را تغییر دهید.
۴) در صورت تمایل، تست‌های عمیق شبکه و `mod_rewrite` را اجرا کنید: `?deep=1`.
۵) در صورت تمایل، از فرم داخل صفحه برای تست اتصال دیتابیس استفاده کنید.
۶) اگر موردی نیاز به اصلاح دارد، متن آماده‌ی تیکت را کپی و برای پشتیبانی هاست ارسال کنید.
۷) **در پایان کار، حتماً روی «حذف این فایل از سرور» بزنید** (یا دستی حذف کنید). هرگز چنین اسکریپت تشخیصی را برای مدت طولانی در دسترس عموم نگذارید.

### قفل کردن گزارش

گزارش به‌صورت پیش‌فرض قفل است. اگر `NVD_ACCESS_KEY` را خالی بگذارید، ابزار در اولین اجرا یک کلید تصادفی می‌سازد و آن را در فایل `.nvd6-lock.php` کنار همان پوشه ذخیره می‌کند؛ همان پاسخ اول، کلید و یک لینک آماده با `?key=...` را نشان می‌دهد — آن را ذخیره کنید چون دیگر نمایش داده نمی‌شود. هر درخواست بعدی به همین کلید نیاز دارد.

> روی هاستی که ممکن است دیگران زودتر از شما آدرس را باز کنند، به‌جای تکیه بر کلید خودکار، از قبل کلید را دستی تنظیم کنید:

```php
define('NVD_ACCESS_KEY', 'کلیدی که فقط خودتان می‌دانید');
```

از این پس گزارش فقط با افزودن `?key=کلید-شما` به آدرس باز می‌شود. اگر پوشه قابل نوشتن نباشد و کلیدی هم تنظیم نشده باشد، ابزار بدون قفل ادامه می‌دهد و در هر بار بارگذاری یک هشدار برای تنظیم دستی کلید نشان می‌دهد.

## 🖥 پیش‌نیازها

- PHP نسخه‌ی ۵.۶ یا بالاتر روی سروری که می‌خواهید بررسی کنید (دقیقاً همین موضوع را برایتان گزارش می‌دهد اگر نسخه قدیمی باشد).
- برای *اجرای* خودِ ابزار نیازی به افزونه‌ی خاصی نیست؛ در نبود هر افزونه، به‌سادگی همان را در گزارش اعلام می‌کند.

## 🌍 افزودن زبان جدید

تمام متن‌های قابل‌مشاهده از طریق یک تابع کمکی ساده عبور می‌کنند:

```php
T('متن فارسی', 'English text');          // رشته‌ی ثابت
T('حداقل %s', 'Minimum %s', array($v));  // با جای‌گذاری به سبک vsprintf
```

برای افزودن زبان سوم، تابع `T()` را گسترش دهید (مثلاً با پذیرفتن کد زبان و یک جدول ترجمه) و لینک تعویض زبان متناظر را در سربرگ اضافه کنید. مشارکت شما خوش‌آمد است.

## 🤝 مشارکت

ایشو و Pull Request خوش‌آمد است. لطفاً فایل را بدون وابستگی خارجی و سازگار با PHP 5.6 نگه دارید.

## 📄 لایسنس

منتشرشده تحت [مجوز MIT](LICENSE).

## 🏢 درباره‌ی ما

**شرکت نوید ایرانیان (Navid Iranian Co.)** — طراحی وب‌سایت، سئو، میزبانی وب، ثبت دامنه و دیجیتال مارکتینگ، با پشتیبانی فارسی و عربی برای بازار ایران و عراق.
🌐 [navidiranian.com](https://navidiranian.com/) · [navidiranian.co.ir](https://navidiranian.co.ir/) · [joomlafarsi.co.ir](https://joomlafarsi.co.ir/) · [cmssupport.ir](https://cmssupport.ir/)
📞 [۰۹۳۹ ۵۵۶ ۶۶۵۲](tel:+989395566652) · [۰۲۱ ۹۱۳۰ ۳۶۶۲](tel:+982191303662)
