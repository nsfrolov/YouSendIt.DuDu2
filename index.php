<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

const MAX_UPLOAD_SIZE = 104857600;
const STORAGE_DIR = __DIR__ . '/files';
const HASH_INDEX_FILE = STORAGE_DIR . '/hash_index.json';
const STATS_FILE = STORAGE_DIR . '/stats.json';

function ensureStorageDir(): void
{
    if (!is_dir(STORAGE_DIR)) {
        mkdir(STORAGE_DIR, 0775, true);
    }
}

function createFileId(int $length = 8): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $max = strlen($alphabet) - 1;
    $id = '';

    for ($i = 0; $i < $length; $i++) {
        $id .= $alphabet[random_int(0, $max)];
    }

    return $id;
}

function normalizeExtension(string $originalName): string
{
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $ext = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $ext));
    return substr($ext, 0, 20);
}

function loadHashIndex(): array
{
    if (!is_file(HASH_INDEX_FILE)) {
        return [];
    }

    $raw = file_get_contents(HASH_INDEX_FILE);
    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function saveHashIndex(array $index): void
{
    file_put_contents(HASH_INDEX_FILE, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function loadStats(): array
{
    ensureStorageDir();
    if (!is_file(STATS_FILE)) {
        return [
            'created_at' => time(),
            'uploaded_bytes' => 0,
            'downloaded_bytes' => 0,
        ];
    }

    $raw = file_get_contents(STATS_FILE);
    if ($raw === false || $raw === '') {
        return [
            'created_at' => time(),
            'uploaded_bytes' => 0,
            'downloaded_bytes' => 0,
        ];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [
            'created_at' => time(),
            'uploaded_bytes' => 0,
            'downloaded_bytes' => 0,
        ];
    }

    return [
        'created_at' => isset($decoded['created_at']) ? (int) $decoded['created_at'] : time(),
        'uploaded_bytes' => isset($decoded['uploaded_bytes']) ? (int) $decoded['uploaded_bytes'] : 0,
        'downloaded_bytes' => isset($decoded['downloaded_bytes']) ? (int) $decoded['downloaded_bytes'] : 0,
    ];
}

function saveStats(array $stats): void
{
    file_put_contents(STATS_FILE, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function addStatsBytes(string $type, int $bytes): void
{
    if ($bytes <= 0) {
        return;
    }

    $stats = loadStats();
    if ($type === 'upload') {
        $stats['uploaded_bytes'] += $bytes;
    } elseif ($type === 'download') {
        $stats['downloaded_bytes'] += $bytes;
    }

    saveStats($stats);
}

function checkModRewrite(): bool
{
    // Use the configuration setting if defined, otherwise auto-detect
    if (defined('USE_MOD_REWRITE')) {
        return USE_MOD_REWRITE;
    }
    
    // Fallback to auto-detection if constant is not defined
    if (!function_exists('apache_get_modules')) {
        // Can't check, assume it works if .htaccess exists
        return file_exists(__DIR__ . '/.htaccess');
    }
    
    return in_array('mod_rewrite', apache_get_modules());
}

function generateDownloadLink(string $id): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER["HTTP_HOST"];
    
    // Use short URLs if mod_rewrite is available, otherwise use query string
    if (checkModRewrite()) {
        return $protocol . '://' . $host . '/' . $id;
    } else {
        return $protocol . '://' . $host . '/?d=' . $id;
    }
}

function getAverageBytesPerDay(): int
{
    $stats = loadStats();
    $totalBytes = (int) $stats['uploaded_bytes'] + (int) $stats['downloaded_bytes'];
    $createdAt = max(1, (int) $stats['created_at']);
    $days = max(1, (int) ceil((time() - $createdAt + 1) / 86400));

    return (int) floor($totalBytes / $days);
}

function outputCaptchaImage(string $text): void
{
    $width = 120;
    $height = 36;
    $image = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($image, 248, 248, 248);
    imagefilledrectangle($image, 0, 0, $width, $height, $bg);

    for ($i = 0; $i < 9; $i++) {
        $lineColor = imagecolorallocate(
            $image,
            random_int(100, 220),
            random_int(100, 220),
            random_int(100, 220)
        );
        imageline(
            $image,
            random_int(0, $width),
            random_int(0, $height),
            random_int(0, $width),
            random_int(0, $height),
            $lineColor
        );
    }

    for ($i = 0; $i < 500; $i++) {
        $dotColor = imagecolorallocate(
            $image,
            random_int(120, 255),
            random_int(120, 255),
            random_int(120, 255)
        );
        imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $dotColor);
    }

    $chars = str_split($text);
    $x = 10;
    foreach ($chars as $char) {
        $textColor = imagecolorallocate(
            $image,
            random_int(0, 120),
            random_int(0, 120),
            random_int(0, 120)
        );
        $y = random_int(9, 16);
        imagestring($image, 4, $x, $y, $char, $textColor);
        imagestring($image, 4, $x + 1, $y, $char, $textColor);
        $x += random_int(21, 24);
    }

    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    imagepng($image);
    imagedestroy($image);
}

function serveDownload(string $id, string $ext = ''): void
{
    ensureStorageDir();

    $id = preg_replace('/[^A-Za-z0-9]/', '', $id) ?? '';

    if ($id === '') {
        http_response_code(404);
        exit('File not found.');
    }

    // Always look for .attach file
    $path = STORAGE_DIR . '/' . $id . '.attach';

    if (!is_file($path)) {
        http_response_code(404);
        exit('File not found.');
    }

    $fileSize = filesize($path);
    if ($fileSize === false) {
        http_response_code(500);
        exit('Cannot read file size.');
    }

    // Try to get original filename from hash index
    $originalName = $id . '.file';
    $hashIndex = loadHashIndex();
    foreach ($hashIndex as $hash => $data) {
        if (is_array($data) && isset($data['id']) && $data['id'] === $id) {
            if (isset($data['original_name']) && $data['original_name'] !== '') {
                $originalName = $data['original_name'];
            } elseif (isset($data['ext']) && $data['ext'] !== '') {
                $originalName = $id . '.' . $data['ext'];
            }
            break;
        }
    }

    // Clean filename for safe download - preserve Unicode characters
    $downloadName = basename($originalName);
    // Remove only dangerous characters (path traversal, null bytes) but preserve Unicode
    $downloadName = preg_replace('/[\x00\/\\\\\:\*\?\"\<\>\|]/', '_', $downloadName) ?? $downloadName;
    // Limit length to prevent issues
    if (mb_strlen($downloadName, 'UTF-8') > 255) {
        $namePart = mb_substr($downloadName, 0, 250, 'UTF-8');
        $extPart = pathinfo($downloadName, PATHINFO_EXTENSION);
        $downloadName = $namePart . ($extPart ? '.' . $extPart : '');
    }

    // Security headers to prevent browser warnings and mixed content issues
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    
    // Browser compatibility for filenames:
    // IE 5/6: Very limited Unicode support, will show what it can
    // IE 7+: Better UTF-8 support
    // Modern browsers: Full UTF-8 via filename* parameter
    
    // Sanitize filename - remove only dangerous characters
    $safeName = str_replace(['"', '\\'], '_', $downloadName);
    
    // Truncate to safe byte length (255 bytes max for filenames)
    if (strlen($safeName) > 255) {
        $safeName = substr($safeName, 0, 250);
        // Ensure we don't cut in the middle of a UTF-8 character
        while (strlen($safeName) > 0 && (ord($safeName[strlen($safeName) - 1]) & 0xC0) === 0x80) {
            $safeName = substr($safeName, 0, -1);
        }
    }
    
    // RFC 5987 UTF-8 encoding for modern browsers
    $utf8Encoded = rawurlencode($downloadName);
    
    // Send both formats for maximum compatibility
    header('Content-Disposition: attachment; filename="' . $safeName . '"; filename*=UTF-8\'\'' . $utf8Encoded);
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: private, must-revalidate, max-age=0');
    header('Pragma: private');
    header('Content-Length: ' . $fileSize);
    
    // Security headers to prevent mixed content warnings
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    addStatsBytes('download', (int) $fileSize);
    
    // Stream file in chunks for better performance
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        http_response_code(500);
        exit('Cannot read file.');
    }
    
    while (!feof($handle)) {
        echo fread($handle, 8192);
        flush();
    }
    fclose($handle);
    exit;
}

function serveFileInfo(string $id): void
{
    ensureStorageDir();
    
    $id = preg_replace('/[^A-Za-z0-9]/', '', $id) ?? '';
    
    if ($id === '') {
        http_response_code(404);
        exit('File not found.');
    }
    
    // Always look for .attach file
    $path = STORAGE_DIR . '/' . $id . '.attach';
    
    if (!is_file($path)) {
        http_response_code(404);
        exit('File not found.');
    }
    
    $fileSize = filesize($path);
    if ($fileSize === false) {
        http_response_code(500);
        exit('Cannot read file size.');
    }
    
    // Try to get original filename and description from hash index
    $originalName = $id . '.file';
    $fileDescription = '';
    $hashIndex = loadHashIndex();
    foreach ($hashIndex as $hash => $data) {
        if (is_array($data) && isset($data['id']) && $data['id'] === $id) {
            if (isset($data['original_name']) && $data['original_name'] !== '') {
                $originalName = $data['original_name'];
            } elseif (isset($data['ext']) && $data['ext'] !== '') {
                $originalName = $id . '.' . $data['ext'];
            }
            if (isset($data['description']) && $data['description'] !== '') {
                $fileDescription = $data['description'];
            }
            break;
        }
    }
    
    // Clean filename
    $downloadName = basename($originalName);
    $downloadName = preg_replace('/[\x00\/\\\\\:\*\?\"\<\>\|]/', '_', $downloadName) ?? $downloadName;
    
    // Format file size
    if ($fileSize >= 1073741824) {
        $formattedSize = number_format($fileSize / 1073741824, 2) . ' GB';
    } elseif ($fileSize >= 1048576) {
        $formattedSize = number_format($fileSize / 1048576, 2) . ' MB';
    } elseif ($fileSize >= 1024) {
        $formattedSize = number_format($fileSize / 1024, 2) . ' KB';
    } else {
        $formattedSize = $fileSize . ' bytes';
    }
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER["HTTP_HOST"];
    
    // Generate download URL based on mod_rewrite availability
    if (checkModRewrite()) {
        $downloadUrl = $protocol . '://' . $host . '/' . $id . '?download';
    } else {
        $downloadUrl = $protocol . '://' . $host . '/?d=' . $id . '&download';
    }
    
    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Download: <?php echo htmlspecialchars($downloadName, ENT_QUOTES, 'UTF-8'); ?> - YouSendIt</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="style.css" rel="stylesheet" type="text/css">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<body id="body1">
<div id="formLayer" style="left: 0px; overflow: visible; position: relative; top: 0px; width: 100%; height: 100%;">
  <table width="740" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="Page">
    <tr>
      <td height="25" colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="HeaderUtilities">
          <tr>
            <td width="100%"><img src="images/utilities_left.gif" width="100%" height="25" alt=""></td>
            <td><img src="images/utilities_separator.gif" width="25" height="25" alt=""></td>
            <td nowrap="nowrap" class="HeaderUtilities"><a href="http://www.dudu2.ru/">Join DuDu2.ru!</a></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td height="50" colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="HeaderNav">
          <tr>
            <td width="220"><a href="/"><img src="images/logo.gif" height="50" border="0" alt="YouSendIt"></a></td>
            <td width="100%" class="HeaderNav"><a href="/" class="HeaderNav">Home</a> | <a href="solutions.php" class="HeaderNav">Solutions</a> | <a href="mailto:dsc@w10.site" class="HeaderNav">Contact Us</a></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td colspan="2" valign="top">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/feature_bg.gif">
          <tr>
            <td align="center"><img src="images/feature.gif" width="490" height="40" alt=""></td>
          </tr>
        </table>
        <div align="right"><span class="Subscript"><a href="howdoesitwork.php">How does it work</a><br>
	        <a href="whyyousendit.php">Why YouSendIt</a></div>
      </td>
    </tr>
    <tr>
      <td colspan="2" valign="top" class="Page">
        <table width="495" border="0" align="center" cellpadding="4" cellspacing="1">
          <tr align="left" valign="middle">
            <td nowrap="nowrap">&nbsp;</td>
            <td class="Instructions">
              <h2 style="margin: 0; padding: 10px 0;">File Ready for Download</h2>
            </td>
          </tr>
          <tr align="left" valign="top">
            <td nowrap="nowrap">&nbsp;</td>
            <td width="100%" class="Label">
              <span class="Instructions">File information:</span>
              <br><br>
              <table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#f0f0f0">
                <tr>
                  <td>
                    <b>Filename:</b> <?php echo htmlspecialchars($downloadName, ENT_QUOTES, 'UTF-8'); ?><br><br>
                    <b>File Size:</b> <?php echo htmlspecialchars($formattedSize, ENT_QUOTES, 'UTF-8'); ?><br><br>
                    <?php if ($fileDescription !== ''): ?>
                    <b>Description:</b> <?php echo nl2br(htmlspecialchars($fileDescription, ENT_QUOTES, 'UTF-8')); ?><br><br>
                    <?php endif; ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr align="left" valign="middle">
            <td nowrap="nowrap">&nbsp;</td>
            <td width="100%" class="Label">
              <span class="Instructions">Click the button below to download your file:</span>
              <br><br>
              <form method="post" action="<?php echo htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="submit" name="DownloadFile" value="Download File">
              </form>
            </td>
          </tr>
          <tr align="left" valign="middle">
            <td nowrap="nowrap">&nbsp;</td>
            <td width="100%">
              <span class="Instructions">
                <a href="/" class="Footer">← Back to homepage</a>
              </span>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td width="444" class="Footer"><a href="/" class="Footer">YouSendIt</a> © 2026 | <a class="Footer" href="privacy.php">Privacy Policy</a> | <a class="Footer" href="terms.php">Terms of Service</a></td>
      <td width="296" class="Footer"><div align="right">Transferring over <?php echo number_format(getAverageBytesPerDay(), 0, '.', ','); ?> bytes per day</div></td>
    </tr>
  </table>
</div>
</body>
</html>
    <?php
    exit;
}

// Handle download requests
if (isset($_GET['d'])) {
    // If ?download parameter is present, serve the file directly
    if (isset($_GET['download'])) {
        serveDownload((string) $_GET['d']);
    } else {
        // Otherwise show file info page
        serveFileInfo((string) $_GET['d']);
    }
}

$message = '';
$error = '';
$link = '';
$emailSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientEmail = trim((string) ($_POST['recipient_email'] ?? ''));
    $fileDescription = trim((string) ($_POST['file_description'] ?? ''));
    
    if (!isset($_FILES['LoadFileName']) || $_FILES['LoadFileName']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Select file and try again.';
    } elseif ((int) $_FILES['LoadFileName']['size'] > MAX_UPLOAD_SIZE) {
        $error = 'File too large. Max size is 100 MB.';
    } elseif ($recipientEmail !== '' && !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        ensureStorageDir();
        $originalName = (string) $_FILES['LoadFileName']['name'];
        $uploadSize = (int) $_FILES['LoadFileName']['size'];
        $ext = normalizeExtension($originalName);
        $tmpFile = (string) $_FILES['LoadFileName']['tmp_name'];
        $fileHash = hash_file('sha256', $tmpFile);

        if ($fileHash === false) {
            $error = 'Cannot calculate file hash.';
        } else {
            $hashIndex = loadHashIndex();
            $known = $hashIndex[$fileHash] ?? null;

            if (is_array($known) && isset($known['id'])) {
                $existingId = preg_replace('/[^A-Za-z0-9]/', '', (string) $known['id']) ?? '';
                $existingPath = STORAGE_DIR . '/' . $existingId . '.attach';

                if ($existingId !== '' && is_file($existingPath)) {
                    $publicLink = generateDownloadLink($existingId);
                    $message = 'Your file uploaded successfully.';
                    $link = $publicLink;
                    addStatsBytes('upload', $uploadSize);
                    
                    // Send email with download link if email is provided
                    if ($recipientEmail !== '') {
                        $emailSubject = 'Your file is ready for download';
                        $emailBody = '<html><body>';
                        $emailBody .= '<div style="text-align: center; margin-bottom: 20px;">';
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $emailBody .= '<img src="' . $protocol . '://' . $_SERVER["HTTP_HOST"] . '/images/sending_logo.gif" alt="YouSendIt" style="max-width: 300px;">';
                        $emailBody .= '</div>';
                        $emailBody .= '<h2>Your file has been uploaded successfully!</h2>';
                        $emailBody .= '<p><strong>Filename:</strong> ' . htmlspecialchars($originalName, ENT_QUOTES, 'UTF-8') . '</p>';
                        $emailBody .= '<p><strong>Description:</strong> ' . htmlspecialchars($fileDescription, ENT_QUOTES, 'UTF-8') . '</p>';
                        $emailBody .= '<p>Click the link below to download your file:</p>';
                        $emailBody .= '<p><a href="' . htmlspecialchars($publicLink, ENT_QUOTES, 'UTF-8') . '">Download File</a></p>';
                        $emailBody .= '<p>Or copy this link: ' . htmlspecialchars($publicLink, ENT_QUOTES, 'UTF-8') . '</p>';
                        $emailBody .= '</body></html>';
                        
                        if (send_smtp_email_advanced($recipientEmail, 'Recipient', $emailSubject, $emailBody, true)) {
                            $emailSent = true;
                        }
                    }
                    
                    // Clear form fields after successful upload
                    $recipientEmail = '';
                    $fileDescription = '';
                } else {
                    unset($hashIndex[$fileHash]);
                    saveHashIndex($hashIndex);
                }
            }
        }

        if ($link === '' && $error === '') {
            do {
                $id = createFileId(12);
                $target = STORAGE_DIR . '/' . $id . '.attach';
            } while (file_exists($target));

            if (move_uploaded_file($tmpFile, $target)) {
                $publicLink = generateDownloadLink($id);
                $message = 'Your file uploaded successfully.';
                $link = $publicLink;
                addStatsBytes('upload', $uploadSize);

                if ($fileHash !== false) {
                    $hashIndex = loadHashIndex();
                    $hashIndex[$fileHash] = [
                        'id' => $id,
                        'ext' => $ext,
                        'original_name' => $originalName,
                        'description' => $fileDescription
                    ];
                    saveHashIndex($hashIndex);
                }
                
                // Send email with download link if email is provided
                if ($recipientEmail !== '') {
                    $emailSubject = 'Your file is ready for download';
                    $emailBody = '<html><body>';
                    $emailBody .= '<div style="text-align: center; margin-bottom: 20px;">';
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $emailBody .= '<img src="' . $protocol . '://' . $_SERVER["HTTP_HOST"] . '/images/sending_logo.gif" alt="YouSendIt" style="max-width: 300px;">';
                    $emailBody .= '</div>';
                    $emailBody .= '<h2>Your file has been uploaded successfully!</h2>';
                    $emailBody .= '<p><strong>Filename:</strong> ' . htmlspecialchars($originalName, ENT_QUOTES, 'UTF-8') . '</p>';
                    $emailBody .= '<p><strong>Description:</strong> ' . htmlspecialchars($fileDescription, ENT_QUOTES, 'UTF-8') . '</p>';
                    $emailBody .= '<p>Click the link below to download your file:</p>';
                    $emailBody .= '<p><a href="' . htmlspecialchars($publicLink, ENT_QUOTES, 'UTF-8') . '">Download File</a></p>';
                    $emailBody .= '<p>Or copy this link: ' . htmlspecialchars($publicLink, ENT_QUOTES, 'UTF-8') . '</p>';
                    $emailBody .= '</body></html>';
                    
                    if (send_smtp_email_advanced($recipientEmail, 'Recipient', $emailSubject, $emailBody, true)) {
                        $emailSent = true;
                    }
                }
                
                // Clear form fields after successful upload
                $recipientEmail = '';
                $fileDescription = '';
            } else {
                $error = 'Cannot save uploaded file.';
            }
        }
    }
}

$averageBytesPerDay = getAverageBytesPerDay();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>YouSendIt | Email large files quickly, securely, and easily!</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="style.css" rel="stylesheet" type="text/css">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<script>
function refreshCaptchaImage() {
    var img = document.getElementById('captchaImage');
    if (!img) {
        return;
    }
    img.src = '/?captcha=1&v=' + new Date().getTime();
}
</script>
<body id="body1">
<div id="formLayer" style="left: 0px; overflow: visible; position: relative; top: 0px; width: 100%; height: 100%;">
  <table width="740" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="Page">
    <tr>
      <td height="25" colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="HeaderUtilities">
          <tr>
            <td width="100%"><img src="images/utilities_left.gif" width="100%" height="25" alt=""></td>
            <td><img src="images/utilities_separator.gif" width="25" height="25" alt=""></td>
            <td nowrap="nowrap" class="HeaderUtilities"><a href="http://www.dudu2.ru/">Join DuDu2.ru!</a></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td height="50" colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0" class="HeaderNav">
          <tr>
            <td width="220"><a href="/"><img src="images/logo.gif" height="50" border="0" alt="YouSendIt"></a></td>
            <td width="100%" class="HeaderNav"><a href="/" class="HeaderNav">Home</a> | <a href="solutions.php" class="HeaderNav">Solutions</a> | <a href="mailto:dsc@w10.site" class="HeaderNav">Contact Us</a></td>
          </tr>
        </table></td>
    </tr>
    <tr>
      <td colspan="2" valign="top">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/feature_bg.gif">
          <tr>
            <td align="center"><img src="images/feature.gif" width="490" height="40" alt=""></td>
          </tr>
        </table>
        <div align="right"><span class="Subscript"><a href="howdoesitwork.php">How does it work</a><br>
	        <a href="whyyousendit.php">Why YouSendIt</a></div>
      </td>
    </tr>
    <tr>
      <td colspan="2" valign="top" class="Page">
        <form name="yousendit" method="post" action="/" id="yousendit" enctype="multipart/form-data">
          <table width="495" border="0" align="center" cellpadding="4" cellspacing="1">
            <tr align="left" valign="middle">
              <td nowrap="nowrap">&nbsp;</td>
              <td class="Instructions">
              Enter your recipient's email address, choose a file to store on YouSendIt server, click on Send It button to send a link. Your privacy is guaranteed. 
              </td>
            </tr>
            <tr align="left" valign="top">
              <td nowrap="nowrap"><img src="images/step_1.gif" width="50" height="50" alt=""></td>
              <td width="100%" class="Label">
                <span class="Instructions">Recipient's Email Address (Optional):</span>
                <br><input name="recipient_email" type="text" style="WIDTH: 70%;" value="<?php echo isset($_POST['recipient_email']) ? htmlspecialchars($_POST['recipient_email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <br><br>
                <span class="Instructions">Message to Recipient (Optional):</span>
                <br><textarea name="file_description" style="WIDTH: 70%; HEIGHT: 100px;"><?php echo isset($_POST['file_description']) ? htmlspecialchars($_POST['file_description'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
              </td>
            </tr>
            <tr align="left" valign="middle">
              <td nowrap="nowrap"><img src="images/step_2.gif" width="50" height="50" alt=""></td>
              <td width="100%" class="Label">
                Select File to Send (Up to 100 MB):
                <br><input name="LoadFileName" id="LoadFileName" type="file" style="WIDTH: 70%">
              </td>
            </tr>
            <tr align="left" valign="middle">
              <td nowrap="nowrap"><img src="images/step_3.gif" width="50" height="50" alt=""></td>
              <td width="100%">
                <input src="images/send_it.gif" name="UploadFile" id="UploadFile" type="image" alt="Send It">
              </td>
            </tr>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $link === '' && $error === ''): ?>
            <tr align="left" valign="middle">
              <td colspan="2" nowrap="nowrap">
                <div align="center">
                  <img src="images/sending_progress.gif" alt="Uploading...">
                </div>
              </td>
            </tr>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
            <tr align="left" valign="middle">
              <td colspan="2" nowrap="nowrap"><div align="center"><b style="color:#b30000;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></b></div></td>
            </tr>
            <?php endif; ?>
            <?php if ($message !== '' && $link !== ''): ?>
            <tr align="left" valign="middle">
              <td colspan="2" nowrap="nowrap"><div align="center"><b><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?><br><?php if ($emailSent): ?>Download link has been sent to the email address.<?php elseif ($recipientEmail === ''): ?>File uploaded successfully. Copy the link below to share.<?php else: ?>Failed to send email notification.<?php endif; ?><br>Link: <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?></a></b></div></td>
            </tr>
            <?php endif; ?>
          </table>
        </form>
      </td>
    </tr>
    <tr>
      <td width="444" class="Footer"><a href="/" class="Footer">YouSendIt</a> © 2026 | <a class="Footer" href="privacy.php">Privacy Policy</a> | <a class="Footer" href="terms.php">Terms of Service</a></td>
      <td width="296" class="Footer"><div align="right">Transferring over <?php echo number_format($averageBytesPerDay, 0, '.', ','); ?> bytes per day</div></td>
    </tr>
  </table>
</div>
</body>
</html>
