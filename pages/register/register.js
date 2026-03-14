$("#regBtn").click(function () {
  let name = $("#name").val();
  let email = $("#email").val();
  let password = $("#password").val();
  let role = $("#role").val();

  console.log("aaaaaaa");
  $.ajax({
    url: "../../api/userApi.php",
    method: "POST",
    data: {
      name: name,
      password: password,
      email: email,
      role: role,
      methodName: "register",
    },
    success: function (response) {
      if (response.status == "success") {
        if (response.user.role.trim() == "center") {
          window.location.href = "../center/dashboard-center.php";
        } else {
          window.location.href = "../user/dashboard-user.php";
        }
      }
    },
  });
});
