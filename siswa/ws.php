<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
$pageTitle = "Ujian";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(!empty(@$auth_student_id) && !empty(@$auth_school_id))
{
$test_id = kh_filter_input(INPUT_GET, "test_id", FILTER_SANITIZE_STRING_NEW);

?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Chat</title>
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="chat.css?rand=<?php echo mt_rand(1,99999999);?>">
<link rel="stylesheet" href="icon.css">
<script src="ws.js"></script>

<script type="text/javascript">
var websocketURL = '';
if(window.location.protocol.toString() == 'https:')
{
	websocketURL = 'wss://<?php echo $_SERVER['SERVER_NAME'];?>/wss.socket/some-path/?groupId=group1';
}
else
{
	websocketURL = 'ws://<?php echo $_SERVER['SERVER_NAME'];?>:8888/ujian/?group_id=student&module=test&test_id=<?php echo $test_id;?>';
}
console.log(window.location.protocol.toString());
console.log(websocketURL);
let option = {
    reconnectInterval: 3000,
    maxReconnectInterval: 30000
};
var ws = new ReconnectingWebSocket(websocketURL);
ws.debug = false;

ReconnectingWebSocket.prototype.onopen = function(event) {
    console.log('onopen');
    console.log(event.data);
};
/** An event listener to be called when the WebSocket connection's readyState changes to CLOSED. */
ReconnectingWebSocket.prototype.onclose = function(event) {
    console.log('onclose');
    console.log(event.data);
};
/** An event listener to be called when a connection begins being attempted. */
ReconnectingWebSocket.prototype.onconnecting = function(event) {
    console.log('onconnecting');
    console.log(event.data);
};
/** An event listener to be called when a message is received from the server. */
ReconnectingWebSocket.prototype.onmessage = function(event) {
    console.log('onmessage');
    console.log(event.data);
};
/** An event listener to be called when an error occurs. */
ReconnectingWebSocket.prototype.onerror = function(event) {
    console.log('onerror');
    console.log(event.data);
};

</script>

<style type="text/css">

</style>
</head>

<body>


</body>
</html>
<?php
}
else
{
	require_once "login-form.php";
}
?>