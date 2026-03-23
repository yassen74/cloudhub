<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Location: myprofile.php#profile-editor');
exit;
