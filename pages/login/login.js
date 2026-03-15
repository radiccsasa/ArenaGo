function showNotification(message, type) {
    const icon = {
        'success': 'bi-check-circle-fill',
        'danger': 'bi-exclamation-triangle-fill',
        'warning': 'bi-exclamation-circle-fill',
        'info': 'bi-info-circle-fill'
    };
    
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
    
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

$("#loginBtn").click(function() {
    let email = $("#email").val();
    let password = $("#password").val();

    if(email === '' || password === '') {
        showNotification('Molimo unesite email i lozinku', 'warning');
        return;
    }

    $.ajax({
        url: "../../api/userApi.php",
        method: "POST",
        data: {
            password: password,
            email: email,
            methodName: "login"
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === "success") {
                showNotification('Uspešno ste se prijavili! Preusmeravanje...', 'success');
                setTimeout(function() {
                    if(response.user && response.user.role == "admin")
                    {
                        window.location.href = "../admin/dashboard-admin.php";
                    }
                    if (response.user && response.user.role == "center") {
                        window.location.href = "../center/dashboard-center.php";
                    } if  (response.user && response.user.role == "user") {
                        window.location.href = "../user/dashboard-user.php";
                    }
                }, 1000);
            } else {
                showNotification(response.message || "Greška pri logovanju", 'danger');
            }
        },
        error: function(xhr) {
            console.error("Server response:", xhr.responseText);
            showNotification("Došlo je do greške na serveru. Pokušajte ponovo.", 'danger');
        }
    });
});