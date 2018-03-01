<?php
function redirect($url, $statusCode = 303) { header('Location: ' . $url, true, $statusCode);  exit(); die();}

function connectweb() {	return anyconnect('dbweb'); }
function connectkart() { return anyconnect('dbkart'); }
function connectwebnal() {return anyconnect('dbwebnal');}
function connectana() {return anyconnect('dbanalitika');}

function anyconnect($dbini = '')
{
	$ini = new Configini("flpt");
	$handle = mysqli_connect($ini->getinival($dbini,'dbhost',''),$ini->getinival($dbini,'dbuser',''),$ini->getinival($dbini,'dbpass','')) or die('Error');
	if ($handle){
		mysqli_select_db($handle, $ini->getinival($dbini,'dbname','###')) or die('Нема база');
		mysqli_set_charset($handle, $ini->getinival($dbini,'dbchar',''));
	}
	return $handle;
}

function disconnect($xhandle)
{
	mysqli_close($xhandle) or die('Problem so zakacalka =D');
}
function login($handle, $xuser, $xpass)
{
	if (!$handle) return false;
	//strip
	//$xpass=md5($xpass);
	
	$xuser=mysqli_real_escape_string($handle,$xuser);
	$xpass=mysqli_real_escape_string($handle,$xpass);
	$res=mysqli_query($handle, "select id, id_cmp, id_profil, idcmp_nal, boja from usr where login='$xuser' and pass='$xpass'");
	if (mysqli_affected_rows($handle) == 1) 
	{
		$row=mysqli_fetch_row($res);
		$_SESSION['lgn'] = $xuser;
		$_SESSION['idcmp'] = $row[1];
		$_SESSION['timeout'] = time();
		$_SESSION['filid'] = $row[2];
		$_SESSION['cmpnalozi'] = $row[3];
		$_SESSION['sektor']='';
		$_SESSION['fin_baza']=array();
		if (EMPTY($row[4])){
			$_SESSION['boja']='FFFFFF';
		}else{
			$_SESSION['boja']=$row[4];
		}
		$dt = date('Y-m-d H:i:s', time());
		mysqli_query($handle, "insert into loginlog (id, login, logintime) values ($row[0], '$xuser', '$dt')") or die('zxzz1');
		mysqli_query($handle, "update usr set lastlogin = '$dt' where id = $row[0]") or die('zxzz2');
		return true;
	}
	if (isset($_SESSION['lgnatt'])){ $lgnatt = $_SESSION['lgnatt'];}
		else {
			$lgnatt = 0;
			session_destroy();
			session_start();
			$_SESSION['lgnatt'] = $lgnatt;
			return false;
		}
}

function logged()
{
	if (isset($_SESSION["lgn"])){
		if ($_SESSION["lgn"]!='') {
			$ini = new Configini('flpt');
			$tmout = (int) $ini->getinival('def', 'timeout', '360000');			
			$prevtime = $_SESSION["timeout"] or die('Problem so timeout!!!');
			if (time()-$prevtime > $tmout)
			{
				return logout();
			}
			$_SESSION["timeout"] = time();
			return $_SESSION["lgn"];
		}					
	}
	return '';
}

function Generatemenuphp(){
	$cPhpall = '';
	$apo = '"';
	$class =' 		<a class="';
	$href = '" href="'	;
	$strl = '">';
	$handle = anyconnect('dbweb');
	$cmdmens = "select cast(id_profil as char) as id_profil, group_concat(concat('$class', class, '$href', href, '$strl', dsc, '</a>') order by {id_profil ind} separator '') from menu group by id_profil order by id_profil, ind";
	//$cmdmens = "select 1";
	$res1 = mysqli_query($handle, $cmdmens);
	
	$ppp = '<?php function genmenu()
		{$xFil = $_SESSION["filid"];';
	$menphp = 'SELECT concat("switch ($xFil) {",group_concat("	case ", id, ": genmenu", id, "(); break;" separator ""), "}}") FROM profil';
	$res2 = mysqli_query($handle, $menphp);	
	while($row = mysqli_fetch_array($res1))
	{
		$cPhpall = $cPhpall.'function genmenu'.$row[0]."()
			{echo '".'<div class='.$apo.'menudiv'.$apo.'>'.$row[1];
		$cLine = '<span><span style='.$apo.'display:inline-block; position:absolute; right:70px'.$apo.'>';
		//$cLine = $cLine.'".trim(getuser())."</span><a class='.'"menulink" href="?run=func" style="position:absolute;right:15px">Одјава</a></span></div>';
		$cLine = $cLine."'".'.trim(getuser()).'."'".'</span><a class='.'"menulink" href="logout.php" style="position:absolute;right:15px">Одјава</a></span></div>';
		$cLine = $cLine."';
		}
		";
		$cPhpall = $cPhpall.$cLine;
	}
	$row = mysqli_fetch_row($res2);
	$cPhpall = $ppp.$row[0].$cPhpall.'?>';
		
	file_put_contents('inc\menu.php', $cPhpall);
}

class Configini{
	private $filename;
	private $ini_array=array();
	
	public function __construct($filename) {
		$this->filename = $filename;
		$this->ini_array = parse_ini_file(file_get_contents($this->filename), true);
	}
	
	public function getinival($sektor, $setting, $defval){
		if (isset($this->ini_array[$sektor][$setting]))
			return $this->ini_array[$sektor][$setting];
		return $defval;
	}	
}

function getuser()
{
	if (isset($_SESSION["lgn"])){
		return $_SESSION["lgn"];
	}
	return '';
}

function ime_prezime()
{
	
}

function getprofil()
{
	if (isset($_SESSION["filid"])){
		return $_SESSION["filid"];
	}
	return 0;
}
function getcmp()
{
	if (isset($_SESSION["idcmp"])){
		return $_SESSION["idcmp"];
	}
	return '';
}
function getcmp_opis()
{
	if (isset($_SESSION["idcmp"])){
		$handle=connectkart();
		$cmd=mysqli_query($handle, "SELECT opis from firmi where cod=".$_SESSION['idcmp']);
		$firma_opis=mysqli_fetch_row($cmd);
		return $firma_opis[0];
	}
	else return '';

}
function populateizvod()
{
	
}
function populatebsobjopt()
{
	$handle = connectkart();
	$cOpts='';
	$res=mysqli_query($handle,"select cod as id, opis as dsc from org_e where vid = '04'");
	while ($row=mysqli_fetch_row($res)){
		$cOpts=$cOpts.'<option value="'.$row[0].'">'.$row[0].' '.$row[1].'</option>';
	}
	return $cOpts;
}
function populateusrid()  // za karticki dava firmi
{
	if ($_SESSION['sektor']=='dbkart'){      
	$handle = connectkart();
	$cOpts='';
	//$id_cmp=$_SESSION['sektor'];
	$res=mysqli_query($handle, "select cod, opis from firmi order by tip,opis");
	while ($row=mysqli_fetch_row($res)){
		$cOpts=$cOpts.'<option value="'.$row[0].'">'.$row[1].'</option>';
	}
	return $cOpts;
	}
	else                  // za finansii dava useri
	{
		$handle = connectweb();
		$cOpts='';
		//$id_cmp=$_SESSION['sektor'];
		$res=mysqli_query($handle, "SELECT usr.id,login FROM usr LEFT JOIN profilcmp on profilcmp.id_profil=usr.id_profil WHERE profilcmp.id_cmp='".$_SESSION['sektor']."'");
		while ($row=mysqli_fetch_row($res)){
			$cOpts=$cOpts.'<option value="'.$row[0].'">'.$row[1].'</option>';
		}
		return $cOpts;
	}
}

function populatefin()  //za finansii dava useri
{
	if ($_SESSION['sektor']!='dbkart'){
		$handle = connectweb();
		$cOpts='';
		//$id_cmp=$_SESSION['sektor'];
		$res=mysqli_query($handle, "select id, login from firmi");
		while ($row=mysqli_fetch_row($res)){
			$cOpts=$cOpts.'<option value="'.$row[0].'">'.$row[1].'</option>';
		}
		return $cOpts;
	}
}

function populatekartopt()
{
	if (!isset($_SESSION["idcmp"])) die('Problem so kod na firma');
	if ($_SESSION["idcmp"]=='') die('Problem so kod na firma');
	
	$handle = connectkart();
	$cIdcmp = $_SESSION["idcmp"];
	$cOpts='';
	//strip
	$res=mysqli_query($handle,"select kart_br as id, kart_br as kardno from kartici where firma='$cIdcmp'") or die();
	while ($row=mysqli_fetch_row($res)){
		$cOpts=$cOpts.'<option value="'.$row[0].'">'.$row[1].'</option>';
	}
	return $cOpts;
}
function top_pic(){
	$ini = new Configini("flpt");
	//echo '<div><img src="img/logo.png"><div style="font:bold;float:right;font-size:18;text-align:right;margin-top:-37px">ЛУКОИЛ МАКЕДОНИЈА</div></div>';
	echo '<div><img src="img/logo.jpg"/><span style="font-weight:bold;position:absolute;top:20;right:10;font-size:23;">'.$ini->getinival('def', 'cmpname', 'ERR name').'</span></div>';
}
function genmenuold1()
{
	if (isadmin()==1){
		echo '<div class="menudiv">
		<a class="menulink" href="infomng.php">Соопштенија</a>
		<a class="menulink" href="usrmng.php">Корисници</a>
		<span>
			<span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="?run=func" OnClick="return confirm(\'Дали сте сигурни?\');" style="position:absolute;right:15px">Одјава</a>
		</span>
		</div>';
	}
	else
	echo '<div class="menudiv">
	<a class="menulink" href="index.php">Почетна</a>
	<a class="menulink" href="izvod.php">Извештај за продажба</a>
	<a class="menulink" href="info.php">Соопштенија</a>
	<a class="menulink" href="optprof.php">Промена на лозинка</a>
	<a class="menulink" href="help.php">Помош</a>
	<span>
		<span style="display:inline-block; position:absolute; right:70px">'.trim(getuser()).'</span><a class="menulink" href="?run=func" OnClick="return confirm(\'Дали сте сигурни?\');" style="position:absolute;right:15px">Одјава</a>
	</span>
	</div>';
}

function getrx($tharx='')
{
	if ($tharx!='')
	{
		$_SESSION['rx'] = $tharx;
	}
}

function logout()
{
	session_start();
	session_unset();
	session_destroy();
	session_write_close();
	header('Location: login.php');
}

function isadmin(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT isadmin from profil where id = '".getprofil()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	if ($row[0]==1)
		return true;
	else
		return false;
	
}
function isadmin_nalozi(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT admin_nalozi from profil where id = '".getprofil()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	if ($row[0]==1)
		return true;
	else
		return false;

}
function isadmin_fina(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT admin_fina from profil where id = '".getprofil()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	if ($row[0]==1)
		return true;
	else
		return false;

}

function bskart(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT bskart from profil where id = '".getprofil()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	return $row[0];
}
function finarep(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT finarep from profil where id = '".getprofil()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	return $row[0];
}

function nalog(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT nalozi from profil where id = '".getprofil()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	return $row[0];
}

function resetpass(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT resetpass from usr where login = '".getuser()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	return $row[0];
}
function getid(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT id from usr where login = '".getuser()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	return $row[0];
}
function getidcmp_nal(){
	$handle = connectweb();
	$res = mysqli_query($handle, "SELECT idcmp_nal from usr where login = '".getuser()."'") or die(mysqli_error());
	$row=mysqli_fetch_row($res);
	return $row[0];
}
function genreport($oddat, $dodat, $bss, $karts)
{
	$handle = connectkart();
	$idcmp = getcmp();
	$oddat=mysqli_real_escape_string($handle,$oddat);
	$dodat=mysqli_real_escape_string($handle,$dodat);
	//$bss=mysqli_real_escape_string($handle,$bss);
	//$karts=mysqli_real_escape_string($handle,$karts);
	$header = array('Клиент', 'Корисник', 'Карта', 'Датум/час', 
			'Регистрација', 'Сметка', 'Артикл', 'Ед.цена', 'Количина', 'Вредност', 'Станица', 'Километража', 'SAP број');
	$structure = array('klient', 'korisnik', 'karta', 'datum_cas', 'avtomobil', 'smetka', 'artikl', 'ed_cena',
			'kolicina', 'vrednost', 'stanica', 'kilometraza', 'sap');
	//strip
	$cmd = "select 'aaa' as trclass,
			0 as bold, 
			firmi.opis as klient, 
			kartici.vozac_id as sap,
			kartici.vozac as korisnik, 
			lkk_broj as karta, 
			t_dt as datum_cas, 
			kartici.reg_br as avtomobil, 
			dokument as smetka, 
			materijali.opis as artikl,
			mat as artgrup,  
			format(bs_exchange.cena,2) as ed_cena, 
			format(kolicina,2) as kolicina, 
			format(iznos,0) as vrednost, 
			bs as stanica,
			kilometri as kilometraza,
			firmi.cod as klientcod from bs_exchange
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
	where t_dt between '$oddat' and '$dodat' and 
	lkk_broj in ($karts) and bs in ($bss) and mat <> ''
	order by klientcod, karta, datum_cas";
	$res=mysqli_query($handle, $cmd) or die(mysqli_error($handle));
	//echo $cmd;
	$cmd = "select 'bbb' as trclass,
			1 as bold, 
			concat('Вкупно за артикл:', mat) as klient, 
			lkk_broj as kardno, 
			format(sum(kolicina),2) as kolicina, 
			mat as artgrup,  
			materijali.opis as artikl,
			format(sum(iznos),0) as vrednost from bs_exchange 
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
	where t_dt between '$oddat' and '$dodat' and 
	lkk_broj in ($karts) and bs in ($bss)
	group by klient, kardno, artgrup
	order by kardno, artikl";
	$res1=mysqli_query($handle, $cmd) or die(mysqli_error());
	
	$cmd = "select 'ccc' as trclass,
			1 as bold, 
			materijali.opis as artikl,
			mat as artgrup,
			format(sum(kolicina),2) as kolicina, 
			format(sum(iznos),0) as vrednost from bs_exchange
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	left join materijali on materijali.cod = bs_exchange.mat and materijali.godina = bs_exchange.godina 
	where t_dt between '$oddat' and '$dodat' and
	lkk_broj in ($karts) and bs in ($bss)
	order by artikl;";
	$res2=mysqli_query($handle, $cmd) or die(mysqli_error());
	
	$cmd = "select 'ddd' as trclass,
			1 as bold, 
			concat('Вкупно за карта:', lkk_broj) as klient,
			lkk_broj as kardno,
			format(sum(kolicina),2) as kolicina, 
			format(sum(iznos),0) as vrednost from bs_exchange
	left join kartici on kart_br = lkk_broj and bs_exchange.firma=kartici.firma
	left join firmi on bs_exchange.firma = firmi.cod
	where t_dt between '$oddat' and '$dodat' and
	lkk_broj in ($karts) and bs in ($bss)
	group by klient, kardno
	order by kardno";
	$res3=mysqli_query($handle, $cmd) or die(mysqli_error());
	//echo $cmd;
	$tmp = '';
	$tmp3 = '';
	$result = array();
	while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)){
		if ($tmp=='') $tmp=$row['karta'];
		else 
		{
			if ($tmp != $row['karta'])
			{
				if (isset($row1)) $result[] = $row1;
				do {
					$row1 = mysqli_fetch_array($res1, MYSQLI_ASSOC);
					if ($tmp == $row1['kardno']) $result[] = $row1;
				} while ($tmp == $row1['kardno']);
				$row3 = mysqli_fetch_array($res3, MYSQLI_ASSOC);
				$result[] = $row3;
				$tmp = $row['karta'];
			}
		}			
		//$result[] = $row;
		if (isset($row)) $result[] = $row;
	}
	//$result[] = $row1;
	if (isset($row1)) $result[] = $row1;
	while ($row1 = mysqli_fetch_array($res1, MYSQLI_ASSOC)) $result[] = $row1;
	$result[] = mysqli_fetch_array($res3, MYSQLI_ASSOC);
	//print_r($result);
	return drawtablefarr($result, $structure, $header);
	
}

function convertres2arr($reso)
{
	$results = array();	
	while ($row = mysqli_fetch_array($reso, MYSQLI_ASSOC)){		
		$results[] = $row;
	}
	return $results;
}

function concatarr($arr1, $arr2)
{
	foreach($arr2 as $element)
		$arr1[] = $element;	
	return $arr1;
}

function dodadisoop($db, $firmi_cod, $soopid)
{
	$handle=connectweb();
	if ($db=='dbkart'){		
		$res=mysqli_query($handle, "SELECT id from usr where id_cmp in ($firmi_cod)");		
	
		while($row=mysqli_fetch_row($res)){
			//$handle = anyconnect($db);
			$soopid=mysqli_real_escape_string($handle,$soopid);
			mysqli_query($handle, "INSERT INTO usrsoop (id_usr,id_soop,procitano) VALUES($row[0],'$soopid',0)");
		}
	}
	else
	{
		$usrid=$firmi_cod;
		$usrid_arr=explode(',' , $usrid);//go pravime u array
		$soopid=mysqli_real_escape_string($handle,$soopid);
		foreach($usrid_arr as $usr){				
			mysqli_query($handle, "INSERT INTO usrsoop (id_usr,id_soop,procitano) VALUES($usr,'$soopid',0)");				
		}
	}
}

function editirajsoop($db, $firmi_cod, $soopid)
{
	$handle = connectweb();
	$soopid=mysqli_real_escape_string($handle,$soopid);
	mysqli_query($handle, "DELETE FROM usrsoop WHERE id_soop=".$soopid);
	
	if ($db=='dbkart'){
		$res=mysqli_query($handle, "SELECT id from usr where id_cmp in ($firmi_cod)");
	
		while($row=mysqli_fetch_row($res)){
			//$handle = anyconnect($db);
			mysqli_query($handle, "INSERT INTO usrsoop (id_usr,id_soop,procitano) VALUES($row[0],$soopid,0)");
		}
	}
	else
	{
		$usrid=$firmi_cod;
		$usrid_arr=explode(',' , $usrid);//go pravime u array
		$soopid=mysqli_real_escape_string($handle,$soopid);
		foreach($usrid_arr as $usr){
			mysqli_query($handle, "INSERT INTO usrsoop (id_usr,id_soop,procitano) VALUES($usr,'$soopid',0)");
		}
	}
}

function drawtable($reso, $head)
{
	$cFull = '<table cellspacing="0" cellpadding="4" align="Left" rules="cols" border="1" style="color:#333333;border-color:#666666;border-width:1px;border-style:solid;font-size:10px;width:100%;border-collapse:collapse;>"';
	$cThead = "";
	while ($row = mysqli_fetch_array($reso, MYSQLI_ASSOC)){
		if ($cThead=="")
		{
			$cThead = '<tr align="left" style="color:#666666;background-color:GhostWhite;border-color:White;font-size:10px;font-weight:bold;">';
			for ($i=0; $i < count($head); $i++){
				$cThead = $cThead .'<td>'.$head[$i].'</td>';
			}
			$cFull = $cFull.$cThead.'</tr>';
			
		}
		$cLine='<tr style="background-color:White;font-size:10px;">';
		foreach ($row as $field=>$val){
			$cLine = $cLine.'<td>'.$val.'</td>';
		}
		$cFull=$cFull.$cLine.'</tr>';
	}
	
	$cFull=$cFull;
	return $cFull;
}

function drawtablefarr($array, $struc, $head)
{
	$cFull = '<table id="report" cellspacing="0" cellpadding="4" align="Left" rules="cols" border="1" style="color:#333333;border-color:#666666;border-width:1px;border-style:solid;font-size:10px;width:100%;border-collapse:collapse;>"';	
	$cThead = '<tr align="left" style="color:#666666;background-color:GhostWhite;border-color:White;font-size:10px;font-weight:bold;">';
	for ($i=0; $i < count($head); $i++){
		$cThead = $cThead .'<td>'.$head[$i].'</td>';
	}
	$cFull = $cFull.$cThead.'</tr>';	

	foreach($array as $row)
	{
		$cLine='<tr ';
		//class
		if (isset($row['trclass']))
			$cLine=$cLine.'class="'.$row['trclass'].'" ';
		//style
		if (isset($row['bold']))
			if (empty($row['bold']))
				$cLine=$cLine.'style="background-color:White;font-size:10px;font-weight: lighter;"';
			else 
				$cLine=$cLine.'style="background-color:Dark;font-size:10px;font-weight: bolder;"';
						
		$cLine=$cLine.'>';
		
		$vk_vr=0;
		
		foreach($struc as $value)
		{
			if (isset($row[$value]))
				$cLine=$cLine.'<td>'.$row[$value].'</td>';
			else 
				$cLine=$cLine.'<td></td>';
		}
		$cFull=$cFull.$cLine.'</tr>';
	}	
	$cLine=
	$cFull=$cFull;
	return $cFull;
}

function cp_zont_1251($str='')
{
	$CP1251 = 'АБВГДЃЕЖЗЅИЈКЛЉМНЊОПРСТЌУФХЦЧЏШабвгдѓежзѕијклљмнњопрстќуфхцчџш';
	$CPZONT = 'ABVGDGEZZYIJKLQMNWOPRSTKUFHCCX{abvgdgezzyijklqmnwoprstkufhccx[';	
	$novstr = '';
	$nLen = strlen($str);	
	for ($i=0; $i < $nLen; $i++)
	{
	$nInt = strrpos($CPZONT, $str[$i]);
	if ($nInt != false)
		$novstr = $novstr.$CP1251[$nInt];
		else
		$novstr = $novstr.$str[$i];
	}
	return $novstr;
}

function cp_1251_zont($str='')
{
	$CP1251 = 'АБВГДЃЕЖЗЅИЈКЛЉМНЊОПРСТЌУФХЦЧЏШабвгдѓежзѕијклљмнњопрстќуфхцчџш';
	$CPZONT = 'ABVGDGEZZYIJKLQMNWOPRSTKUFHCCXSabvgdgezzyijklqmnwoprstkufhccxs';
	$novstr = '';
	$nLen = strlen($str);
	for ($i=0; $i < $nLen; $i++)
	{
		$nInt = strrpos($CP1251, $str[$i]);
		if ($nInt != false)
			$novstr = $novstr.$CPZONT[$nInt];
		else
			$novstr = $novstr.$str[$i];
	}
	return $novstr;
}

function reports_look_danbr($arg_look_cod,$Tabela)
{
	$handle=anyconnect($_SESSION['fin_baza'][0]); //treba da se napravi konekcija so prioritetnata baza, momentalno ja zemam prvata
	$arg_look_cod=mysqli_real_escape_string($handle,$arg_look_cod);
	$query = "select DANBR from $Tabela where cod = '$arg_look_cod'" ;


	$rezult = mysqli_query($handle, $query) or die("Error in query:" . $query . " <hr> error test:" . mysqli_error());

	$row = mysqli_fetch_row($rezult);
	$sif =  $row[0];
	$count = mysqli_affected_rows($handle);

	if ($count == 1 ){
		return  $sif;
	}
	else
	{
		return false ;
	}

}

function fin_unset()
{
	$_SESSION['fin_baza']='';
	unset($_SESSION['xxk']);
	unset($_SESSION['korisnik']);
	unset($_SESSION['konto']);
	unset($_SESSION['totfin']);
	unset($_SESSION['klik']);
	unset($_SESSION['selekt']);
	unset($_SESSION['oddat']);
	unset($_SESSION['dodat']);
	unset($_SESSION['query']);
	
	unset($_SESSION['mat']);
	unset($_SESSION['kol']);
	unset($_SESSION['vk_vr']);
	unset($_SESSION['rabat']);
	unset($_SESSION['cena_r']);
	unset($_SESSION['cena']);
}

function junkcode()
{
	/*$hihihi = convertres2arr($res);
	 $hihihi = concatarr($hihihi, convertres2arr($res1));
	$hihihi = concatarr($hihihi, convertres2arr($res2));*/
	
	/*$bli = '<table><tr><td>'.drawtable($res, $header).'</td></tr>';
	 $bli = $bli.'<tr><td>'.drawtable($res1, $header).'</td></tr>';
	$bli = $bli.'<tr><td>'.drawtable($res2, $header).'</td></tr></table>';
	return $bli;*/
	//return drawtable($res, $header);
}
?>