$(document).ready(function () {
    $("#btnLogin").click(function () {
        $("#login_err").css('display', 'none', 'important');
        username = $("#inputUsername").val();
        password = $("#inputPassword").val();
        $.ajax({
            type: "POST",
            url: "http://localhost:8808/api/login/" + username + "/" + password,
            //data: username + "/" + password,
            success: function (result) {
                if (result == 'true') {
                    //$("#add_err").result("right username or password");
                    window.location = "index.html";
                } else {
                    $("#login_err").css('display', 'inline', 'important');
                    $("#login_err").html("<img src='images/alert.png' />Wrong username or password");
                }
            },
            beforeSend: function (result) {
                $("#login_err").css('display', 'inline', 'important');
                $("#login_err").html("<img src='images/ajax-loader.gif' /> Loading...")
            },
            error: function (result) {
                $("#login_err").css('display', 'inline', 'important');
                $("#login_err").html("<img src='images/alert.png' />Login failed!");
            }
        });
        return false;
    });
});
