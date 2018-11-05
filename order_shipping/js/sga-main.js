// The root URL for the RESTful services
// This is standard host on linux
//var HOST = "localhost";

//For wamp, there is an issue, so the host should be as:
// refer this link: https://stackoverflow.com/questions/29945153/php-slim-api-404-not-found
var HOST = "localhost/order"
var PORT = "";
// var rootURL = "http://" + HOST + ":" + PORT + "/api";
var rootURL = "http://localhost/api"
// var rootURL = "http://" + HOST + "/api";
var currentOrder;
var sCustomers;
var rCustomers;
var customerTypes = {
    SENDER: 'sender',
    RECEIVER: 'receiver',
};

/**
 * Config action for button/event
 */
// Nothing to delete in initial application state
// $ ('#btnDelete').hide ();


/**
 * Define action to connect/get data from server
 */
function findAllOrders () {
    console.log ('find all orders');
    $.ajax ({
        type: 'GET',
        url: rootURL + "/orders",
        dataType: "json", // data type of response
        success: renderTableData,
        error: function (data, jqXHR, textStatus, errorThrown) {
            console.log ("failed", data);
        }
    });
}

function addOrder () {
    console.log ('Add a new order');
    if (!validation ()) {
        alert ('Add order fail at validation');
        return;
    }
    $.ajax ({
        type: 'POST',
        contentType: 'application/json',
        url: rootURL + "/orders/add",
        dataType: "json",
        data: formToJSON (),
        success: function (data, textStatus, jqXHR) {
            console.log ('Order created successfully');
            renderTableDataWithAdd (data);
            alert ('Order created successfully');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log ('add order error : ' + textStatus);
            alert ('add order error: ');
        }
    });
    return false;
}

function deleteOrder (selectedRow) {
    console.log ('Delete selected order');
    var c = confirm ("Continue delete?");
    if (c) {
        var orderId = JSON.stringify ({"orderId": selectedRow.data ()[0]});
        //get selected data here, invoke delete and remove also
        // var dataString = 'orderid=' + orderId;
        $.ajax ({
            url: rootURL + "/orders/delete",
            type: "POST",
            dataType: "json",
            data: orderId,
            success: function (data) {
                if (data.deleteStatus.toLowerCase () == "yes") {
                    selectedRow.remove ().draw ();
                } else {
                    alert ("can't delete the row")
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log ('Delete order error : ' + textStatus + jqXHR.responseText);
                alert ('delete order error: ' + textStatus + jqXHR);
            }
        });
    }
}

/**
 * Fill order to form for editing
 * @param selectedRow
 */
function fillOrder (selectedRow) {
    console.log ('Fill selected order');
    $.ajax ({
        type: 'GET',
        url: rootURL + "/orders/" + selectedRow.data ()[0],
        dataType: "json", // data type of response
        success: function (data) {
            $ ('#orderForm').find ('#oId').val (selectedRow.data ()[0]);
            $ ('#orderForm').find ('#oRow').val (selectedRow.index ());
            renderOrderEditData (data);
            $ ('#orderModal').modal ('show');
        },
        error: function (data, textStatus, jqXHR) {
            console.log ("failed", data);
        }
    });
}

/**
 * update order
 *
 * @param selectedRow
 */
function editOrder (table) {
    console.log ('Update selected order');
    $.ajax ({
        type: 'POST',
        contentType: 'application/json',
        url: rootURL + "/orders/update",
        dataType: "json",
        data: formToJSON(),
        success: function (data, textStatus, jqXHR) {
            console.log ('Order edited successfully');
            var updatedShippingData = $.map (data, function (value, index) {
                return [value];
            });
            updateOrderTableData (updatedShippingData, table, $ ("#oRow").val ());
            alert ('Shipping edited successfully');
            //Only close modal if edited successfully
            $ ("#orderModal").modal ('hide');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log ('Update shipping error : ' + textStatus);
            alert ('Update shipping error: ' + textStatus);
        }
    });
    return false;
}

//==================================================
/**
 * Find all customers and render
 */
function findAllCustomers (custType) {
    console.log ('find all senders');
    $.ajax ({
        type: 'GET',
        url: rootURL + '/customer/' + custType,
        dataType: "json", // data type of response
        success: function (data, textStatus, jqXHR) {
            renderCustomerTableData (null, data, custType);
        },
        error: function (data) {
            console.log ("failed", data);
        }
    });
}

/**
 * delete selected customer
 * @param selectedRow
 * @param custType
 */
function deleteCustomer (selectedRow, custType) {
    console.log ('Delete selected customer');
    var c = confirm ("Continue delete?");
    if (c) {
        var customerId = JSON.stringify ({"customerId": selectedRow.data ()[0]});
        $.ajax ({
            url: rootURL + "/customer/" + custType + "/delete",
            type: "POST",
            dataType: "json",
            data: customerId,
            success: function (data) {
                if (data.deleteStatus.toLowerCase () == "yes") {
                    selectedRow.remove ().draw ();
                } else {
                    alert ("can't delete the selected customer")
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log ('Delete customer error : ' + textStatus);
                alert ('delete customer error: ' + textStatus + jqXHR.toString ());
            }
        });
    }
}

/**
 * Update selected customer; Have put the table reference to update data later since we can't
 * access table if out of its scope. But need to check anyway
 */
function editCustomer (table) {
    console.log ('Update selected customer');
    var custType = $ ("#custType").val ();
    $.ajax ({
        type: 'POST',
        contentType: 'application/json',
        url: rootURL + "/customer/" + custType + "/update",
        dataType: "json",
        data: formCustomerDataToJSON (),
        success: function (data, textStatus, jqXHR) {
            console.log ('Customer edited successfully');
            var updatedCustomerData = $.map (data, function (value, index) {
                return [value];
            });
            applyData (table, updatedCustomerData, false, $ ("#rowId").val ());
            alert ('Customer edited successfully');
            //Only close modal if edited successfully
            $ ("#customerModal").modal ('hide');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log ('Update order error : ' + textStatus);
            alert ('Update order error: ' + textStatus);
        }
    });
    return false;
}

/**
 * Add customer
 *
 * @param table
 * @returns {boolean}
 */
function addCustomer (table) {
    console.log ('Add new customer');
    var custType = $ ("#custType").val ();
    $.ajax ({
        type: 'POST',
        contentType: 'application/json',
        url: rootURL + "/customer/" + custType + "/add",
        dataType: "json",
        data: formCustomerDataToJSON (),
        success: function (data, textStatus, jqXHR) {
            console.log ('Customer added successfully');
            // var updatedCustomerData = $.map (data, function (value, index) {
            //     if (index == 0) {
            //         return [parseInt(value)];
            //     }
            //     return [value];
            // });
            // var icon;
            // switch (custType) {
            //     case "receivers":
            //         icon = "<span style='display:inline-flex !important;'><a href='Javascript:void(0);' id='editRecv' class='glyphicon glyphicon-pencil'></a> <a href='Javascript:void(0);' id='deleteRecv' class='glyphicon glyphicon-trash'></a> </span>";
            //         break;
            //     case "senders":
            //         icon = "<span style='display:inline-flex !important;'><a href='Javascript:void(0);' id='editSender' class='glyphicon glyphicon-pencil'></a> <a href='Javascript:void(0);' id='deleteSender' class='glyphicon glyphicon-trash'></a> </span></a>";
            //         break;
            // }
            // updatedCustomerData.push (icon);
            // applyData(table, data, true, "");
            renderCustomerTableData (table, data, custType);
            alert ('Customer added successfully');
            //Only close modal if edited successfully
            $ ("#customerModal").modal ('hide');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log ('Add customer error : ' + textStatus + errorThrown);
            alert ('Add customer error: ' + textStatus);
        }
    });
    return false;
}

/**
 * Get all sender customers
 */
function getSCustomers () {
    console.log ('Get all sender customers');
    $.ajax ({
        type: 'GET',
        url: rootURL + "/customer/senders",
        dataType: "json", // data type of response
        success: function (data) {
            sCustomers = data;
            rederDataList (sCustomers, 'sendPhoneList');
        },
        error: function (data) {
            console.log ("failed to load or render senders", data);
        }
    });
}

/**
 * Get all receiver customers
 */
function getRCustomers () {
    console.log ('Get all receiver customers');
    $.ajax ({
        type: 'GET',
        url: rootURL + "/customer/receivers",
        dataType: "json", // data type of response
        success: function (data) {
            rCustomers = data;
            rederDataList (rCustomers, 'recvPhoneList');
        },
        error: function (data) {
            console.log ("failed to load or render receivers", data);
        }
    });
}

///////////////////////
/////////Shipping actions
//////////////////

/**
 * Find and fill all shippings
 */
function findAllShippings () {
    console.log ('find all shippings');
    $.ajax ({
        type: 'GET',
        url: rootURL + "/shipping/all",
        dataType: "json", // data type of response
        success: renderShippingTableData,
        error: function (data) {
            console.log ("failed", data);
        }
    });
}

/**
 * Add shipping
 */
function addShipping (selectedRows) {
    console.log ('Add new shipping');
    $.ajax ({
        type: 'POST',
        contentType: 'application/json',
        url: rootURL + "/shipping/add",
        dataType: "json",
        data: formShippingDataToJSON (selectedRows),
        success: function (data, textStatus, jqXHR) {
            console.log ('Shipping added successfully');
            renderShippingTableData (data);
            alert ('Shipping added successfully');
            //Only close modal if edited successfully
            $ ("#shippingModal").modal ('hide');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log ('Add shipping error : ' + textStatus + errorThrown);
            alert ('Add shipping error: ' + textStatus);
        }
    });
    return false;
}

/**
 * Delete shipping
 */
function deleteShipping (selectedRow) {
    console.log ('Delete selected shipping');
    var c = confirm ("Continue delete?");
    if (c) {
        var shippingId = JSON.stringify ({"shippingId": selectedRow.data ()[0]});
        $.ajax ({
            url: rootURL + "/shipping/delete",
            type: "POST",
            dataType: "json",
            data: shippingId,
            success: function (data) {
                if (data.deleteStatus.toLowerCase () == "yes") {
                    selectedRow.remove ().draw ();
                } else {
                    alert ("can't delete the selected shipping")
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log ('Delete shipping error : ' + textStatus);
                alert ('delete shipping error: ' + textStatus + jqXHR.toString ());
            }
        });
    }
}

/**
 * Fill order details for selected shipping
 *
 * @param selectedRow
 */
function fillOrderDetails (selectedRow) {
    console.log ("Filling order details to update shipping");
    $ ('#shippingDetails').find ('#sRowId').val (selectedRow.index ());
    fillShippingDataForUpdating (selectedRow.data ());
}

/**
 * Update selected shipping
 */
function editShipping (selectedRow) {
    console.log ('Update selected shipping');
    $.ajax ({
        type: 'POST',
        contentType: 'application/json',
        url: rootURL + "/shipping/update",
        dataType: "json",
        data: formShippingDataToJSON (selectedRow),
        success: function (data, textStatus, jqXHR) {
            console.log ('Shipping edited successfully');
            var updatedShippingData = $.map (data, function (value, index) {
                return [value];
            });
            updateShippingTableData (updatedShippingData, $ ("#sRowId").val ());
            alert ('Shipping edited successfully');
            //Only close modal if edited successfully
            $ ("#shippingModal").modal ('hide');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log ('Update shipping error : ' + textStatus);
            alert ('Update shipping error: ' + textStatus);
        }
    });
    return false;
}

/**
 * Adding new data after adding successfully for order table; only return  order ID + used input data
 * @param data
 */
function renderTableDataWithAdd (data) {
    // JAX-RS serializes an empty list as null, and a 'collection of one' as an object (not an 'array of one')
    var list = data == null ? [] : (data.addedOrder instanceof Array ? data.addedOrder : [data.addedOrder]);
    var table = $ ('#orderTbl').DataTable ();
    var orderDetailstable = $ ('#orderDetailsTbl').DataTable ();
    var extraButton = "<span style='display:inline-flex !important;'><a href='' class='order-edit glyphicon glyphicon-pencil'></a> <a href='' class='order-delete glyphicon glyphicon-trash'></a> <a href='Javascript:void(0);' id='printOrder' class='glyphicon glyphicon-print'></a>  </span>";
    // var weight = order.weight;
    $.each (list, function (index, order) {
        var weight = order.weight;
        var amount = order.amount;
        if (!weight)
            weight = 0;
        if (!amount)
            amount = 0;
        table.row.add ([order.orderId, order.date, order.sendName, order.sendPhone, order.sendAddr, order.recvName, order.recvPhone, order.recvAddr, weight, amount, extraButton]).draw ();
        orderDetailstable.row.add ([order.orderId, order.orderId, order.date, order.sendName, order.recvName, weight, amount]).draw ();
    });
}

/**
 * Fill order data to edit
 *
 * @param data
 */
function renderOrderEditData (data) {
    var list = data == null ? [] : (data.gotOrder instanceof Array ? data.gotOrder : [data.gotOrder]);
    $.each (list, function (index, order) {
        $ ('#orderForm').find ('#sendPhone').val (order.s_phone);
        $ ('#orderForm').find ('#sendName').val (order.s_name);
        $ ('#orderForm').find ('#sendAddr').val (order.s_address);

        $ ('#orderForm').find ('#recvPhone').val (order.r_phone);
        $ ('#orderForm').find ('#recvName').val (order.r_name);
        $ ('#orderForm').find ('#recvAddr').val (order.r_address);

        var orderDate = order.date.split ("-");
        $ ('#orderForm').find ('#day').val (orderDate[2]);
        $ ('#orderForm').find ('#month').val (orderDate[1]);
        $ ('#orderForm').find ('#year').val (orderDate[0]);

        $ ('#orderForm').find ('#productDesc').val (order.product_desc);
        if (order.status == 0) {
            $ ('input:radio[name=status]')[0].checked = true;
        }

        if (order.status == 1) {
            $ ('input:radio[name=status]')[1].checked= true;
        }

        var productDetails = order.orderDetails;
        var i = 1;
        productDetails.forEach (function (product) {
            $ ('#orderForm').find ('#productDesc' + i).val (product.p_desc);
            $ ('#orderForm').find ('#weight' + i).val (product.weight);
            $ ('#orderForm').find ('#unit' + i).val (product.unit);
            $ ('#orderForm').find ('#price' + i).val (product.price);
            $ ('#orderForm').find ('#total' + i).val (product.total);
        });
        $ ('#orderForm').find ('#weight').val (order.weight);
        $ ('#orderForm').find ('#amount').val (order.total);

    });
}

/**
 * Render JSON data getting from server and render to html table
 * Will render for:
 * 1. ordered data
 * 2. shipping data
 * 3. delivered data
 * @param data
 */
function renderTableData (data) {
    // JAX-RS serializes an empty list as null, and a 'collection of one' as an object (not an 'array of one')
    var list = data == null ? [] : (data.orders instanceof Array ? data.orders : [data.orders]);
    var table = $ ('#orderTbl').DataTable ();
    // var shippingTable = $('#shippingTbl').DataTable();
    var deliveredTable = $ ('#deliveredTbl').DataTable ();
    var orderDetailsTable = $ ('#orderDetailsTbl').DataTable ();

    table.clear ().draw ();
    deliveredTable.clear ().draw ();
    var extraButton = "<span style='display:inline-flex !important;'><a href='' class='order-edit glyphicon glyphicon-pencil'></a> <a href='' class='order-delete glyphicon glyphicon-trash'></a> <a href='Javascript:void(0);' id='printOrder' class='glyphicon glyphicon-print'></a>  </span>";
    // var weight = order.weight;
    $.each (list, function (index, order) {
        var weight = order.weight;
        var amount = order.total;
        /* this will validate if value is not:
            null
            undefined
            NaN
            empty string ("")
            false
            0
        */
        if (!weight)
            weight = 0;
        if (!amount)
            amount = 0;
        // divide data to 2 parts: ordered/delivered (0/1)
        if (order.status == 0)
            table.row.add ([order.id, order.date, order.s_name, order.s_phone, order.s_address, order.r_name, order.r_phone, order.r_address, weight, amount]).draw ();
        else if (order.status == 1)
            deliveredTable.row.add ([order.id, order.date, order.s_name, order.s_phone, order.s_address, order.r_name, order.r_phone, order.r_address, weight, amount]).draw ();
        //reserve first column for checkbox with order id as well
        console.log ("fill data for order details table of shipping");
        orderDetailsTable.row.add ([order.id, order.id, order.date, order.s_name, order.r_name, weight, amount]).draw ();
    });
}

// Helper function to serialize all the form fields into a JSON string
function formToJSON () {
    var productDetails = new Array ();
    for (var i = 1; i <= 10; i++) {
        var productDetail = new Object ();
        if (!$ ('#productDesc' + i).val ())
            continue;
        productDetail.desc = $ ('#productDesc' + i).val ();
        productDetail.weight = $ ('#weight' + i).val ();
        productDetail.unit = $ ('#unit' + i).val ();
        productDetail.price = $ ('#price' + i).val ();
        productDetail.total = $ ('#total' + i).val ();
        productDetails[i - 1] = productDetail;
    }
    var json = JSON.stringify ({
        "id":$('#oId').val(),
        "date": $ ('#year').val () + "-" + $ ("#month").val () + "-" + $ ("#day").val (),
        "sendName": $ ('#sendName').val (),
        "sendPhone": $ ('#sendPhone').val (),
        "sendAddr": $ ('#sendAddr').val (),
        "recvName": $ ('#recvName').val (),
        "recvPhone": $ ('#recvPhone').val (),
        "recvAddr": $ ('#recvAddr').val (),
        "weight": $ ('#weight').html (),
        "amount": $ ('#amount').html (),
        "productDesc": $ ('#productDesc').val (),
        "fileNames": $ ('#uploaded').val (),
        "status": $('input:radio[name=status]:checked').val(),
        "userId": "5", //TODO: temp value to pass request, need to put real value
        "recvId": $ ("#recvPhoneList option[value='" + $ ('#recvPhone').val () + "']").attr ('data-id'),
        "sendId": $ ("#sendPhoneList option[value='" + $ ('#sendPhone').val () + "']").attr ('data-id'),
        "productDetails": productDetails
    });
    return json;
}

/**
 * Update order table after editting
 *
 * @param data
 */
function updateOrderTableData (data, table, rowId) {
    if (table.row (rowId).length > 0) {
        var totalColumn = orderTable.columns ().count ();
        for (var i = 0; i < totalColumn - 1; i++) {
            orderTable.cell (rowId, i).data (data[i]);
        }
        // table.row (rowId).data ([data[0],data[1],data[2],data[3]]).invalidate ();
    } else {
        //Add row data if new
        table.row.add (data);
    }
    // }
    //Redraw table maintaining paging
    table.draw (false);
}

// Helper function to serialize all the form fields into a JSON string
function formCustomerDataToJSON () {
    var customer = JSON.stringify ({
        "id": $ ('#customerDetails').find ('#tmpId').val (),
        "cust_name": $ ('#customerDetails').find ('#custName').val (),
        "phone": $ ('#customerDetails').find ('#custPhone').val (),
        "address": $ ('#customerDetails').find ('#custAddress').val ()
    });
    return customer;
}

/**
 * Render JSON data to datalist
 */
function rederDataList (data, datalist) {
    // JAX-RS serializes an empty list as null, and a 'collection of one' as an object (not an 'array of one')
    var list = data == null ? [] : (data.customers instanceof Array ? data.customers : [data.customers]);
    $.each (list, function (index, customer) {
        $ ('#' + datalist + '').append ("<option data-id='" + customer.id + "'value='" + customer.phone + "'>"); // Not working.
    });
}

/**
 * Render data for specific customer table
 *
 * @param data
 * @param custType
 */
function renderCustomerTableData (table, data, custType) {
    var list = data == null ? [] : (data.customers instanceof Array ? data.customers : [data.customers]);
    var custTable;
    if (!table) {
        switch (custType) {
            case "receivers":
                custTable = $ ('#recvTbl').DataTable ();
                break;
            case "senders":
                custTable = $ ('#senderTbl').DataTable ();
                break;

        }
    } else {
        custTable = table;
    }
    $.each (list, function (index, customer) {
        custTable.row.add ([customer.id, customer.cust_name, customer.phone, customer.address]).draw ();

    });
}

function getCustomersAsJson (data) {
    var list = data == null ? [] : (data.customers instanceof Array ? data.customers : [data.customers]);
    var returnMap = {};
    $.each (list, function (index, customer) {
        returnMap[customer.phone] = customer;
    });
    return JSON.stringify (returnMap);
}

// method to handle datatable with selected row
function applyData (table, data, append, rowId) {

    //Quickly appends new data rows.  Does not update rows
    if (append == true) {
        var list = data == null ? [] : (data.customers instanceof Array ? data.customers : [data.customers]);
        $.each (list, function (index, customer) {
            table.rows.add (customer).draw ();
        });
        // table.rows.add (data);
        //Locate and update rows by rowId or add if new
    } else {
        // var index;
        // for (var x = 0; x < data.length; x++) {
        //Find row index by rowId if row exists - use id (customer, order ...)
        // index = table.row (rowId);

        //Update row data if existing, and invalidate for redraw
        if (table.row (rowId).length > 0) {
            var totalColumn = table.columns ().count ();
            for (var i = 0; i < totalColumn - 1; i++) {
                table.cell (rowId, i).data (data[i]);
            }
            // table.row (rowId).data ([data[0],data[1],data[2],data[3]]).invalidate ();
        } else {
            //Add row data if new
            table.row.add (data);
        }
        // }
        //Redraw table maintaining paging
        table.draw (false);
    }
}

/**
 * Form shipping data to json for server
 */
function formShippingDataToJSON (selectedRows) {
    var orderDetails = new Array ();
    $ ('#orderDetailsTbl').DataTable ().rows ().every (function (rowIdx, tableLoop, rowLoop) {
        var data = this.data ();

        // Get row ID
        var rowId = data[0];

        // // Determine whether row ID is in the list of selected row IDs
        // var index = $.inArray(rowId, selectedRows);

        // If checkbox is checked and row ID is not in list of selected row IDs
        if (this.checked) {//} && index === -1) {
            var order = new Object ();
            order.id = rowId;
            order.date = data[2];
            order.sender = data[3];
            order.receiver = data[4];
            order.weight = data[5];
            order.amount = data[6];
            orderDetails[i - 1] = order;
            // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
        }
    });
    var shipping = JSON.stringify ({
        "id": $ ('#shippingDetails').find ('#tmpSId').val (),
        "date": $ ('#shippingDetails').find ('#shippingDate').val (),
        "mawb": $ ('#shippingDetails').find ('#flightNo').val (),
        "hawb": $ ('#shippingDetails').find ('#shippingHawb').val (),
        "pieces": $ ('#shippingDetails').find ('#shippingUnit').val (),
        "weight": $ ('#shippingDetails').find ('#shippingWeight').val (),
        "amount": $ ('#shippingDetails').find ('#shippingTotalAmount').html (),
        "orderDetails": selectedRows
    });
    return shipping;
}

/**
 * Render data for shipping table
 *
 * @param data
 */
function renderShippingTableData (data) {
    var list = data == null ? [] : (data.addedShipping instanceof Array ? data.addedShipping : [data.addedShipping]);
    if (list[0] == undefined) { // --> mean that there is no data for added shipping
        list = data == null ? [] : (data.shippings instanceof Array ? data.shippings : [data.shippings]);
    }
    var shippingTable = $ ("#shippingTbl").DataTable ();
    $.each (list, function (index, shipping) {
        shippingTable.row.add ([shipping.id, shipping.date, shipping.mawb, shipping.hawb, shipping.pieces, shipping.weight, shipping.amount, shipping.orderDetails]).draw ();
    });
}

/**
 * Update shipping table after editting
 *
 * @param data
 */
function updateShippingTableData (data, rowId) {
    var shippingTable = $ ('#shippingTbl').DataTable ();
    if (shippingTable.row (rowId).length > 0) {
        var totalColumn = shippingTable.columns ().count ();
        for (var i = 0; i < totalColumn - 1; i++) {
            shippingTable.cell (rowId, i).data (data[i]);
        }
        // table.row (rowId).data ([data[0],data[1],data[2],data[3]]).invalidate ();
    } else {
        //Add row data if new
        shippingTable.row.add (data);
    }
    // }
    //Redraw table maintaining paging
    shippingTable.draw (false);
}

/**
 * Fill data to order details for updating
 * @param data
 */
function fillShippingDataForUpdating (selectedData) {
    // var list = data == null ? [] : (data.shippingDetails instanceof Array ? data.shippingDetails : [data.shippingDetails]);
    // var orderDetails = list.orderDetails;

    // fill flight form data
    $ ('#shippingDetails').find ('#tmpSId').val (selectedData[0]);
    $ ('#shippingDetails').find ('#shippingDate').val (selectedData[1]);
    $ ('#shippingDetails').find ('#flightNo').val (selectedData[2]);
    $ ('#shippingDetails').find ('#shippingHawb').val (selectedData[3]);
    $ ('#shippingDetails').find ('#shippingUnit').val (selectedData[4]);
    $ ('#shippingDetails').find ('#shippingWeight').val (selectedData[5]);
    $ ('#shippingDetails').find ('#shippingTotalAmount').html (selectedData[6]);

    $ ('#orderDetailsTbl').DataTable ().rows ().every (function () {
        // console.log (this.data ()); --> enable logging all order details data
        // var row = $(this).closest('tr');

        var data = this.data ();

        // Get row ID
        // var rowId = data[0];
        var orderId = data[1];

        // Determine whether row ID is in the list of selected row IDs
        var index = $.inArray (orderId, selectedData[7]);

        if (!this.checked && index !== -1) {
            $ (this.node ()).find ('input[type="checkbox"]').prop ('checked', true);
            $ (this.node ()).addClass ('selected');
        } else {
            $ (this.node ()).find ('input[type="checkbox"]').prop ('checked', false);
            $ (this.node ()).removeClass ('selected');
        }
        // $ ('#orderDetailsTbl').DataTable ().draw(false).node();
    });
}


//TODO List:
// #1. loss icon in all tables
// #2: testing order (adding, edit for new order, delivered order)
// #3: deploy to server
// #4: check if needed one more status for order
// #5: fill uploaded files to orders
// #6: login user, :(
// #7: print order, :(