<?php function genmenu()
		{$xFil = $_SESSION["filid"];switch ($xFil) {	case 1: genmenu1(); break;	case 3: genmenu3(); break;	case 4: genmenu4(); break;  case 2: genmenu2(); break;  case 5: genmenu5(); break;	case 6: genmenu6(); break;	case 7: genmenu7(); break;	case 8: genmenu8(); break;	case 9: genmenu9(); break;	case 10: genmenu10(); case 14: genmenu14(); break;}}
		function genmenu1()
			{echo '<div class="menudiv"> 		<a class="menulink" href="infomng.php">Соопштенија</a> 		<a class="menulink" href="profili.php">Профили</a> 		<a class="menulink" href="usrmng.php">Корисници</a> 	<a class="menulink" href="mngizvod.php">Продажба со Лукоил картички</a>  	<a class="menulink" href="mngfin_karti.php">Финансиска картица</a>    <a class="menulink" href="mngprikaz_nalozi.php">Барања</a> 	 <a class="menulink" href="optprof.php">Промена на лозинка</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu10()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="finansii.php">Финансиски извештаи</a> 		<a class="menulink" href="mngprikaz_nalozi.php">Барања</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu11()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="finansii.php">Финансиски извештаи</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu13()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="finansii.php">Финансиски извештаи</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu14()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="izbor_naracki.php">Нарачки</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu2()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="izvod.php">Продажба со Лукоил картички</a> 	  <a class="menulink" href="fin_karti.php">Финансиска картица</a> 	<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.'корисник: <b>'.trim(getuser()).' ; </b>фирма: <b>'.trim(getcmp_opis()).'</b></span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu5()
		{
			echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="izvod.php">Продажба со Лукоил картички</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu3()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="finansii_rusija.php">Финансиски извештаи</a> 	 	<a class="menulink" href="ihr_analitika.php">Промет</a>     <a class="menulink" href="ihr_prometsektor.php">Промет по сектори</a>	  	<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu4()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="finansii.php">Финансиски извештаи</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu6()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="finansii.php">Финансиски извештаи</a> 		<a class="menulink" href="izbor_naracki.php">Нарачки</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu7()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="finansii.php">Финансиски извештаи</a> 		<a class="menulink" href="mngprikaz_nalozi.php">Барања</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu8()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="izvod.php">Продажба со Лукоил картички</a> 		<a class="menulink" href="mngprikaz_nalozi.php">Барања</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		function genmenu9()
			{echo '<div class="menudiv"> 		<a class="menulink" href="index.php">Почетна</a> 		<a class="menulink" href="finansii.php">Финансиски извештаи</a> 		<a class="menulink" href="finansii.php">Финансиски извештаи</a> 		<a class="menulink" href="info.php">Соопштенија</a> 		<a class="menulink" href="optprof.php">Промена на лозинка</a> 		<a class="menulink" href="help.php">Помош</a><span><span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		}
		?>