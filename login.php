<?php
	include_once 'inc/functions.php';                                     // so parametar ke se gleda dali ke bide golemo/malo 
	include_once 'inc/initialize.php';
	require_once('inc/recaptchalib.php');
	if (isset($_GET['rx'])) getrx($_GET['rx']);
	if(logged()) redirect('index.php');

?>
<form name='loginform' method='post' autocomplete='off' action='chklogin.php'>
<table>
	<tr>
		<td>Корисник:</td>
		<td><input type='text' name='login'></input></td>
	</tr>
	<tr>
		<td>Лозинка:</td>
		<td><input type='password' name='pass'></input></td>
	</tr>	
	<tr><td><input type='submit' name='subbut' value='Пријави се'></input> </td></tr>	
</table>
	<?php 
		$ini = new Configini("flpt");
		if (!isset($_SESSION['lgnatt'])) $_SESSION['lgnatt'] = 0;
		if ($_SESSION['lgnatt'] >= (int) $ini->getinival('def','lgnatt','3')) {
			echo recaptcha_get_html($ini->getinival('def','captchapb','###'));
		}		
	?>	
</form>

<?php 
if (isset($_GET['err']) && $_GET['err']==1)
	echo "Внесовте погрешна лозинка и/или корисничко име!"
?>