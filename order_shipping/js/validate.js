function LTrim (str) {
    if (str == null) {
        return null;
    }
    for (var i = 0; str.charAt (i) == " "; i++)
        ;
    return str.substring (i, str.length);
}

function RTrim (str) {
    if (str == null) {
        return null;
    }
    for (var i = str.length - 1; str.charAt (i) == " "; i--)
        ;
    return str.substring (0, i + 1);
}

function Trim (str) {
    return RTrim (LTrim (str));
}

function checknumber (value) {
    var valid = "0123456789.";
    var chars;
    var result = true;
    if (value.length == 0)
        return false;
    var dotcount = 0;
    for (i = 0; i < value.length && result == true; i++) {
        chars = value.charAt (i);
        if (valid.indexOf (chars) == -1 || dotcount > 1) {
            result = false;
            dotcount = 0;
        } else {
            if (chars == '.') {
                dotcount++;
            }
            result = true;
        }
    }
    return result;
}

function validation () {
    if (document.getElementById ("sendName").value.length == 0) {
        alert ("Please enter sender name.");
        document.getElementById ("sendName").focus ();
        return false;
    } else if (document.getElementById ("sendPhone").value.length == 0) {
        alert ("Please enter sender phone.");
        document.getElementById ("sendPhone").focus ();
        return false;
    } else if (document.getElementById ("sendAddr").value.length == 0) {
        alert ("Please enter sender address.");
        document.getElementById ("sendAddr").focus ();
        return false;
    } else if (document.getElementById ("recvName").value.length == 0) {
        alert ("Please enter receiver name.");
        document.getElementById ("recvName").focus ();
        return false;
    } else if (document.getElementById ("recvPhone").value.length == 0) {
        alert ("Please enter receiver phone.");
        document.getElementById ("recvPhone").focus ();
        return false;
    } else if (document.getElementById ("recvAddr").value.length == 0) {
        alert ("Please enter receiver address.");
        document.getElementById ("recvAddr").focus ();
        return false;
        // check date later
    // } else if (document.getElementById ("datepicker").value.length == 0) {
    //     alert ("Please enter the date.");
    //     document.getElementById ("datepicker").focus ();
    //     return false;
    } else if (document.getElementById ("productDesc").value.length == 0) {
        alert ("Please enter the product description.");
        document.getElementById ("productDesc").focus ();
        return false;
    }
    // we can  add code to validate product details here later.
    // Just loop all rows and stop if one of them has data
    // for (var i = 1; i <= 10; i++) {
    //     if (document.getElementById ("proDesc" +i).value.length == 0) {
    //     }
    // }
    return true;
}

function validateProductTable () {
    var table = document.getElementById (tableId);
    var total = 0;
    for (var r = 1, n = table.rows.length; r < n; r++) {
        if (table.rows[r].cells[0].children[0].value.length == 0) {
            alert ("Please check and enter product name in product table.");
            return false;
        }
        if (checknumber (table.rows[r].cells[1].children[0].value) == false) {
            alert ("Please check and enter product quantity in product table.");
            return false;
        }
        if (checknumber (table.rows[r].cells[2].children[0].value) == false) {
            alert ("Please check and enter product price in product table.");
            return false;
        }
        if (checknumber (table.rows[r].cells[3].children[3].value) == false) {
            alert ("Please check product amount for each product in product table.");
            return false;
        }
    }
    return true;
}

function validateNumber (value, string, element) {
    if (checknumber (value) == false) {
        alert ("Please enter " + string + " in number.")
        document.getElementById (element).focus ();
        return false;
    }
    return true;
}

/**
 * Validate and set date for order
 */
function validateDate () {
    var ysel = document.getElementsByName ("year")[0],
        msel = document.getElementsByName ("month")[0],
        dsel = document.getElementsByName ("day")[0];
    for (var i = 2016; i <= 2030; i++) {
        var opt = new Option ();
        opt.value = opt.text = i;
        ysel.add (opt);
    }
//	if (document.addEventListener){
//		ysel.addEventListener('change', validate_date, false); 
//	} else if (ysel.attachEvent){
//		ysel.attachEvent('onchange', validate_date);
//	}
//	
//	if (document.addEventListener){
//		msel.addEventListener('change', validate_date, false); 
//	} else if (msel.attachEvent){
//		msel.attachEvent('onchange', validate_date);
//	}
//	ysel.addEventListener("change", validate_date);
//	msel.addEventListener("change", validate_date);
}

function validate_date () {
    var ysel = document.getElementsByName ("year")[0],
        msel = document.getElementsByName ("month")[0],
        dsel = document.getElementsByName ("day")[0];
    var y = +ysel.value, m = msel.value, d = dsel.value;
    if (m === "2")
        var mlength = 28 + (!(y & 3) && ((y % 100) !== 0 || !(y & 15)));
    else var mlength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][m - 1];
    dsel.length = 0;
    for (var i = 1; i <= mlength; i++) {
        var opt = new Option ();
        if (i < 10)
            i = '0' + i;
        opt.value = opt.text = i;
        if (i == d) opt.selected = true;
        dsel.add (opt);
    }
}

function selectDate () {
    var today = new Date ();
    var dd = today.getDate ();
    var mm = today.getMonth () + 1; //January is 0!
    var yyyy = today.getFullYear ();

    if (dd < 10) {
        dd = '0' + dd;
    }

    if (mm < 10) {
        mm = '0' + mm;
    }
    var ysel = document.getElementsByName ("year")[0],
        msel = document.getElementsByName ("month")[0],
        dsel = document.getElementsByName ("day")[0];
    ysel.value = yyyy;
    dsel.value = dd;
    msel.value = mm;
}

function selectDateForData (date) {
    var ysel = document.getElementsByName ("year")[0],
        msel = document.getElementsByName ("month")[0],
        dsel = document.getElementsByName ("day")[0];
    dsel.value = date[0];
    msel.value = date[1];
    ysel.value = date[2];
}
