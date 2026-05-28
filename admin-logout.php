<?php
session_start();
session_destroy();
header('Location: https://smartlocker-ta.infinityfree.me');
exit;
?>