<?php ?>

<!doctype html>
<html lang="sr">

<head>
  <meta charset="UTF-8">
  <title>Center Dashboard</title>

  <link rel="stylesheet" href="../../__bootstrap_packages/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link rel="stylesheet" href="dashboard-centers.css">

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">
  <?php require_once '../../utils/header-user.php';
  require_once '../../utils/toast/toast.php' ?>

  <div class="container mt-5">

    <h2>Panel sportskog centra</h2>

    <div class="card p-4 mt-4">

      <h4 id="headerForm"></h4>
      <label for="">Naziv centra</label>
      <input id="name" class="form-control mb-2">

      <label for="">Opis centra</label>
      <textarea id="description" class="form-control mb-2"></textarea>

      <label for="">Lokacija</label>
      <input id="location" class="form-control mb-2">

      <label for="">Latitude</label>
      <input id="latitude" class="form-control mb-2">

      <label for="">Longitude</label>
      <input id="longitude" class="form-control mb-2">

      <button id="saveCenter" type="button" class="btn btn-success">
        Sačuvaj centar
      </button>

    </div>

    <div class="container mt-4">
      <h4>Termini</h4>

      <!-- Grid termina -->
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="termsGrid">
        <!-- Ovde će se učitati termini preko AJAX-a -->
        <div class="col">
          <div class="card">
            <div class="card-body text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Učitavanje...</span>
              </div>
              <p class="mt-2">Učitavanje termina...</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card p-4 mt-4">

      <h4>Statistika</h4>

      <div id="stats"></div>
      <div class="card p-4 mt-4">
        <h4>Zahtevi za rezervacije</h4>

        <!-- Filter za pretragu (opciono) -->
        <div class="row mb-3">
          <div class="col-md-4">
            <input type="text" id="searchReservation" class="form-control" placeholder="Pretraži po korisniku...">
          </div>
          <div class="col-md-3">
            <select id="filterStatus" class="form-select">
              <option value="">Svi statusi</option>
              <option value="pending">Na čekanju</option>
              <option value="approved">Odobreno</option>
              <option value="rejected">Odbijeno</option>
            </select>
          </div>
        </div>

        <!-- Tabela rezervacija -->
        <div class="table-responsive">
          <table class="table table-hover" id="reservationsTable">
            <thead class="table-dark">
              <tr>
                <th>Korisnik</th>
                <th>Sport</th>
                <th>Datum</th>
                <th>Vreme</th>
                <th>Cena</th>
                <th>Popust</th>
                <th>Ukupno</th>
                <th>Status</th>
                <th>Akcije</th>
              </tr>
            </thead>
            <tbody id="reservationsBody">
              <tr>
                <td colspan="9" class="text-center">Učitavanje rezervacija...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <h3 class="mb-3 mt-4">
      <i class="bi bi-chat text-primary"></i> Komentari
    </h3>
    <div class="row mb-4" id="commentsContainer">
      <div class="col-md-12 text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Učitavanje komentara...</span>
        </div>
      </div>
    </div>
  </div>




  <script src="dashboard-center.js"></script>

</body>

</html>