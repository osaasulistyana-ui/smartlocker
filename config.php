<?php
$host = 'sql113.byetcluster.com';
$db   = 'if0_41742559_smartlocker';
$user = 'if0_41742559';
$pass = 'BvUOkeszaj7bpB';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['status'=>'error','message'=>$conn->connect_error]));
}
$conn->set_charset('utf8');
?>