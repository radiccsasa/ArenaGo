
$("#loginBtn").click(function() {
    let email= $("#email").val();
    let password= $("#password").val();

    $.ajax({
    url: "../../api/userApi.php",
    method: "POST",
    data: {
        password:password,
        email:email,
        methodName:"login"
    },



    
    success:function(response){

    if(response.user.role == "center"){
        window.location.href = "../center/dashboard-center.php";
    }

    else{
        window.location.href = "../user/dashboard-user.php";
    }

}
});
});