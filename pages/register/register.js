$("#regBtn").click(function () {
  let name = $("#name").val();
  let email = $("#email").val();
  let password = $("#password").val();
  let role = $("#role").val();

  // Validacija
  if (name === "" || email === "" || password === "") {
    showToast("Molimo popunite sva polja", "warning");
    return;
  }

  if (password.length < 6) {
    showToast("Lozinka mora imati najmanje 6 karaktera", "warning");
    return;
  }

  if (!email.includes("@") || !email.includes(".")) {
    showToast("Unesite ispravnu email adresu", "warning");
    return;
  }

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
    dataType: "json",
    success: function (response) {
      console.log("Server odgovor:", response);

      if (response.status == "success") {
        showToast("Uspešno ste se registrovali! Preusmeravanje...", "success");

        setTimeout(function () {
          if (response.user && response.user.role == "center") {
            window.location.href = "../center/dashboard-center.php";
          } else {
            window.location.href = "../user/dashboard-user.php";
          }
        }, 1500);
      } else {
        showToast(response.message || "Greška pri registraciji", "error");
      }
    },
    error: function (xhr) {
      console.error("Server response:", xhr.responseText);
      showToast("Došlo je do greške na serveru. Pokušajte ponovo.", "error");
    },
  });
});
