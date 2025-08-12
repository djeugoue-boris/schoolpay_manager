<?php
if (isset($_SESSION['message'])) {
    echo "<div class='alert alert-{$_SESSION['message_type']} alert-dismissible fade show' role='alert'>
            {$_SESSION['message']}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fermer'></button>
          </div>";
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>
