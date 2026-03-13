
$("#loginBtn").click(function() {
    let email= $("#email").val();
    let password= $("#password").val();

    console.log("ccccbbbbb");
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
        window.location.href = "../center/dashboard-center.html";
    }

    else{
        window.location.href = "../user/dashboard-user.html";
    }

}
});
});