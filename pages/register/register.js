
$("#regBtn").click(function() {
    let name= $("#name").val();
    let email= $("#email").val();
    let password= $("#password").val();
    let role= $("#role").val();

    console.log("aaaaaaa");
    $.ajax({
    url: "../../api/userApi.php",
    method: "POST",
    data: {
        name:name,
        password:password,
        email:email,
        role:role,
        methodName:"register"
    },
    success: function(response) {

        console.log(response.status);

    }
});
});