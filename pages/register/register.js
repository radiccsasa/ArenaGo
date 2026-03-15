function showNotification(message, type) {
    const icon = {
        'success': 'bi-check-circle-fill',
        'danger': 'bi-exclamation-triangle-fill',
        'warning': 'bi-exclamation-circle-fill',
        'info': 'bi-info-circle-fill'
    };
    
    // Prvo ukloni postojecu notifikaciju
    $('#notificationArea').empty();
    
    const notification = `
        <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi ${icon[type]} me-2 fs-4"></i>
            <div class="flex-grow-1">
                ${message}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    $('#notificationArea').html(notification);
    
    // Automatski nestane posle 5 sekundi
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

$("#regBtn").click(function () {
    let name = $("#name").val();
    let email = $("#email").val();
    let password = $("#password").val();
    let role = $("#role").val();

    // Validacija
    if(name === '' || email === '' || password === '') {
        showNotification('Molimo popunite sva polja', 'warning');
        return;
    }

    if(password.length < 6) {
        showNotification('Lozinka mora imati najmanje 6 karaktera', 'warning');
        return;
    }

    if(!email.includes('@') || !email.includes('.')) {
        showNotification('Unesite ispravnu email adresu', 'warning');
        return;
    }

    $.ajax({
        url: "../../api/userApi.php",
        method: "POST",
        data: {
            name: name,
            password: password,
            email: email,
            role: role,
            methodName: "register",
        },
        dataType: 'json',
        success: function (response) {
            console.log("Server odgovor:", response);
            
            if (response.status == "success") {
                showNotification('Uspešno ste se registrovali! Preusmeravanje...', 'success');
                
                setTimeout(function() {
                    if (response.user && response.user.role == "center") {
                        window.location.href = "../center/dashboard-center.php";
                    } else {
                        window.location.href = "../user/dashboard-user.php";
                    }
                }, 1500);
            } else {
                showNotification(response.message || "Greška pri registraciji", 'danger');
            }
        },
        error: function(xhr) {
            console.error("Server response:", xhr.responseText);
            showNotification("Došlo je do greške na serveru. Pokušajte ponovo.", 'danger');
        }
    });
});