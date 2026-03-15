$("#loginBtn").click(function () {
  let email = $("#email").val();
  let password = $("#password").val();

  if (email === "" || password === "") {
    showToast("Molimo unesite email i lozinku", "warning");
    return;
  }

  $.ajax({
    url: "../../api/userApi.php",
    method: "POST",
    data: {
      password: password,
      email: email,
      methodName: "login",
    },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        showToast("Uspešno ste se prijavili! Preusmeravanje...", "success");
        setTimeout(function () {
          if(response.user && response.user.role == "admin")
                    {
                        window.location.href = "../admin/dashboard-admin.php";
                    }
                    if (response.user && response.user.role == "center") {
                        window.location.href = "../center/dashboard-center.php";
                    } if  (response.user && response.user.role == "user") {
                        window.location.href = "../user/dashboard-user.php";
                    }
                }, 1000);
      } else {
        showToast(response.message || "Greška pri logovanju", "error");
      }
    },
    error: function (xhr) {
      console.error("Server response:", xhr.responseText);
      showToast("Došlo je do greške na serveru. Pokušajte ponovo.", "error");
    },
  });
});
