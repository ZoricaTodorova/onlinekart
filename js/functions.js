function mvopts2sright(sFrom, sTo)
{
	var oFrom = document.getElementById(sFrom);
	var oTo = document.getElementById(sTo);
	if(oTo.options[0].value == "###") oTo.removeChild(oTo.options[0]);
	for (i=0;i < oFrom.options.length; i++)
		if (oFrom.options[i].selected)
			oTo.options[oTo.options.length] = new Option(oFrom.options[i].text, oFrom.options[i].value);

	var i = 0;
	while (i < oFrom.options.length)
	{
		if (oFrom.options[i].selected)
			oFrom.removeChild(oFrom.options[i]);
		else 
			i++;
	}
}
function mvopts2sleft(sFrom, sTo)
{
	var oFrom = document.getElementById(sFrom);
	var oTo = document.getElementById(sTo);
	for (i=0;i < oFrom.options.length; i++)
		if (oFrom.options[i].selected)
			oTo.options[oTo.options.length] = new Option(oFrom.options[i].text, oFrom.options[i].value);

	var i = 0;
	while (i < oFrom.options.length)
	{
		if (oFrom.options[i].selected)
			oFrom.removeChild(oFrom.options[i]);
		else 
			i++;
	}
	if(oFrom.options.length == 0)
		oFrom.options[oFrom.options.length] = new Option('яхре', '###');
}

function moveall(sFrom, sTo)
{
	var oFrom = document.getElementById(sFrom);
	var oTo = document.getElementById(sTo);
	for (i=0; i < oFrom.options.length; i++)
		oTo.options[oTo.options.length] = new Option(oFrom.options[i].text, oFrom.options[i].value);
	while (oFrom.options.length != 0)
		{
		oFrom.removeChild(oFrom.options[0]);
		}
	if(oFrom.options.length == 0)
		oFrom.options[oFrom.options.length] = new Option('яхре', '###');
}

function submitpickvod()
{
	var oKart = document.getElementById('kartselected');
	var oObj = document.getElementById('bsselected');
	var sObj = '';
	var sKart = '';

	if (oKart.options.length > 0)
		{
			if(oKart.options[0].value == "###") 
				oKart = document.getElementById('kartselector');
			sKart = oKart.options[0].value;
			for (i=1; i < oKart.options.length; i++) 
				sKart = sKart + ',' + oKart.options[i].value;				
		}
	if (oObj.options.length > 0)
	{
		if(oObj.options[0].value == "###") 
			oObj = document.getElementById('bsselector');
		sObj = oObj.options[0].value;
		for (i=1; i < oObj.options.length; i++) 
			sObj = sObj + ',' + oObj.options[i].value;
	}
	var oNew1 = document.createElement('input');
	oNew1.type = "hidden";
	oNew1.name = "resobj";
	oNew1.value = sObj;
	var oNew2 = document.createElement('input');
	oNew2.type = "hidden";
	oNew2.name = "reskart";
	oNew2.value = sKart;
	var oNew3 = document.createElement('input');
	oNew3.type = "hidden";
	oNew3.name = "thaoddat";
	oNew3.value = document.getElementById('OdGodina').value + document.getElementById('OdMesec').value + document.getElementById('OdDen').value + "000000";
	var oNew4 = document.createElement('input');
	oNew4.type = "hidden";
	oNew4.name = "thadodat";
	oNew4.value = document.getElementById('DoGodina').value + document.getElementById('DoMesec').value + document.getElementById('DoDen').value + "235959";
	document.forms['pickvod'].appendChild(oNew1);
	document.forms['pickvod'].appendChild(oNew2);
	document.forms['pickvod'].appendChild(oNew3);
	document.forms['pickvod'].appendChild(oNew4);
	document.forms['pickvod'].submit();
}

//function hideall()
//{
//	//var oObjs = document.getElementsByName("aaa");
//	alert('1');
//	var oObjs = document.getElementsByTagName("tr");
//	for(var i=0; i < oObjs.length; i++) {
//		alert('2');
//	    oObjs[i].hidden = true;
//	}
//}


function hideall1()
{
	var ddd = document.getElementsByClassName("ddd");
	var bbb = document.getElementsByClassName("bbb");
	
	var x = document.getElementById("vk_kart").checked;
	
	if (x == true){
		for(var i=0; i < ddd.length; i++) {
		    ddd[i].hidden = false;
		}
		for(var i=0; i < bbb.length; i++) {
		    bbb[i].hidden = false;
		}
	}else{
		for(var i=0; i < ddd.length; i++) {
		    ddd[i].hidden = true;
		}
		for(var i=0; i < bbb.length; i++) {
		    bbb[i].hidden = true;
		}
	}

}

function hideall2()
{
	var oObjs = document.getElementsByClassName("ddd");
	var x = document.getElementById("vk_kart").checked;
	
	if (x == true){
		for(var i=0; i < oObjs.length; i++) {
		    oObjs[i].hidden = false;
		}
	}else{
		for(var i=0; i < oObjs.length; i++) {
		    oObjs[i].hidden = true;
		}
	}

}

function hideall3()
{
	var oObjs = document.getElementsByClassName("redot");
	var x = document.getElementById("vk_prod").checked;
	
	if (x == true){
		for(var i=0; i < oObjs.length; i++) {
		    oObjs[i].hidden = false;
		}
	}else{
		for(var i=0; i < oObjs.length; i++) {
		    oObjs[i].hidden = true;
		}
	}

}

function junk()
{
	/* js
	 * for (i=0; i < oKart.options.length; i++)
	oKart.options[i].selected = true;
for (i=0; i < oObj.options.length; i++)
	oObj.options[i].selected = true;*/
	
	/* php
	 * //echo $_POST["kartselected"];	
	if (isset($_POST['bsselected']))
		foreach ($_POST['bsselected'] as $selectedOption) echo $selectedOption."\n";	
	if (isset($_POST['kartselected']))
		foreach ($_POST['kartselected'] as $selectedOption2) echo $selectedOption2."\n";
		
		if (isset($_POST['click']))
	{
		if ($_POST['click'])
		{
			//echo $_POST["DoGodina"];
			echo $_POST["kartselected"];
		}
	}
	 * */
}
function submitpicksoop()
{
	var oObj = document.getElementById('bsselected');
	var sObj = '';

	if (oObj.options.length > 0)
	{
		if(oObj.options[0].value == "###") 
			oObj = document.getElementById('bsselector');
		sObj = '"'+oObj.options[0].value+'"';
		for (i=1; i < oObj.options.length; i++)
			sObj = sObj + ',"' + oObj.options[i].value + '"';
	}
	var oNew1 = document.createElement('input');
	oNew1.type = "hidden";
	oNew1.name = "resobj";
	oNew1.value = sObj;
	document.forms['picksoop'].appendChild(oNew1);
	document.forms['picksoop'].submit();
}
function SetBaza(){
	document.getElementById('baza_id').value = document.getElementById('baza').value;
}
function PopulateTextbox(id1,id2){
	document.getElementById(id1).value = document.getElementById(id2).value;
}
//function msgbox(msg){
	//return alert(msg);
//}

