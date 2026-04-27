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
            <font size="5" color="#84B6CE"><b>How Does It Work?</b></font><hr>
            <font size="2" color="#426984"><b>New to YouSendIt? Here's what you do.</b></font>
            <table width="495" border="0" align="center" cellpadding="4" cellspacing="1">
              <tbody><tr align="left" valign="middle"> 
                <td nowrap=""> <img src="images/step_1.gif" width="50" height="50" border="0"> 
                </td>
                <td width="100%">Please enter the text from the captcha image, this is to protect against spam.</td>
              </tr>
              <tr align="left" valign="middle"> 
                <td nowrap=""> <img src="images/step_2.gif" width="50" height="50" border="0"> 
                </td>
                <td width="100%"> Select a file to send. You can send photos, audio, 
                  documents or anything else. Your file will be stored by YouSendIt 
                  without ever filling up your recipient's mailbox. </td>
              </tr>
              <tr align="left" valign="middle"> 
                <td nowrap=""> <img src="images/step_3.gif" width="50" height="50" border="0"> 
                </td>
                <td width="100%">Click on Send. YouSendIt will automatically email your 
                  recipient a link to your file stored on our server. </td>
              </tr>
              <tr align="left" valign="middle"> 
                <td nowrap="">&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr align="left" valign="middle"> 
                <td colspan="2">No passwords to share, no software to install, no accounts 
                  to create, and no full mailboxes. <a href="index.php">Start sending 
                  now</a>!</td>
              </tr>
            </tbody></table>
            <p><b>« <a href="javascript: history.back()" class="PageNav">Back</a></b></p>
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
