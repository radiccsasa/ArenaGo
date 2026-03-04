document.addEventListener("DOMContentLoaded", function() {

    console.log("SportBook aplikacija pokrenuta");

    const loginForm = document.getElementById("loginForm");

    if(loginForm){
        loginForm.addEventListener("submit", function(e){
            e.preventDefault();
            alert("Login će biti povezan sa PHP backendom.");
        });
    }

});