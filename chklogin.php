<?php
	include_once 'inc/functions.php';
	include_once 'inc/initialize.php';
	require_once('inc/recaptchalib.php');
	
	if (isset($_SESSION['lgnatt'])) {
		$ini = new Configini("flpt");
		if ($_SESSION['lgnatt'] >= (int) $ini->getinival('def','lgnatt','3')) {			
			$resp = recaptcha_check_answer ($ini->getinival('def','captchapr','###'),
					$_SERVER["REMOTE_ADDR"],
					$_POST["recaptcha_challenge_field"],
					$_POST["recaptcha_response_field"]);			
			if (!$resp->is_valid) {
				$_SESSION['lgnatt'] = $_SESSION['lgnatt'] + 1;
				redirect('login.php?err=1');
				return ;
			}
		}
	}
	
	$user=$_POST['login'];
	$pass=$_POST['pass'];
	$pass=md5($pass);
	
	if (login(connectweb(), $user, $pass)){
		if (isadmin()==1){
			if (isset($_SESSION['rx']))
			{
				$redx= $_SESSION['rx'];
				unset($_SESSION['rx']);
				redirect($redx.'.php');
			}
			else
			redirect('infomng.php');
	    }
	    elseif (resetpass()==1)
	    	redirect('optprof_reset.php');
	    else
		{
			if (isset($_SESSION['rx']))
			{
				$redx = $_SESSION['rx'];
				unset($_SESSION["rx"]);
				redirect($redx.'.php');				
			}
			else
				redirect('index.php');
		}
	}
	else {
		if (!isset($_SESSION['lgnatt'])) $_SESSION['lgnatt'] = 0; 
		$_SESSION['lgnatt'] = $_SESSION['lgnatt'] + 1;
		redirect('login.php?err=1');
	}
?>