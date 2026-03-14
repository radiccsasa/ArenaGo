$(document).ready(function () {
  // Prvo proveri da li centar postoji
  $.ajax({
    url: "../../api/centerApi.php",
    method: "POST",
    data: {
      methodName: "checkCenterExists",
    },
    dataType: "json",
    success: function (response) {
      console.log("Check center response:", response); // Dodaj za debug
      if (response.exists) {
        loadCenterData();
        $("#saveCenter")
          .text("Ažuriraj centar")
          .removeClass("btn-success")
          .addClass("btn-primary")
          .off("click")
          .on("click", updateCenter);
        $("#headerForm").text("Azuriraj Podatke");
      } else {
        $("#saveCenter")
          .text("Kreiraj centar")
          .removeClass("btn-primary")
          .addClass("btn-success")
          .off("click")
          .on("click", createCenter);
        $("#headerForm").text("Kreiraj svoj centar");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error checking center:", error);
      console.log("Response:", xhr.responseText);
    },
  });

  loadStatistics();
  loadReservations();

  // Filter događaji
  $("#searchReservation").on("keyup", function () {
    let search = $(this).val();
    let status = $("#filterStatus").val();
    loadReservations(status, search);
  });

  $("#filterStatus").on("change", function () {
    let status = $(this).val();
    let search = $("#searchReservation").val();
    loadReservations(status, search);
  });

  // Delegacija za dugmad (MORAJU biti unutar document.ready)
  $(document).on("click", ".approve-btn", function () {
    let reservationId = $(this).data("id");
    updateReservationStatus(reservationId, "approved");
  });

  $(document).on("click", ".reject-btn", function () {
    let reservationId = $(this).data("id");
    updateReservationStatus(reservationId, "rejected");
  });
});

// Sve funkcije ostaju iste, ali ih stavi VAN document.ready
function createCenter() {
  let name = $("#name").val();
  let description = $("#description").val();
  let location = $("#location").val();
  let latitude = $("#latitude").val();
  let longitude = $("#longitude").val();

  if (!validateForm()) return;

  $.ajax({
    url: "../../api/centerApi.php",
    method: "POST",
    dataType: "json",
    data: {
      name: name,
      description: description,
      location: location,
      latitude: latitude,
      longitude: longitude,
      methodName: "createCenter",
    },
    success: function (response) {
      console.log("Create response:", response); // Dodaj za debug
      if (response.status == "success") {
        alert("Centar uspešno kreiran!");
        $("#saveCenter")
          .text("Ažuriraj centar")
          .removeClass("btn-success")
          .addClass("btn-primary")
          .off("click")
          .on("click", updateCenter);
      } else {
        alert("Greška: " + (response.message || "Nepoznata greška"));
      }
    },
    error: function (xhr, status, error) {
      console.error("Error creating center:", error);
      console.log("Response:", xhr.responseText);
      alert("Došlo je do greške na serveru: " + error);
    },
  });
}

function updateCenter() {
  let name = $("#name").val();
  let description = $("#description").val();
  let location = $("#location").val();
  let latitude = $("#latitude").val();
  let longitude = $("#longitude").val();

  if (!validateForm()) return;

  $.ajax({
    url: "../../api/centerApi.php",
    method: "POST",
    dataType: "json",
    data: {
      name: name,
      description: description,
      location: location,
      latitude: latitude,
      longitude: longitude,
      methodName: "updateCenter",
    },
    success: function (response) {
      console.log("Update response:", response); // Dodaj za debug
      if (response.status == "success") {
        alert("Centar uspešno ažuriran!");
      } else {
        alert("Greška: " + (response.message || "Nepoznata greška"));
      }
    },
    error: function (xhr, status, error) {
      console.error("Error updating center:", error);
      console.log("Response:", xhr.responseText);
      alert("Došlo je do greške na serveru: " + error);
    },
  });
}

function loadCenterData() {
  $.ajax({
    url: "../../api/centerApi.php",
    method: "POST",
    data: {
      methodName: "getCenterData",
    },
    dataType: "json",
    success: function (response) {
      console.log("Load center data response:", response); // Dodaj za debug
      if (response.status == "success") {
        const center = response.data;
        $("#name").val(center.name || "");
        $("#description").val(center.description || "");
        $("#location").val(center.location || "");
        $("#latitude").val(center.latitude || "");
        $("#longitude").val(center.longitude || "");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading center data:", error);
    },
  });
}

function loadStatistics() {
  $.ajax({
    url: "../../api/centerApi.php",
    method: "POST",
    dataType: "json",
    data: {
      methodName: "getStats",
    },
    success: function (response) {
      console.log("Stats response:", response); // Dodaj za debug
      $("#stats").html(`
        <p>Ukupno rezervacija: ${response.reservations || 0}</p>
        <p>Ukupno termina: ${response.terms || 0}</p>
        <p>Prosečna ocena: ${response.rating || "Nema ocena"}</p>
      `);
    },
    error: function (xhr, status, error) {
      console.error("Error loading stats:", error);
    },
  });
}

function validateForm() {
  if (!$("#name").val()) {
    alert("Naziv centra je obavezan");
    return false;
  }
  if (!$("#description").val()) {
    alert("Opis centra je obavezan");
    return false;
  }
  if (!$("#location").val()) {
    alert("Lokacija je obavezna");
    return false;
  }
  return true;
}

function loadReservations(status = "", search = "") {
  $.ajax({
    url: "../../api/centerApi.php",
    method: "POST",
    dataType: "json",
    data: {
      methodName: "getReservations",
      filter_status: status,
      search: search,
    },
    success: function (response) {
      console.log("Reservations response:", response); // Dodaj za debug
      if (response.status === "success") {
        displayReservations(response.data);
      } else {
        $("#reservationsBody").html(
          '<tr><td colspan="9" class="text-center text-danger">Greška pri učitavanju rezervacija: ' +
            (response.message || "Nepoznata greška") +
            "</td></tr>",
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading reservations:", error);
      console.log("Response:", xhr.responseText);
      $("#reservationsBody").html(
        '<tr><td colspan="9" class="text-center text-danger">Greška pri učitavanju rezervacija</td></tr>',
      );
    },
  });
}

function displayReservations(reservations) {
  let html = "";

  if (!reservations || reservations.length === 0) {
    html =
      '<tr><td colspan="9" class="text-center">Nema rezervacija za prikaz</td></tr>';
  } else {
    reservations.forEach(function (res) {
      let discount = parseFloat(res.discount) || 0;
      let price = parseFloat(res.price) || 0;
      let totalPrice = price - (price * discount) / 100;

      let statusBadge = "";
      switch (res.status) {
        case "pending":
          statusBadge =
            '<span class="badge bg-warning text-dark">Na čekanju</span>';
          break;
        case "approved":
          statusBadge = '<span class="badge bg-success">Odobreno</span>';
          break;
        case "rejected":
          statusBadge = '<span class="badge bg-danger">Odbijeno</span>';
          break;
        default:
          statusBadge =
            '<span class="badge bg-secondary">' +
            (res.status || "Nepoznato") +
            "</span>";
      }

      html += `<tr>
        <td><strong>${escapeHtml(res.user_name || "N/A")}</strong><br><small class="text-muted">${escapeHtml(res.user_email || "")}</small></td>
        <td><span class="badge bg-info">${escapeHtml(res.sport_name || "N/A")}</span></td>
        <td>${formatDate(res.date)}</td>
        <td>${res.time || "N/A"}</td>
        <td>${formatPrice(price)}</td>
        <td>${discount > 0 ? discount + "%" : "-"}</td>
        <td><strong>${formatPrice(totalPrice)}</strong></td>
        <td>${statusBadge}</td>
        <td>
          <div class="btn-group btn-group-sm" role="group">`;

      if (res.status === "pending") {
        html += `
          <button class="btn btn-success approve-btn" data-id="${res.id}" title="Prihvati">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </button>
          <button class="btn btn-danger reject-btn" data-id="${res.id}" title="Odbij">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>`;
      } else {
        html += `<span class="text-muted">Nema akcija</span>`;
      }

      html += `
          </div>
        </td>
      </tr>`;
    });
  }

  $("#reservationsBody").html(html);
}

function escapeHtml(text) {
  if (!text) return "";
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatDate(dateString) {
  if (!dateString) return "";
  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString("sr-RS");
  } catch (e) {
    return dateString;
  }
}

function formatPrice(price) {
  if (!price && price !== 0) return "0 RSD";
  try {
    return (
      new Intl.NumberFormat("sr-RS", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(price) + " RSD"
    );
  } catch (e) {
    return price + " RSD";
  }
}

function updateReservationStatus(reservationId, status) {
  if (
    !confirm(
      `Da li ste sigurni da želite da ${status === "approved" ? "prihvatite" : "odbijete"} ovu rezervaciju?`,
    )
  ) {
    return;
  }

  $.ajax({
    url: "../../api/centerApi.php",
    method: "POST",
    dataType: "json",
    data: {
      methodName: "updateReservationStatus",
      reservation_id: reservationId,
      status: status,
    },
    success: function (response) {
      console.log("Update status response:", response); // Dodaj za debug
      if (response.status === "success") {
        alert(
          `Rezervacija je uspešno ${status === "approved" ? "prihvaćena" : "odbijena"}!`,
        );
        loadReservations(
          $("#filterStatus").val(),
          $("#searchReservation").val(),
        );
      } else {
        alert("Greška: " + (response.message || "Nepoznata greška"));
      }
    },
    error: function (xhr, status, error) {
      console.error("Error updating reservation:", error);
      console.log("Response:", xhr.responseText);
      alert("Došlo je do greške na serveru: " + error);
    },
  });
}
