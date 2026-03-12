
$("#loginBtn").click(function() {
    let email= $("#email").val();
    let password= $("#password").val();

    console.log("abbbbbbbbbbb");
    $.ajax({
    url: "../../api/userApi.php",
    method: "POST",
    data: {
        password:password,
        email:email,
        methodName:"login"
    },
    success: function(response) {

        console.log(response.user);

    }
});
});