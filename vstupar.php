<!--
   vstupar.php
   
   Copyright 2015 Petr Studený <dosmanak@dosbook>
   
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.
   
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
   MA 02110-1301, USA.
   
   
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Vstupařův průvodce večerem</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta content='width=device-width, initial-scale=0.7, maximum-scale=0.9, user-scalable=0' name='viewport' />
	<style>
		body { font-family: Helvetica }
		#concerts { padding: 2%; }
		#concerts table { width: 100%; font-size: 230%; table-layout: fixed}
		.tableHeader {font-size: 60% }
		#concerts tr { line-height: 200%; margin: 50px;}
		#concerts td { width: 15% }
		.priceCol { height: 80%; width: 90%; font-size: 60%}
		.modifier { height: 100%; width: 100%; font-size: 60%}
		/*
		.modifier-2 { background-color: red}
		.modifier6 { background-color: green}
		*/
		#eraser input{ font-size: 150%; line-height: 150% }
	</style>
	<script type="text/javascript">
	function setCookie(name, value)
	{
		/* dont ask for expiration in argument, use fix value */
		days = 2
		var date = new Date();
		date.setTime(date.getTime()+days*24*60*60*1000); 
		var expires = "; expires=" + date.toGMTString();
		document.cookie = name+"=" + value+expires + ";path=/"; 
	}
	function getCookie(cname) {
					var name = cname + "=";
					var ca = document.cookie.split(';');
					for(var i=0; i<ca.length; i++) {
									var c = ca[i];
									while (c.charAt(0)==' ') c = c.substring(1);
									if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
					}
					return 0;
	}
	function deleteAllCookies() 
	{
		var cookies = document.cookie.split(";");

		for (var i = 0; i < cookies.length; i++) {
			var cookie = cookies[i];
			var eqPos = cookie.indexOf("=");
			var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
			document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
		}
		window.location.reload(false);
	}
	function slugify(text)
	{
	  return text.toString().toLowerCase()
		.replace(/\s+/g, '-')           // Replace spaces with -
		.replace(/[^\w\-]+/g, '')       // Remove all non-word chars
		.replace(/\-\-+/g, '-')         // Replace multiple - with single -
		.replace(/^-+/, '')             // Trim - from start of text
		.replace(/-+$/, '');            // Trim - from end of text
	}
	function defaultName()
	{
		d = newDate();
		return "Koncert "+d.getDay()+". "+d.getMonth()+"., "+d.getHours()+"h";
	}

	function addConcertForm() //TBD
	{
		newConcert = document.createElement("input");
		newConcert.type = "text";
		newConcert.id = "title";
		newConcert.value = defaultName();
		newVariants = document.createElement("input");
		newConcert.type = "text";
		newVariants.id = "rows";
		newVariants.value = 5;
		newButton = document.createElement("input");
		newButton.type = "button";
		newButton.value = "Přidej";
    newButton.onclick = function() { new Concert() };
	}

	function createConcert()
	{
		var data = 'data={';
		concerts = document.getElementById("concerts");
		c1 = new Concert("První",5);
		concerts.appendChild(c1.drawTable());
		c1.loadCookies();	
		data += c1.jsonify();
		c2 = new Concert("Druhý",5);
		concerts.appendChild(c2.drawTable());
		c2.loadCookies();
		data += ','+c2.jsonify();
		c3 = new Concert("Special",3);
		concerts.appendChild(c3.drawTable());		
		c3.loadCookies();
		data += ','+c3.jsonify();
		data += '}';

		send = document.createElement("input");
		send.type = "button";
		send.class = "SendMail";
		send.onclick = function () { sendConcertsData(data); };
		send.value = "Odeslat e-mail";
		concerts.appendChild(send);

		
	}

	function sendConcertsData(data)
	{
		var xhttp = new XMLHttpRequest();
		xhttp.open("POST", "vstupar.php", true);
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				document.write(xhttp.responseText);
			}
		}
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send(data);
	}

	/***
	** Object concert:
	** one concert must have variables:
	**   name string
	**   rows integer
	**   price[rows] array of integers
	**   count[rows] array of integers
	** one concert must have methods:
	**   sumPrice(row)
	**   changeValBut(row, val)
	** one concert mauste have constructor
	**   new Concert(name,rows) - draw table with buttons and correct variables in it.
	**     create one concert with given name and rows.
	***/
	function Concert(_name,_rows)
	{
		this.title = _name;
		this.name = slugify(_name);
		this.rows = _rows;
		this.price = new Array(this.rows);
		this.count = new Array(this.rows);
    for (var i=0; i < this.rows; i++) {
      this.price[i]=0;
      this.count[i]=0;
    }
		this.setPrice = function(row, price)
		{
			if (isNaN(price)) { alert ("wrong call to setPrice"); return;};
			this.price[row] = price;
			//console.log(this.name+"-price"+row);
			//console.log(document.getElementById(this.name+"-price"+row));
			document.getElementById(this.name+"-price"+row).value = price;
			document.getElementById(this.name+"-sumPrice"+row).innerHTML = this.sumPrice(row);
			document.getElementById(this.name+"-totalPrice").innerHTML = this.getTotalPrice();
			setCookie(this.name+"-price"+row, price);
		}
		this.setCount = function(row, count)
		{
			if (isNaN(count)) { alert ("wrong call to setCount"); return;};
			this.count[row] = count;
			document.getElementById(this.name+"-count"+row).innerHTML = this.count[row];
			document.getElementById(this.name+"-sumPrice"+row).innerHTML = this.sumPrice(row);
			document.getElementById(this.name+"-totalPrice").innerHTML = this.getTotalPrice();
			document.getElementById(this.name+"-totalCount").innerHTML = this.getTotalCount();
			setCookie(this.name+"-count"+row, count);
		}
		this.sumPrice = function(row)
		{
			return this.price[row]*this.count[row];
		}
		this.getTotalPrice = function()
		{
			var totalPrice = 0;
			for (var i=0;i<this.rows;i++)
			{
				totalPrice += this.sumPrice(i);
			}
			return totalPrice;	
		}
		
		this.getTotalCount = function()
		{
			var totalCount = 0;
			for (var i=0;i<this.rows;i++)
			{
				totalCount += this.count[i];
			}
			return totalCount;
		}
		
		this.changeValBut = function(row,val){
			this.setCount(row,this.count[row] + val);
		}
		this.drawButton = function(row,val)
		{
			var input = document.createElement("input");
			input.type = "button";
			input.id = row+this.name+val;
			var label = val.toString();
			if (label.charAt(0) != "-") { label = "+"+label; };
			input.className = "modifier modifier"+val;
			input.value = label;
			//console.log("this, pri incializaci buttonu: ",this.name);
			var that = this;
			input.onclick = function() { that.changeValBut(row,val); }
			return input;
		}
		this.drawTable = function()
		{
			var envelope = document.createElement("div");
			envelope.id = this.name;
			var title = document.createElement("h2");
			title.name = this.title;
			title.innerHTML = this.title;
			var feeTable = document.createElement("table");
			var th = feeTable.insertRow(-1);
			th.className = "tableHeader";
			var hcount = th.insertCell(-1);
			hcount.innerHTML = "<b>Počet</b>";
			var hsumPrice = th.insertCell(-1);
			hsumPrice.innerHTML = "<b>Součet</b>";
			var hprice = th.insertCell(-1);
			hprice.innerHTML = "<b>Cena</b>";
			for (c=0; c < 4; c++){th.insertCell(-1);};
			for (r=0; r < this.rows; r++){
				var tr = feeTable.insertRow(-1);
				var countCell = tr.insertCell(-1);
				countCell.id = this.name+"-count"+r;
				countCell.name = this.name+"-count"+r;
				countCell.innertHTML = this.count[r];
				var sumPriceCell = tr.insertCell(-1);
				sumPriceCell.id = this.name+"-sumPrice"+r;
				sumPriceCell.innerHTML = this.sumPrice(r);
				var priceCell = tr.insertCell(-1);
				var priceCellInput = document.createElement("input");
				priceCellInput.type = "number";
				priceCellInput.id = this.name+"-price"+r;
				priceCellInput.name = this.name+"-price"+r;
				priceCellInput.className = "priceCol";
				priceCellInput.value = this.price[r];
				priceCellInput.size = 4;
				console.log("init: ",r);
				//this.r = r;				
				var that = this;
				priceCellInput.onblur = function(){
					roww = this.id.replace(/\D*/, "");
					that.setPrice(roww, this.value);
				}
				//priceCellInput.onblur = () => this.setPrice(r, priceCellInput.value);
				priceCell.appendChild(priceCellInput);
				//tr.insertCell(-1).appendChild(this.drawButton(r,-2));
				tr.insertCell(-1).appendChild(this.drawButton(r,-1));
				tr.insertCell(-1).appendChild(this.drawButton(r,+1));
				tr.insertCell(-1).appendChild(this.drawButton(r,+2));
				tr.insertCell(-1).appendChild(this.drawButton(r,+4));
				console.log("Processing row "+r);
			}
			var sumTotaltr = feeTable.insertRow(-1);
			sumTotaltr.innerHTML='<td id="'+this.name+'-totalCount">0</td>\
			<td id="'+this.name+'-totalPrice">0</td><td colspan="5">⇐Celkem</td>';
			
			envelope.appendChild(title);
			envelope.appendChild(feeTable);
			
			return envelope;
		}
		this.loadCookies = function()
		{
			/* fill from cookie */
			for (r=0; r < this.rows; r++){
				var priceRow = parseInt(getCookie(this.name+"-price"+r));
				if (priceRow == 0) { priceRow = 50*r; }
				this.setPrice(r,priceRow);
				this.setCount(r,parseInt(getCookie(this.name+"-count"+r)));
			}
		}
		this.jsonify = function()
		{
			//var jsonstring = '{"'+this.name+'":{ "title": "'+this.title+'", "prices": ['+this.price+'], "counts": ['+this.count+']}}';
			var jsonstring = '"'+this.name+'":{ "title": "'+this.title+'", "prices": ['+this.price+'], "counts": ['+this.count+']}';
			return jsonstring;
		}
	}

	</script>
</head>

<body id="main" onLoad="createConcert()">
	<div id="concertForm"></div>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$datajson = ($_POST["data"]);
	$data = json_decode($_POST["data"],true);
	//var_dump($data);
	echo "<pre>\n";
	$body = '';
	setlocale(LC_TIME, "cs_CZ");
	$body .= strftime("Vstupne do JazzDocku za %A %d. %m. %G %R\n");
	foreach ($data as $key => $value) {
		$body .= $value['title'];
		$body .= ":\n";
		$body .= "\tCena:\tPocet:\tCelkem\n";
		$sum = 0;
		for ($i = 0; ($i < count($value['prices'])); $i++)
		{
			$body .= "\t".$value['prices'][$i];
			$body .= "\t".$value['counts'][$i];
			$body .= "\t".$value['counts'][$i]*$value['prices'][$i];
			$sum += $value['counts'][$i]*$value['prices'][$i];
			$body .= "\n";
		}
		$body .= "\t\tSoucet:\t$sum\n";
	}
	echo $body;
	echo "</pre>";
	$sent = mail("Petr Studeny <dosmanak@centrum.cz>",strftime("JZD Vstupne %d.%m.%G"),$body);
	echo "Mail odeslan je $sent";
}
else
{
?>
	  <div id="concerts"></div>
<?php
}
?>
	<div id="eraser">
		<input type="button" value="Resetovat stránku" onclick="deleteAllCookies()"/>
	</div>
	
</body>

</html>
