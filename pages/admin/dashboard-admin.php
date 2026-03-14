<!doctype html>
<html lang="sr">
  <head>
    <meta charset="UTF-8" />
    <title>Admin Panel</title>
    <link
      rel="stylesheet"
      href="../../__bootstrap_packages/css/bootstrap.min.css"
    />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../__bootstrap_packages/js/bootstrap.bundle.min.js"></script>
  </head>
  <body>
    <div class="container mt-5">
      <h2>Admin Panel</h2>

      <table class="table mt-4">
        <thead>
          <tr>
            <th>Ime</th>
            <th>Email</th>
            <th>Uloga</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Marko Marković</td>
            <td>marko@email.com</td>
            <td>User</td>
            <td>Active</td>
            <td><button class="btn btn-danger btn-sm">Blokiraj</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </body>
</html>
