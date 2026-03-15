// $(document).ready(function () {
//   loadSports();
//   $("#addBtn").on("click", () => {
//     const date = $("#date").val();
//     const time = $("#time").val();
//     const price = $("#price").val();
//     const discount = $("#discount").val();
//     const capacity = $("#capacity").val();
//     const sport = $("#sportId").val();

//     $.ajax({
//       url: "../../api/termApi.php",
//       method: "POST",
//       data: {
//         methodName: "addTerm",
//         date: date,
//         time: time,
//         price: price,
//         discount: discount,
//         capacity: capacity,
//         sport: sport,
//       },
//       dataType: "json",
//       success: function (response) {
//         console.log(response.status);
//         location.href = "/ArenaGo/pages/center/dashboard-center.php";
//       },
//     });
//   });
// });

// function loadSports() {
//   $.ajax({
//     url: "../../api/termApi.php",
//     method: "POST",
//     data: {
//       methodName: "getSports",
//     },
//     success: function (response) {
//       let options = '<option value="">Izaberi sport</option>';

//       response.data.forEach(function (sport) {
//         options += `<option value="${sport.id}">${sport.name}</option>`;
//       });

//       $("#sportId").html(options);
//     },
//   });
// }

$(document).ready(function () {
  // Učitaj sportove
  loadSports();

  // Proveri da li je edit mod
  const mode = $("#mode").val();
  const termId = $("#termId").val();

  if (mode === "edit" && termId) {
    // Učitaj podatke termina za izmenu
    loadTermData(termId);
  }

  // Na klik dugmeta
  $("#saveBtn").on("click", function () {
    const mode = $("#mode").val();

    if (mode === "edit") {
      updateTerm();
    } else {
      addTerm();
    }
  });
});

// Funkcija za dodavanje termina
function addTerm() {
  const date = $("#date").val();
  const time = $("#time").val();
  const price = $("#price").val();
  const discount = $("#discount").val() || 0;
  const capacity = $("#capacity").val();
  const sport = $("#sportId").val();

  // Validacija
  if (!date || !time || !price || !capacity || !sport) {
    alert("Molimo popunite sva polja!");
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
      console.log("Add response:", response);
      if (response.status === "success") {
        alert("Termin uspešno dodat!");
        window.location.href = "/ArenaGo/pages/center/dashboard-center.php";
      } else {
        alert("Greška: " + (response.message || "Nepoznata greška"));
      }
    },
    error: function (xhr, status, error) {
      console.error("Error adding term:", error);
      alert("Došlo je do greške na serveru");
    },
  });
}

// Funkcija za izmenu termina
function updateTerm() {
  const termId = $("#termId").val();
  const date = $("#date").val();
  const time = $("#time").val();
  const price = $("#price").val();
  const discount = $("#discount").val() || 0;
  const capacity = $("#capacity").val();
  const sport = $("#sportId").val();

  // Validacija
  if (!date || !time || !price || !capacity || !sport) {
    alert("Molimo popunite sva polja!");
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
        alert("Termin uspešno ažuriran!");
        window.location.href = "/ArenaGo/pages/center/dashboard-center.php";
      } else {
        alert("Greška: " + (response.message || "Nepoznata greška"));
      }
    },
    error: function (xhr, status, error) {
      console.error("Error updating term:", error);
      alert("Došlo je do greške na serveru");
    },
  });
}

// Funkcija za učitavanje sportova
function loadSports() {
  $.ajax({
    url: "../../api/termApi.php",
    method: "POST",
    data: {
      methodName: "getSports",
    },
    dataType: "json",
    success: function (response) {
      console.log("Sports response:", response);

      if (response.status === "success") {
        let options = '<option value="">Izaberi sport</option>';

        response.data.forEach(function (sport) {
          options += `<option value="${sport.id}">${sport.name}</option>`;
        });

        $("#sportId").html(options);
      } else {
        console.error("Greška pri učitavanju sportova:", response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading sports:", error);
    },
  });
}

// Funkcija za učitavanje podataka termina (za izmenu)
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
      console.log("Term data response:", response);

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
        alert("Greška pri učitavanju termina: " + response.message);
        window.location.href = "dashboard-center.php";
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading term data:", error);
      alert("Došlo je do greške pri učitavanju podataka");
      window.location.href = "dashboard-center.php";
    },
  });
}
