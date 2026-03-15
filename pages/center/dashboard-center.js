$(document).ready(function () {
  $.ajax({
    url: "../../api/centerApi.php",
    method: "POST",
    data: {
      methodName: "checkCenterExists",
    },
    dataType: "json",
    success: function (response) {
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
      showToast("Doslo je do greske", "error");
    },
  });

  loadStatistics();
  loadReservations();
  loadTerms();

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

  $(document).on("click", ".approve-btn", function () {
    let reservationId = $(this).data("id");
    updateReservationStatus(reservationId, "approved");
  });

  $(document).on("click", ".reject-btn", function () {
    let reservationId = $(this).data("id");
    updateReservationStatus(reservationId, "rejected");
  });
});

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
      if (response.status == "success") {
        showToast("Centar uspešno kreiran!", "success");
        $("#saveCenter")
          .text("Ažuriraj centar")
          .removeClass("btn-success")
          .addClass("btn-primary")
          .off("click")
          .on("click", updateCenter);
        $("#headerForm").text("Azuriraj Podatke");
      } else {
        showToast(
          "Greška: " + (response.message || "Nepoznata greška"),
          "error",
        );
      }
    },
    error: function (xhr, status, error) {
      showToast("Došlo je do greške na serveru: " + error, "error");
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
      if (response.status == "success") {
        showToast("Centar uspešno ažuriran!", "success");
      } else {
        showToast(
          "Greška: " + (response.message || "Nepoznata greška"),
          "error",
        );
      }
    },
    error: function (xhr, status, error) {
      showToast("Došlo je do greške na serveru: " + error, "error");
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
      if (response.status == "success") {
        const center = response.data;
        $("#name").val(center.name || "");
        $("#description").val(center.description || "");
        $("#location").val(center.location || "");
        $("#latitude").val(center.latitude || "");
        $("#longitude").val(center.longitude || "");
      }
    },
    error: function (xhr, status, error) {},
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
      $("#stats").html(`
        <p>Ukupno rezervacija: ${response.reservations || 0}</p>
        <p>Ukupno termina: ${response.terms || 0}</p>
        <p>Prosečna ocena: ${response.rating || "Nema ocena"}</p>
      `);
    },
    error: function (xhr, status, error) {},
  });
}

function validateForm() {
  if (!$("#name").val()) {
    showToast("Naziv centra je obavezan");
    return false;
  }
  if (!$("#description").val()) {
    showToast("Opis centra je obavezan");
    return false;
  }
  if (!$("#location").val()) {
    showToast("Lokacija je obavezna");
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
      $("#reservationsBody").html(
        '<tr><td colspan="9" class="text-center text-danger">Greška pri učitavanju rezervacija</td></tr>',
      );
    },
  });
}

function loadTerms() {
  console.log("loadTerms se poziva");

  $.ajax({
    url: "../../api/termApi.php",
    method: "POST",
    data: {
      methodName: "getAllCenterTerms",
    },
    dataType: "json",
    success: function (response) {
      console.log("USPEH - termini:", response);
      if (response.status === "success") {
        displayTermsGrid(response.data);
      } else {
        $("#termsGrid").html(`
          <div class="col-12">
            <div class="alert alert-danger text-center">
              Greška: ${response.message || "Nepoznata greška"}
            </div>
          </div>
        `);
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX greška:", error);
      $("#termsGrid").html(`
        <div class="col-12">
          <div class="alert alert-danger text-center">
            Greška pri učitavanju termina. Proveri konzolu.
          </div>
        </div>
      `);
    },
  });
}

function displayTermsGrid(terms) {
  if (!terms || terms.length === 0) {
    $("#termsGrid").html(`
      <div class="col-12">
        <div class="alert alert-info text-center">
          Još uvek nemate dodate termine. 
          <a href="/ArenaGo/pages/add-term/add-term.php" class="alert-link">Dodaj prvi termin</a>
        </div>
      </div>
      <!-- Dodaj dugme i kada nema termina -->
      <div class="col">
        <a href="/ArenaGo/pages/add-term/add-term.php" class="text-decoration-none">
          <div class="card h-100 shadow-sm border-2 border-success border-opacity-25 bg-light">
            <div class="card-body d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
              <div class="display-1 text-success mb-3">+</div>
              <h5 class="card-title text-success">Dodaj novi termin</h5>
              <p class="text-muted">Klikni da dodaš novi termin</p>
            </div>
          </div>
        </a>
      </div>
    `);
    return;
  }

  let html = "";

  // Prvo dodaj sve postojeće termine
  terms.forEach(function (term) {
    // Formatiranje datuma
    let date = new Date(term.date);
    let formattedDate = date.toLocaleDateString("sr-RS");

    // Boja za status termina
    let statusClass = "";
    let statusText = "";

    let today = new Date();
    today.setHours(0, 0, 0, 0);
    let termDate = new Date(term.date);

    if (termDate < today) {
      statusClass = "bg-warning";
      statusText = "Prošao";
    } else if (termDate.toDateString() === today.toDateString()) {
      statusClass = "bg-success";
      statusText = "Danas";
    } else {
      statusClass = "bg-primary";
      statusText = "Predstoji";
    }

    // Izračunaj cenu sa popustom
    let price = parseFloat(term.price) || 0;
    let discount = parseFloat(term.action_discount) || 0;
    let finalPrice = price - (price * discount) / 100;

    html += `
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-header ${statusClass} text-white">
            <span>${statusText}</span>
          </div>
          <div class="card-body">
            <h5 class="card-title">
              <span class="badge bg-info mb-2">${escapeHtml(term.sport_name || "Sport")}</span>
            </h5>
            
            <div class="mb-2">
              <i class="bi bi-calendar"></i> Datum: <strong>${formattedDate}</strong>
            </div>
            
            <div class="mb-2">
              <i class="bi bi-clock"></i> Vreme: <strong>${term.time}</strong>
            </div>
            
            <div class="mb-2">
              <i class="bi bi-people"></i> Kapacitet: <strong>${term.capacity}</strong>
            </div>
            
            <div class="mb-2">
              <i class="bi bi-tag"></i> Cena: 
              ${
                discount > 0
                  ? `<span class="text-decoration-line-through text-muted me-2">${formatPrice(price)}</span>
                 <span class="text-success fw-bold">${formatPrice(finalPrice)}</span>`
                  : `<span class="fw-bold">${formatPrice(price)}</span>`
              }
            </div>
            
            ${
              discount > 0
                ? `<div class="mb-2">
                <span class="badge bg-danger">Popust ${discount}%</span>
              </div>`
                : ""
            }
          </div>
          
          <div class="card-footer bg-transparent">
            <div class="d-flex justify-content-between">
            <a href="/ArenaGo/api/removeTerm.php?termId=${term.id}" class="btn btn-sm btn-outline-danger delete-term" data-id="${term.id}">
                <i class="bi bi-trash"></i> Obriši
              </a>
          ${
            termDate < today
              ? ""
              : `<a href="/ArenaGo/pages/add-term/add-term.php?id=${term.id}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i> Izmeni
              </a>`
          }
            </div>
          </div>
        </div>
      </div>
    `;
  });

  // Dodaj dugme za novi termin na KRAJU
  html += `
    <div class="col">
      <a href="/ArenaGo/pages/add-term/add-term.php" class="text-decoration-none">
        <div class="card h-100 shadow-sm border-2 border-success border-opacity-25 bg-light hover-scale">
          <div class="card-body d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
            <div class="display-1 text-success mb-3">+</div>
            <h5 class="card-title text-success">Dodaj novi termin</h5>
            <p class="text-muted">Klikni da dodaš novi termin</p>
          </div>
        </div>
      </a>
    </div>
  `;

  $("#termsGrid").html(html);
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
      if (response.status === "success") {
        showToast(
          `Rezervacija je uspešno ${status === "approved" ? "prihvaćena" : "odbijena"}!`,
          "success",
        );
        loadReservations(
          $("#filterStatus").val(),
          $("#searchReservation").val(),
        );
      } else {
        showToast(
          "Greška: " + (response.message || "Nepoznata greška"),
          "error",
        );
      }
    },
    error: function (xhr, status, error) {
      showToast("Došlo je do greške na serveru: " + error, "error");
    },
  });
}
