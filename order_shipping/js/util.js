//There are element ids duplication between real shipment fee and fee details dialog --> using parent element id goes with class name to identify correct one 
// multiply to 100 to fix dynamic dot with float number, assumption that, there is not more than 2 number after dot char.
function calFeeAmount(parentId, weight, price, unit, total) {
	var weight = parseFloat(document.getElementById(parentId).getElementsByClassName(weight)[0].value);// addClient.lender_loanAmount.value;
	var price = parseFloat(document.getElementById(parentId).getElementsByClassName(price)[0].value);// addClient.builder_packageAmount.value;
	var unit = parseFloat(document.getElementById(parentId).getElementsByClassName(unit)[0].value);// addClient.lender_loanAmount.value;
	var totalPricePackage = ((weight * price) + (unit* price)).toFixed(4);
	document.getElementById(parentId).getElementsByClassName(total)[0].value = totalPricePackage;
}
function calTotal(parentId) {
	var total = 0;
	for ( i = 1; i < 11; i++) {
		totalId = 'total' + i;
		total += parseFloat(document.getElementById(parentId).getElementsByClassName(totalId)[0].value);
	}
	// document.getElementById(parentId).getElementsByClassName("amount")[0].value = (total + parseFloat(document.getElementById(parentId).getElementsByClassName("add_fee")[0].value)).toFixed(4);
    // document.getElementById(parentId).getElementsByClassName("amount")[0].value = (total).toFixed(4);
	$("#amount").html(total.toFixed(4));
}


function calTotalWeight(parentId) {
	var weightSum = 0;
	for ( i = 1; i < 11; i++) {
		weight = 'weight' + i;
		weightSum += parseFloat(document.getElementById(parentId).getElementsByClassName(weight)[0].value);
	}
	// document.getElementById(parentId).getElementsByClassName("weight")[0].value = weightSum;
    $("#weight").html(weightSum);
}

function calTotalUnit(parentId) {
	var unitSum = 0;
	for ( i = 1; i < 11; i++) {
		unit = 'unit' + i;
		unitSum += parseFloat(document.getElementById(parentId).getElementsByClassName(unit)[0].value);
	}
	// document.getElementById(parentId).getElementsByClassName("unit")[0].value = unitSum;
    $("#unit").html(unitSum);
}

function calTotalPricePackage() {
	var weight = document.getElementById("total_weight").value;// addClient.lender_loanAmount.value;
	var pricePerWeight = document.getElementById("price_per_weight").value;// addClient.builder_packageAmount.value;
	var totalPricePackage = parseFloat(weight)* parseFloat(pricePerWeight);
	document.getElementById("total_package_price").value = totalPricePackage;
}

function calTotalPricePackage_1() {
	var weight = document.getElementById("total_weight_1").value;// addClient.lender_loanAmount.value;
	var pricePerWeight = document.getElementById("price_per_weight_1").value;// addClient.builder_packageAmount.value;
	var totalPricePackage = parseFloat(weight)* parseFloat(pricePerWeight);
	document.getElementById("total_package_price_1").value = totalPricePackage;
}

function calTotalPricePackage_2() {
	var weight = document.getElementById("total_weight_2").value;// addClient.lender_loanAmount.value;
	var pricePerWeight = document.getElementById("price_per_weight_2").value;// addClient.builder_packageAmount.value;
	var totalPricePackage = parseFloat(weight)* parseFloat(pricePerWeight);
	document.getElementById("total_package_price_2").value = totalPricePackage;
}

function calProductAmount(productquantity, productprice, productamount) {
	var quantity = document.getElementById(productquantity).value;// addClient.lender_loanAmount.value;
	var productPrice = document.getElementById(productprice).value;// addClient.builder_packageAmount.value;
	var productAmount = parseFloat(quantity)* parseFloat(productPrice);
	document.getElementById(productamount).value = productAmount;
	updateTotal('productTbl');
}

function updateTotal(tableId) {
	var table = document.getElementById(tableId);
	var total = 0;
	for (var r = 1, n = table.rows.length; r < n; r++) {
		total = parseFloat(total) + parseFloat(table.rows[r].cells[3].children[0].value);
	}
	document.getElementById("prm_sum").value = parseFloat(total) + parseFloat(document.getElementById("total_package_price").value);
}

function updateTotal() {
	document.getElementById("prm_sum").value = parseFloat(document.getElementById("total_package_price").value) + parseFloat(document.getElementById("total_package_price_1").value) + parseFloat(document.getElementById("total_package_price_2").value) + parseFloat(document.getElementById("add_fee").value);
}

function addRow(tableId) {
	var table = document.getElementById(tableId);
	var e = table.rows.length;
	var x = table.insertRow(e);
	var l = table.rows[e-1].cells.length;
	// x.innerHTML = "&nbsp;";
	for ( var c = 0, m = l; c < m; c++) {
		table.rows[e].insertCell(c);
		table.rows[e].cells[c].innerHTML = "&nbsp;&nbsp;";
	}
}

function addProductRow(tableId) {
    var table = document.getElementById(tableId);
    var tableRows = table.rows.length; // number of rows of tbale
    var e = parseInt(document.getElementById('noOfProducts').value)+1; // the index to create id for each row/cell
    var x = table.insertRow(tableRows); // add new rows to table
    var l = table.rows[tableRows-1].cells.length;
    // insert single column with default data
    table.rows[tableRows].insertCell(0);
    table.rows[tableRows].cells[0].innerHTML="<td><input name=\"product"+e+"name\" type=\"text\" id=\"product"+e+"name\" size=\"30\" placeholder=\"Product name\" /></td>";

    table.rows[tableRows].insertCell(1);
    table.rows[tableRows].cells[1].innerHTML="<td><input name=\"product"+e+"quantity\" type=\"number\" id=\"product"+e+"quantity\" value=\"0\" size=\"30\" onchange=\"calProductAmount('product"+e+"quantity', 'product"+e+"price', 'product"+e+"amount')\"/></td>";
    
    table.rows[tableRows].insertCell(2);
    table.rows[tableRows].cells[2].innerHTML="<td><input name=\"product"+e+"price\" type=\"text\" id=\"product"+e+"price\" value=\"0\" size=\"30\" onchange=\"calProductAmount('product"+e+"quantity', 'product"+e+"price', 'product"+e+"amount')\"/></td>";
    
    table.rows[tableRows].insertCell(3);
    table.rows[tableRows].cells[3].innerHTML="<td><input name=\"product"+e+"amount\" type=\"text\" id=\"product"+e+"amount\" value=\"0\" size=\"30\" readonly=\"true\"/></td>";
	    
    table.rows[tableRows].insertCell(4);
    table.rows[tableRows].cells[4].innerHTML="<td><input type=\"button\" onclick=\"addProductRow('productTbl')\" border=0 style='cursor:hand' value=\"+\"/></td>";
	    
    table.rows[tableRows].insertCell(5);            
    table.rows[tableRows].cells[5].innerHTML="<td><input type=\"button\" onclick=\"removeProductRow('productTbl', this)\" border=0 style='cursor:hand' value=\"-\"/></td>";
    
    document.getElementById('noOfProducts').value = e;
}

function removeProductRow(tableId, selectedRow) {
	var row = selectedRow.parentNode.parentNode;
	document.getElementById(tableId).deleteRow(row.rowIndex);
	//document.getElementById('noOfProducts').value = document.getElementById(tableId).rows.length - 1;
}

function DoNav(theUrl) {
    document.location.href = theUrl;
}

function ChangeColor(tableRow, highLight) {
    if (highLight) {
        tableRow.style.backgroundColor = '#dcfac9';
        tableRow.style.cursor='pointer';
    } else {
        tableRow.style.backgroundColor = 'white';
        tableRow.style.cursor='default';
    }
}

function openProductDescWindow() {
	var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) ) + dualScreenLeft;
    var top = ((height / 2) ) + dualScreenTop;
	childWindow = window.open("productdesc.html", 'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,directories=no,location=no,top=' + top + ', left=' + left);
	if (childWindow.opener == null) {
    	childWindow.opener = self;
	}
}

function openFeeWindow() {
	// Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) - (800 / 2)) + dualScreenLeft;
    var top = ((height / 2) - (400 / 2)) + dualScreenTop;
    
    childWindow = window.open("fee.html", 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=800,height=400,directories=no,location=no,top=' + top + ', left=' + left);
    if (childWindow.opener == null) {
        childWindow.opener = self;
    }
}