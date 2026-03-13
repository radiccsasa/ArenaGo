$("#saveCenter").click(function(){

let name = $("#name").val();
let description = $("#description").val();
let location = $("#location").val();
let latitude = $("#latitude").val();
let longitude = $("#longitude").val();

$.ajax({

url:"../../api/centerApi.php",
method:"POST",

dataType:"json",

data:{
name:name,
description:description,
location:location,
latitude:latitude,
longitude:longitude,
methodName:"createCenter"
},

success:function(response){

console.log(response);

if(response.status == "success"){
alert("Centar uspešno kreiran");
}

}

});

});


// UCITAVANJE STATISTIKE

$(document).ready(function(){

$.ajax({

url:"../../api/centerApi.php",
method:"POST",

dataType:"json",

data:{
methodName:"getStats"
},

success:function(response){

$("#stats").html("Ukupno rezervacija: "+response.reservations);

}

});

});