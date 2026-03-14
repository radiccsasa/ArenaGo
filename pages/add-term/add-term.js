$(document).ready(function () {
  loadSports();
  $("#addBtn").on("click", () => {
    const date = $("#date").val();
    const time = $("#time").val();
    const price = $("#price").val();
    const discount = $("#discount").val();
    const capacity = $("#capacity").val();
    const sport = $("#sportId").val();

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
        console.log(response.status);
        // location.href = "/ArenaGo/pages/center/dashboard-center.php";
      },
    });
  });
});

function loadSports() {
  $.ajax({
    url: "../../api/termApi.php",
    method: "POST",
    data: {
      methodName: "getSports",
    },
    success: function (response) {
      let options = '<option value="">Izaberi sport</option>';

      response.data.forEach(function (sport) {
        options += `<option value="${sport.id}">${sport.name}</option>`;
      });

      $("#sportId").html(options);
    },
  });
}
