<?php
// -------------------------------------------------------------------------------------------------
// Настройка Mailer.
// -------------------------------------------------------------------------------------------------

define('SMTP_HOST', 'smtp.mail.ru');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl'); // 'ssl', 'tls', 'none'
define('SMTP_USERNAME', 'mail@dudu2.ru');
define('SMTP_PASSWORD', 'QYDASG1DT3J5p25bQZWd');
define('SMTP_FROM_EMAIL', 'mail@dudu2.ru');
define('SMTP_FROM_NAME', 'YouSendIt.ДуДу2');

// -------------------------------------------------------------------------------------------------
// Настройка URL (ЧПУ).
// -------------------------------------------------------------------------------------------------
// true  - использовать короткие ссылки (http://site.com/bK86dG3KGM8L)
//         Требуется настроенный .htaccess с mod_rewrite
// false - использовать обычные ссылки (http://site.com/?d=bK86dG3KGM8L)
//         Работает без mod_rewrite на любом сервере
define('USE_MOD_REWRITE', false);

