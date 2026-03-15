<!-- <!doctype html>
<html lang="sr">

<head>
  <meta charset="UTF-8" />
  <title>Dodaj Termin</title>
  <link
    rel="stylesheet"
    href="../../__bootstrap_packages/css/bootstrap.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</head>

<body>
  <div class="container mt-5">
    <h2>Dodaj Novi Termin</h2>

    <form class="mt-4">
      <div class="d-flex gap-3">
        <div class="w-100"><label>Datum</label><input type="date" id='date' class="form-control mb-3" required /></div>
        <div class="w-100"><label>Vreme</label><input type="time" id='time' class="form-control mb-3" required /></div>
      </div>
      <label>Cena</label>
      <input
        type="number"
        class="form-control mb-3"
        id='price'
        required />
      <label>Popust(%)</label>
      <input
        type="number"
        min="0"
        max="100"
        class="form-control mb-3"
        id='discount'
        required />
      <label>Kapacitet</label>
      <input
        type="number"
        class="form-control mb-3"
        id='capacity'
        required />
      <select id="sportId" class="form-select mb-3">
      </select>
      <button id="addBtn" type="button" class="btn btn-success">Dodaj</button>
    </form>
  </div>
  <script src="./add-term.js"></script>
</body>

</html> -->

<!doctype html>
<html lang="sr">

<head>
  <meta charset="UTF-8" />
  <title><?php echo isset($_GET['id']) ? 'Izmeni Termin' : 'Dodaj Termin'; ?></title>
  <link rel="stylesheet" href="../../__bootstrap_packages/css/bootstrap.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
</head>

<body>
  <div class="container mt-5">
    <h2 id="formTitle"><?php echo isset($_GET['id']) ? 'Izmeni Termin' : 'Dodaj Novi Termin'; ?></h2>

    <form class="mt-4">
      <?php if (isset($_GET['id'])): ?>
        <input type="hidden" id="termId" value="<?php echo (int)$_GET['id']; ?>" />
      <?php endif; ?>

      <input type="hidden" id="mode" value="<?php echo isset($_GET['id']) ? 'edit' : 'add'; ?>" />

      <div class="d-flex gap-3">
        <div class="w-100"><label>Datum</label><input type="date" id='date' class="form-control mb-3" required /></div>
        <div class="w-100"><label>Vreme</label><input type="time" id='time' class="form-control mb-3" required /></div>
      </div>

      <label>Cena</label>
      <input type="number" class="form-control mb-3" id='price' required />

      <label>Popust(%)</label>
      <input type="number" min="0" max="100" class="form-control mb-3" id='discount' value="0" required />

      <label>Kapacitet</label>
      <input type="number" class="form-control mb-3" id='capacity' required />

      <select id="sportId" class="form-select mb-3"></select>

      <button id="saveBtn" type="button" class="btn btn-success">
        <?php echo isset($_GET['id']) ? 'Sačuvaj izmene' : 'Dodaj termin'; ?>
      </button>

      <a href="dashboard-center.php" class="btn btn-secondary">Odustani</a>
    </form>
  </div>

  <script src="./add-term.js"></script>
</body>

</html>