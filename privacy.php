<?php
const STORAGE_DIR = __DIR__ . '/files';
const STATS_FILE = STORAGE_DIR . '/stats.json';

function ensureStorageDir(): void
{
    if (!is_dir(STORAGE_DIR)) {
        mkdir(STORAGE_DIR, 0775, true);
    }
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

function getAverageBytesPerDay(): int
{
    $stats = loadStats();
    $totalBytes = (int) $stats['uploaded_bytes'] + (int) $stats['downloaded_bytes'];
    $createdAt = max(1, (int) $stats['created_at']);
    $days = max(1, (int) ceil((time() - $createdAt + 1) / 86400));

    return (int) floor($totalBytes / $days);
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
</head>
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
      </td>
    </tr>
    <tr> 
        <td colspan="2" valign="top" class="Page">
    
    
    
          <font size="5" color="#84B6CE"><b>Privacy Policy</b></font>
          <hr>
          <table width="100%" border="0" cellpadding="0" cellspacing="0" class="Content">
            <tbody><tr> 
              <td><font size="2" color="#426984"><b>Logging IP Addresses</b></font>
                <p>For each visitor to our Web page, our Web server does not recognize 
                  any information regarding the domain or e-mail address. IP addresses 
                  are logged for measuring usage and for no other purpose.</p>
                <font size="2" color="#426984"><b>Collecting Email Addresses</b></font>
                <p>We collect the e-mail addresses of YouSendIt Delivery recipients 
                  solely for the purpose of logging and measuring usage. Personal 
                  user information we collect is not shared with other organizations 
                  for any reason.</p>
                <font size="2" color="#426984"><b>Use of Cookies</b></font>
                <p>We use cookies only to store the preferences of subscribed users, 
                  only if they so choose.</p>
                <font size="2" color="#426984"><b>File Transfer Security</b></font>
                <p>We have appropriate security measures in place in our physical 
                  facilities to protect against the loss, misuse or alteration of 
                  information that we have collected from you at our site. Files stored 
                  for delivery are only accessible by YouSendIt and through the clickable 
                  link generated for your recipient. All files stored for delivery 
                  are deleted when they expire.</p>
                <p>If you feel that this site is not following its stated information 
                  policy, you may contact us at the above addresses or phone number. 
                  Your privacy is important to us and we work hard to protect it.</p>
                </td>
            </tr>
          </tbody></table>
          <p class="PageNav">« <a href="javascript: history.back()" class="PageNav">Back</a></p>
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
