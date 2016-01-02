// 
//  Object Concert keeps actual data in cookie, to allow easily control entrace state
//  There is more helper functions, maybe TODO object
//
//  Author: Petr Studeny <dosmanak@centrum.cz>
//
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
function getCookiesContaining(cname) {
				var values = new Array();
				var name = cname + "=";
				var ca = document.cookie.split(';');
				for(var i=0; i<ca.length; i++) {
								var c = ca[i];
								while (c.charAt(0)==' ') c = c.substring(1);
								if (c.indexOf(name) > 0) values.push(c.substring(c.indexOf('=')+1, c.length));
				}
				return values;
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
	.replace(/\s+/g, '_')           // Replace spaces with _
	.replace(/[^\w\-]+/g, '')       // Remove all non-word chars
	.replace(/\-\-+/g, '_')         // Replace multiple - with single _
	.replace(/-/g,'_')							// Replace - with _
	.replace(/^-+/, '')             // Trim - from start of text
	.replace(/-+$/, '');            // Trim - from end of text
}

//
// Object Evening
//
function Evening(id)
{
	this.htmlObject = document.getElementById(id);
	if (! this.htmlObject ) 
	{
		console.log('wrong html id used creating Evening');
		return null;
	}
	this.defaultTitle = function()
	{
		d = new Date();
		return "Koncert "+d.getDate()+". "+(d.getMonth()+1)+"., "+d.getHours()+"h";
	}
	this.formTitle = document.createElement("input");
	this.formTitle.type = 'text';
	this.formTitle.id = 'formTitle';
	this.formTitle.value = this.defaultTitle();

	this.formRows = document.createElement("input");
	this.formRows.type = 'number';
	this.formRows.id = 'formRows';
	this.formRows.value = 4;

	this.concerts = new Array();

	this.getConcertValues = function()
	{
		var tuple = new Array();
		var elem = document.getElementById('formTitle');
		if ( ! elem ) 
		{
			console.log('formTitle undefined');
			elem = defaultTitle(); 
		}
		tuple['title'] = elem.value;
		elem = document.getElementById('formRows');
		if ( ! elem ) 
		{
			console.log('formRow undefined');
			elem = 4; 
		}
		tuple['rows'] = elem.value;
		return tuple;
	}
	this.formButton = document.createElement("input");
	this.formButton.type = 'button';
	this.formButton.id = 'formButton';
	this.formButton.value = 'Přidej';
	var that = this;
	this.formButton.onclick = function()
	{
		var formValues = that.getConcertValues();
		var co = new Concert ( formValues['title'],formValues['rows'] );
		that.concerts.push( co );
		that.htmlObject.appendChild(co.drawTable()); 
	}
	this.htmlObject.appendChild(this.formTitle);
	this.htmlObject.appendChild(this.formRows);
	this.htmlObject.appendChild(this.formButton);
	// That was just the beggingin, now try to search cookies for existing Concerts
	var titlesCookie = getCookiesContaining("title");
	var rowsCookie = getCookiesContaining("rows");
	while (c = titlesCookie.shift())
	{
		this.concerts.push(new Concert(c, rowsCookie.shift()));
		this.htmlObject.appendChild( concerts[concerts.length - 1].drawTable());
		this.concerts[concerts.length - 1].loadCookies();
	}

	// Go through concerts go generate json to send to server
	this.prepareData = function(){
		var data = 'data={';
		for (var i=0; i < that.concerts.length; i++)
		{
			if ( i > 0 ) data+= ',';
			data += that.concerts[i].jsonify();
		}
		data += '}';
		return data;
	}

	var sendto = document.createElement("input");
	sendto.type = "text";
	sendto.id = "SendMailTo";
	//sendto.value = "info@jazzdock.cz";
	sendto.value = "dosmanak@centrum.cz";
	document.getElementById('resetCookies').appendChild(sendto);
	var send = document.createElement("input");
	send.type = "button";
	send.class = "SendMail";
	send.onclick = function () 
	{ 
		var to = document.getElementById("SendMailTo").value;
		var data = 'mailto='+to+'\&'+that.prepareData();
		sendConcertsData(data); 
	};
	send.value = "Odeslat e-mail";
	document.getElementById('resetCookies').appendChild(send);

	console.log(getCookiesContaining("title"));
	var resetButton = document.createElement("input");
	resetButton.type = "button";
	resetButton.value = "Resetovat stránku";
	resetButton.onclick = function() { deleteAllCookies(); }
	document.getElementById('resetCookies').appendChild(resetButton);
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
	setCookie(this.name+"-title", this.title); // to be able to recreate from cookies
	setCookie(this.name+"-rows", this.rows);
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
			countCell.innerHTML = this.count[r];
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
				var roww = this.id.replace(/.*-price/, "");
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
			this.setPrice(r,priceRow);
			this.setCount(r,parseInt(getCookie(this.name+"-count"+r)));
		}
	}
	this.jsonify = function()
	{
		//var jsonstring = '{"'+this.name+'":{ "title": "'+this.title+'", "prices": ['+this.price+'], "counts": ['+this.count+']}}';
		var jsonstring = '"'+this.name+'":{ "title": "'+encodeURIComponent(this.title)+'", "prices": ['+this.price+'], "counts": ['+this.count+']}';
		return jsonstring;
	}
}

