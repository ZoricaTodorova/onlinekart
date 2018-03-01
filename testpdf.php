 <?php
  require_once('inc\recaptchalib.php');
  $privatekey = "6Ld-5-cSAAAAAPs1FDbEWBcdWd58bZzzsyupBEdV";
  $resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

  if (!$resp->is_valid) {
    // What happens when the CAPTCHA was entered incorrectly
    die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
         "(reCAPTCHA said: " . $resp->error . ")");
  } else {
  	echo 'BINGOOO';
    // Your code here to handle a successful verification
  }
  return ;
?>


<?php
ob_start();
include 'index.php';
require("lib/dompdf/dompdf_config.inc.php");   

$dompdf = new DOMPDF();
$dompdf->load_html(ob_get_clean());
$dompdf->render();
$dompdf->stream("sample.pdf", array('Attachment'=>'0'));

?>