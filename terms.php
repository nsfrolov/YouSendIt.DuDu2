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
    <td colspan="2" valign="top" class="Page">
      
      <font size="5" color="#84B6CE"><b>Terms of Service</b></font><hr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0" class="Content">
        <tbody><tr> 
          <td><p>This YouSendIt Service Agreement (the "Agreement") 
              describes the terms and conditions on which YouSendIt ("we" 
              or "our company") offer services to you ("Customer" 
              or "You"). By registering for or using YouSendIt services, 
              Customer agrees to be bound by the following terms and conditions. 
            </p>
            <font size="2" color="#426984"><b>1. Eligibility for YouSendIt Services.</b></font>
            <p>Our Services are available only to individuals and business entities 
              (including but not limited to sole proprietorships) in good legal 
              standing that can form legally binding contracts under applicable 
              law. Customer hereby represents and warrants that it is duly licensed 
              to do business and is in good legal standing in the jurisdictions 
              in which it does business (during the term of this Agreement) that 
              it is not a competitor of YouSendIt, and that the person agreeing 
              to this Agreement for Customer is at least eighteen years of age 
              and otherwise capable of and authorized to enter binding contracts 
              for Customer. </p>
            <font size="2" color="#426984"><b>2. YouSendIt Services. </b></font>
            <p>Subject to the terms and conditions of this Agreement, YouSendIt 
              makes certain Services available to Customer. For the purposes of 
              this Agreement: (a) "Customer" (or "you") means 
              the individual or business entity that is using or registering to 
              use the Services, including its employees and agents; (b) YouSendIt 
              "Services" means those electronic or interactive services 
              offered by YouSendIt. YouSendIt Online reserves the right to change 
              or discontinue any of the Services at any time. </p>
            <font size="2" color="#426984"><b>3. Ownership</b></font>
            <p>You acknowledge that all materials provided on this Web site, including 
              but not limited to information, documents, products, logos, graphics, 
              sounds, images, software, and services (collectively "Materials"), 
              are provided either by YouSendIt or by their respective third party 
              authors, developers and vendors (collectively "Third Party 
              Providers") and the underlying intellectual property rights 
              are owned by YouSendIt and/or its Third Party Providers. Elements 
              of the Web site are protected by trade dress and other laws and 
              may not be copied or imitated in whole or in part. YouSendIt, the 
              YouSendIt logo and other YouSendIt products referenced herein are 
              trademarks of YouSendIt, and may be registered in certain jurisdictions. 
              All other product names, company names, marks, logos, and symbols 
              may be the trademarks of their respective owners.</p>
            <font size="2" color="#426984"><b>4. Customer Information</b></font>
            <p> Customer represents and warrants that the information it provides 
              in YouSendIt contact information forms is true, accurate, current 
              and complete. Customer agrees to maintain and update this information 
              to ensure that it is true, accurate, current and complete. If, at 
              any time, any information provided by Customer is untrue, inaccurate, 
              not current or incomplete, YouSendIt will have the right to suspend 
              or terminate Customer's account and this Agreement.</p>
            <font size="2" color="#426984"><b> 5. Customer Account</b></font>
            <h5>5.1 Authorized Users</h5>
            <p> Customer may designate persons to act as its agents to use the 
              Services, provided that each designated person has the legal capacity 
              to enter into binding contracts for Customer. Furthermore, Customer 
              represents and warrants that each person who registers under Customer's 
              account is an authorized agent of Customer (an "Authorized 
              User") who has such legal capacity.</p>
            <h5> 5.2 Responsibility for Access</h5>
            <p>Customer is solely responsible and liable for any and all access 
              to and use of the Services (including all activities and transactions) 
              by any Authorized User and/or User ID registered under Customer's 
              account, unless such access to or use of the Services is the direct 
              result solely of the gross negligence of YouSendIt. It is Customer's 
              responsibility, through its systems administrator Authorized User, 
              to set the appropriate access for each of Customer's Authorized 
              Users.</p>
            <h5> 5.3 Responsibility for User IDs and Passwords</h5>
            <p>Customer is solely responsible for maintaining the confidentiality 
              of Customer access information, i.e. account ID's and passwords 
              of its Authorized Users, and are responsible for all activities 
              that occur under your account.</p>
            <h5>5.4 Notification of Unauthorized Use</h5>
            <p>Customer will immediately notify YouSendIt if Customer notices 
              any activity indicating that Customer's account or data is being 
              used without authorization, including: (a) Customer has received 
              confirmation of an order or orders placed using Customer's account 
              which Customer did not place or any similar conflicting report; 
              or (b) Customer becomes aware of any unauthorized use of any product 
              or service related to its account(s). </p>
            <font size="2" color="#426984"><b>6. Customer Data</b></font>
            <p>Customer has sole responsibility and liability for the data its 
              stores on YouSendIt's servers. Customer controls its data through 
              its generated link. YouSendIt encourages Customer to archive its 
              data regularly and frequently; Customer bears full responsibility 
              for archiving its data and sole liability for any lost or irrecoverable 
              data. Customer agrees to maintain its data in compliance with its 
              legal obligations. YouSendIt will delete Customer data upon termination 
              of this Agreement. However, YouSendIt may retain Customer data in 
              its archives after deletion and will not be liable to Customer in 
              any way for such retained data.</p>
            <h5>6.1 Special Circumstances</h5>
            <p>YouSendIt will provide access to the Services and Customer's data 
              to an agent of Customer ("Authorized Agent") who provides 
              YouSendIt with a notarized letter signed by an officer of Customer 
              which letter shall include statements of authenticity, authority, 
              and liability as required by YouSendIt in its sole discretion. Customer 
              expressly and irrevocably agrees that YouSendIt may rely on such 
              a letter and on the apparent authority of the person requesting 
              access to the Services or to Customer's account. In no event will 
              YouSendIt be liable to Customer or any third party for YouSendIt's 
              reliance on such letter or such apparent authority.</p>
            <font size="2" color="#426984"><b>8. Acceptable Use</b></font>
            <h5>8.1 Illegality/Adult Content</h5>
            <p>YouSendIt neither sanctions nor permits site content or the transmission 
              of data that contains illegal or obscene material or fosters or 
              promotes illegal activity, including but not limited to, gambling, 
              the offering for sale of illegal weapons, and the promotion or publication 
              of any material that may violate hate crimes legislation.</p>
            <p>YouSendIt reserves the right to immediately suspend or terminate 
              any account or transmission that violates this policy, without prior 
              notice.</p>
            <p>Further, should Customer violate this policy, YouSendIt will actively 
              assist and cooperate with law enforcement agencies and government 
              authorities in collecting and tendering information about Customer, 
              Customer's site, the illegal or obscene content, and those persons 
              that may have inappropriately accessed, acquired, or used the illegal 
              or obscene content.</p>
            <h5>8.2 Wrongful Conduct</h5>
            <p>Customer shall not commit or permit wrongful or damaging acts which 
              justify civil action including, but not limited to, posting of defamatory, 
              scandalous, or private information about a person without their 
              consent or intentionally inflicting emotional distress.</p>
            <h5>8.3 Access and Interference</h5>
            <p>Violations or attempts to violate YouSendIt systems or to interrupt 
              YouSendIt services are strictly prohibited, and may result in criminal 
              and civil liability. Examples of system violations include, without 
              limitation: </p>
            <p>(a) Unauthorized access to or use of YouSendIt Services, including 
              any attempt to probe, scan or test the vulnerability of a system 
              or to breach security or authentication measures without express 
              authorization of YouSendIt; or (b) Interference with Service to 
              any customer or network including, without limitation, flooding, 
              or deliberate attempts to overload a system and broadcast attacks; 
              or (c) Use of any device, software, or routine to interfere or attempt 
              to interfere with the proper working of the Services; or (d) Any 
              action that imposes an unreasonable or disproportionately large 
              load on YouSendIt's infrastructure</p>
            <p>Customer shall not decompile, disassemble, decrypt, extract, reverse 
              engineer or otherwise attempt to derive the source code of the "software 
              tools" (including the tools, methods, processes, and infrastructure) 
              underlying the Services or any other software on the YouSendIt Web 
              site.</p>
            <h5>8.4 Copyright or Trademark Infringement</h5>
            <p>YouSendIt Services may be used only for lawful purposes. Transmission, 
              distribution or storage of any material in violation of any applicable 
              law or regulation, including export control laws, is prohibited. 
              This includes, without limitation, material protected by patent, 
              copyright, trademark, service mark, trade secret or other intellectual 
              property rights. If you use another party's material, you must obtain 
              prior authorization. By using the Services, you represent and warrant 
              that you are the author and copyright owner and/or proper licensee 
              with respect to any hosted content and you further represent and 
              warrant that no content violates the trademark or rights of any 
              third party. YouSendIt reserves the right to suspend or terminate 
              a Customer's transmission(s) that, in YouSendIt's discretion, violates 
              these policies or violates any law or regulation.</p>
            <h5>8.5 Misuse of System Resources</h5>
            <p>Customer shall not misuse system resources including, but not limited 
              to, employing content which consume excessive CPU time or storage 
              space; utilizing excessive bandwidth; or resale of access to content 
              hosted on YouSendIt servers.</p>
            <h5>8.6 Other Activities</h5>
            <p>Whether lawful or unlawful, YouSendIt reserves the right to determine 
              what is harmful to its Customers, operations or reputation, including 
              any activities that restrict or inhibit any other user from using 
              and enjoying the Service or the Internet.</p>
            <p>Please be aware YouSendIt reserves the right to cancel any account 
              or transmission they find in violation of any of the above policies. 
              If appropriate, YouSendIt will refer complaints to law enforcement 
              authorities, and in such case, YouSendIt will actively assist law 
              enforcement agencies with the investigation and prosecution of any 
              such activities, including surrendering Customer account and data 
              information. </p>
            <p>Complaints about violators of our Policy should be sent via e-mail 
              to abuse@yousendit.com. Each complaint will be investigated and 
              may result to immediate cancellation of Services without prior notice.</p>
            <font size="2" color="#426984"><b>9. No Warranty</b></font>
            <p>You expressly understand and agree that: (a) your use of the Service 
              is at your sole risk. YouSendIt Services are provided on an "as 
              is" and "as available" basis. YouSendIt and its suppliers, 
              to the fullest extent permitted by law, disclaim all warranties, 
              including but not limited to warranties of title, fitness for a 
              particular purpose, merchantability and non-infringement of proprietary 
              or third party rights. YouSendIt and its suppliers make no warranties 
              about the accuracy, reliability, completeness, or timeliness of 
              our Services, software, or content; (b) YouSendIt makes no warranty 
              that (i) the Service will meet your requirements, (ii) the service 
              will be uninterrupted, timely, secure, or error-free, (iii) the 
              results that may be obtained from the use of the service will be 
              accurate or reliable, (iv) the quality of any products, services, 
              information, or other material purchased or obtained by you through 
              the Service will meet your expectations, and (v) any errors in the 
              software will be corrected; (d) any material downloaded or otherwise 
              obtained through the use of the Service is done at your own discretion 
              and risk and that you will be solely responsible for any damage 
              to your computer system or loss of data that results from the download 
              of any such material; (e) no advice or information, whether oral 
              or written, obtained by you from us or through or from the Service 
              shall create any warranty not expressly stated in these terms and 
              conditions.</p>
            <font size="2" color="#426984"><b>10. Indemnity</b></font>
            <p>You agree to defend, indemnify, and hold harmless YouSendIt, its 
              affiliates, officers, directors, employees and agents, from and 
              against any claims, actions or demands, including without limitation 
              reasonable legal fees, alleging or resulting from your use the Service, 
              or your breach of this Agreement or other YouSendIt policies, terms 
              and conditions. </p>
            <font size="2" color="#426984"><b>11. Limitation of Liability</b></font>
            <p>Your use of YouSendIt is at your own risk. If you are dissatisfied 
              with any aspect of our Service or with these terms &amp; conditions, 
              or any other rules or policies, your sole remedy is to discontinue 
              use of the Service. You expressly understand and agree that YouSendIt 
              shall not be liable for any direct, indirect, incidental, special, 
              consequential exemplary damages, including but not limited to, damages 
              for loss of profits, goodwill, use, data or other intangible losses 
              (even if we have been advised of the possibility of such damages), 
              resulting from: (i) the use or the inability to use the Service; 
              (ii) the cost of procurement of substitute goods and services resulting 
              from any goods, data, information or services purchased or obtained 
              or messages received or transactions entered into through or from 
              the Service; (iii) unauthorised access to or alteration of your 
              transmissions or data; (iv) statements or conduct of any third party 
              on the service; or (v) any other matter relating to the Service.</p>
            <font size="2" color="#426984"><b>12. Modifications to Agreements, Policies or to our Services</b></font>
            <p>We reserve the right to change this Service Agreement at any time 
              without notice. We also reserve the right at any time to modify 
              or discontinue the Service, temporarily or permanently, with or 
              without notice to you. You agree that we shall not be liable to 
              you or any third party for any modification, suspension or discontinuance 
              of the Service. You acknowledge that we may establish general practices 
              and limits concerning use of the Service, including the maximum 
              disk space that will be allotted on YouSendIt’s servers on 
              your behalf, and the maximum number of times (and the maximum duration 
              for which) you may access the Service in a given period of time. 
              Further, you acknowledge that YouSendIt may change subscription 
              fees at any time without notice. Changes in subscription fees will 
              take effect on expiration of any existing Customer subscription.</p>
            <font size="2" color="#426984"><b>13. Termination</b></font>
            <p>13.1 Without limiting other remedies, YouSendIt may immediately 
              issue a warning, suspend (i.e., lock out access and operation of 
              Services for Customer) either temporarily or indefinitely, or terminate 
              Customer's account and refuse to provide Services to Customer if: 
              (a) YouSendIt believes that Customer have violated or acted inconsistently 
              with this Agreement, or any of our policies; or (b) Customer have 
              failed to pay fees or other payments due to YouSendIt; or (c) YouSendIt 
              is unable to verify or authenticate any information Customer provides 
              to YouSendIt; or (d) YouSendIt believes that Customer's actions 
              may cause legal liability for Customer, YouSendIt’s other 
              clients, or YouSendIt. </p>
            <p>YouSendIt may also in its sole discretion and at any time discontinue 
              providing the Service, or any part thereof, with or without notice. 
              You agree that any termination of your access to the Service under 
              any provision of these terms and conditions may be effected without 
              prior notice, and acknowledge and agree that YouSendIt may immediately 
              deactivate, archive or delete your account and all related information 
              and data and/or any further access to such data or the Service. 
              Further, you agree that YouSendIt shall not be liable to you or 
              any third-party for any termination of your access to the Service.</p>
            <p>Upon termination of this Agreement by either Customer or YouSendIt, 
              all of Customer rights under this Agreement, and YouSendIt's provision 
              of Services, will terminate immediately. </p>
            <p>13.2 The Sections 6 ("Customer Data"), 9 ("No Warranty"), 
              10 ("Indemnity"), 11 ("Limitation Of Liability") 
              and this Section 13 will survive any termination of this Agreement. 
            </p>
            <font size="2" color="#426984"><b> 14. Miscellaneous</b></font>
            <p>These terms and conditions will be governed by and construed in 
              accordance with the laws of the State of California, excluding that 
              body of law governing conflict of laws. Any legal action or proceeding 
              relating to or arising out of these Terms or your use of the Web 
              site will be brought in a federal or state court in Santa Clara 
              County, California, and you submit to the venue and personal jurisdiction 
              of such court. If any provision of these Terms is held to be invalid 
              or unenforceable, such provision will be enforced to the greatest 
              extent possible and the remaining provisions will remain in full 
              force and effect. Headings are for reference purposes only and in 
              no way define, limit, construe, or describe the scope or extent 
              of such section. YouSendIt's failure to act with respect to a breach 
              by Customer or others does not waive YouSendIt’s right to 
              act with respect to subsequent or similar breaches. No action by 
              Customer arising under this Agreement may be brought at any time 
              more than twelve (12) months after the facts occurred upon which 
              the cause of action arose. </p>
            <h5>14.1 Relationship</h5>
            <p>Customer and YouSendIt are independent contractors, and no agency, 
              partnership, joint venture, employee-employer or franchiser-franchisee 
              relationship is intended or created by this Agreement. </p>
            <h5>14.2 Assignment</h5>
            <p>Customer may not assign any of its rights, or delegate any of its 
              duties, under this Agreement, and any attempted assignment will 
              be null and void. </p>
            <h5>14.4 Force Majeure</h5>
            <p>Operation of our Services may be interfered with by numerous factors 
              outside of our control and we shall not be liable to you for any 
              delay or failure in performance under this Agreement resulting directly 
              or indirectly from causes beyond YouSendIt’s control. </p>
            <h5>14.5 Interpretation</h5>
            <p>If any provision of this Agreement is held to be invalid or unenforceable, 
              such provision shall be struck, as narrowly as possible, and the 
              remaining provisions shall be enforced. Headings are for reference 
              purposes only and in no way define, limit, construe or describe 
              the scope or extent of such section. </p>
            <h5>14.6 YouSendIt Confidential Information</h5>
            <p>You represent and warrant to YouSendIt that (a) you are not a competitor 
              of YouSendIt, (b) you shall keep publicly unannounced information 
              and materials pertaining to YouSendIt, pre-release software, testing 
              or testing procedures strictly confidential and (c) you shall not 
              use any information gained from access to the YouSendIt Web site 
              or use of the YouSendIt Services to compete with YouSendIt in its 
              business.</p>
            <h5>14.7 Exceptions</h5>
            <p>Except for other agreements or terms appearing on the Web site, 
              this Agreement set forth the entire understanding and agreement 
              between us with respect to the subject matter hereof. </p>
            <font size="2" color="#426984"><b>Additional Information</b></font>
            <p>Any questions relating to our Legal Agreements and Policies may 
              be directed to <a href="https://web.archive.org/web/20040604214510/mailto:legal@yousendit.com">legal@yousendit.com</a>.<br>
            </p></td>
        </tr>
      </tbody></table>
      <p class="PageNav">« <a href="javascript: history.back()" class="PageNav">Back</a></p>
      <p class="PageNav">&nbsp;</p>
      </td>
    
    <tr>
      <td width="444" class="Footer"><a href="/" class="Footer">YouSendIt</a> © 2026 | <a class="Footer" href="privacy.php">Privacy Policy</a> | <a class="Footer" href="terms.php">Terms of Service</a></td>
      <td width="296" class="Footer"><div align="right">Transferring over <?php echo number_format($averageBytesPerDay, 0, '.', ','); ?> bytes per day</div></td>
    </tr>
  </table>
</div>
</body>
</html>
