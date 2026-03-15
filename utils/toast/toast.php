<?php
// includes/toast.php
?>

<!-- Toast kontejneri -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <!-- Success Toast -->
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body" id="successToastMessage">
                Uspešno!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    <!-- Info Toast -->
    <div id="infoToast" class="toast align-items-center text-white bg-info border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body" id="infoToastMessage">
                Informacija
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    <!-- Warning Toast -->
    <div id="warningToast" class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body" id="warningToastMessage">
                Upozorenje
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    <!-- Error Toast -->
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body" id="errorToastMessage">
                Greška
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    // Funkcije za toastove (možeš i u poseban JS fajl, ali ovde su zbog jednostavnosti)
    function showSuccessToast(message) {
        $('#successToastMessage').text(message);
        let toast = new bootstrap.Toast(document.getElementById('successToast'));
        toast.show();
    }

    function showInfoToast(message) {
        $('#infoToastMessage').text(message);
        let toast = new bootstrap.Toast(document.getElementById('infoToast'));
        toast.show();
    }

    function showWarningToast(message) {
        $('#warningToastMessage').text(message);
        let toast = new bootstrap.Toast(document.getElementById('warningToast'));
        toast.show();
    }

    function showErrorToast(message) {
        $('#errorToastMessage').text(message);
        let toast = new bootstrap.Toast(document.getElementById('errorToast'));
        toast.show();
    }

    // Univerzalna funkcija (opciono)
    function showToast(message, type = 'info') {
        switch (type) {
            case 'success':
                showSuccessToast(message);
                break;
            case 'warning':
                showWarningToast(message);
                break;
            case 'error':
                showErrorToast(message);
                break;
            default:
                showInfoToast(message);
                break;
        }
    }
</script>