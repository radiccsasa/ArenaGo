$(document).ready(function () {
  loadSports();

  const mode = $("#mode").val();
  const termId = $("#termId").val();

  if (mode === "edit" && termId) {
    loadTermData(termId);
  }

  $("#saveBtn").on("click", function () {
    const mode = $("#mode").val();

    if (mode === "edit") {
      updateTerm();
    } else {
      addTerm();
    }
  });
});

function addTerm() {
  const date = $("#date").val();
  const time = $("#time").val();
  const price = $("#price").val();
  const discount = $("#discount").val() || 0;
  const capacity = $("#capacity").val();
  const sport = $("#sportId").val();

  if (!date || !time || !price || !capacity || !sport) {
    showToast("Molimo popunite sva polja!", "warning");
    return;
  }

  $.ajax({
    url: "../../api/termApi.php",
    method: "POST",
    data: {
      methodName: "addTerm",
      date: date,
      time: time,
      price: price,
      discount: discount,
      capacity: capacity,
      sport: sport,
    },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        showToast("Termin je uspesno dodat!", "success");
        window.location.href = "/ArenaGo/pages/center/dashboard-center.php";
      } else {
        showToast(
          "Greška: " + (response.message || "Nepoznata greška"),
          "error",
        );
      }
    },
    error: function (xhr, status, error) {
      showToast("Došlo je do greške na serveru", "error");
    },
  });
}

function updateTerm() {
  const termId = $("#termId").val();
  const date = $("#date").val();
  const time = $("#time").val();
  const price = $("#price").val();
  const discount = $("#discount").val() || 0;
  const capacity = $("#capacity").val();
  const sport = $("#sportId").val();

  if (!date || !time || !price || !capacity || !sport) {
    showToast("Molimo popunite sva polja!", "warning");
    return;
  }

  $.ajax({
    url: "../../api/termApi.php",
    method: "POST",
    data: {
      methodName: "updateTerm",
      term_id: termId,
      date: date,
      time: time,
      price: price,
      discount: discount,
      capacity: capacity,
      sport: sport,
    },
    dataType: "json",
    success: function (response) {
      console.log("Update response:", response);
      if (response.status === "success") {
        showToast("Termin uspešno ažuriran!", "success");
        setTimeout(function() {
          window.location.href = "/ArenaGo/pages/center/dashboard-center.php";
        }, 500);
      } else {
        showToast(
          "Greška: " + (response.message || "Nepoznata greška"),
          "error",
        );
      }
    },
    error: function (xhr, status, error) {
      showToast("Došlo je do greške na serveru", "error");
    },
  });
}

function loadSports() {
  $.ajax({
    url: "../../api/termApi.php",
    method: "POST",
    data: {
      methodName: "getSports",
    },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        let options = '<option value="">Izaberi sport</option>';

        response.data.forEach(function (sport) {
          options += `<option value="${sport.id}">${sport.name}</option>`;
        });

        $("#sportId").html(options);
      } else {
        showToast("Greska pri ucitavanju sportova", "error");
      }
    },
    error: function (xhr, status, error) {
      showToast("Greska pri ucitavanju sportova", "error");
    },
  });
}

function loadTermData(termId) {
  $.ajax({
    url: "../../api/termApi.php",
    method: "POST",
    data: {
      methodName: "getTerm",
      term_id: termId,
    },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        const term = response.data;

        // Popuni formu
        $("#date").val(term.date);
        $("#time").val(term.time);
        $("#price").val(term.price);
        $("#discount").val(term.action_discount || 0);
        $("#capacity").val(term.capacity);
        $("#sportId").val(term.sport_id);
      } else {
        showToast(
          "Greška pri učitavanju termina: " + response.message,
          "error",
        );
        window.location.href = "dashboard-center.php";
      }
    },
    error: function (xhr, status, error) {
      showToast("Došlo je do greške pri učitavanju podataka", "error");
      window.location.href = "dashboard-center.php";
    },
  });
}
