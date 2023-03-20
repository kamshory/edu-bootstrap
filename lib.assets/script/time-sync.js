window.onload = function () {
	let unixTimestamp = (new Date()).getTime();
	let xhr = null;
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	}
	else {
		if (window.ActiveXObject) {
			xhr = new ActiveXObject('MSXML2.XMLHTTP.3.0');
		}
	}

	xhr.open('POST', 'lib.tools/ajax/set-server-time.php');
	xhr.setRequestHeader('X-Command-For-Server', 'Set-Server-Time');
	xhr.setRequestHeader('X-Unix-Timestamp', unixTimestamp);
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.onload = function () {
		if (xhr.status === 200) {
			console.log('success');
		}
		else {
			console.log('error');
		}
	};
	xhr.send('unixtimestamp=' + encodeURIComponent(unixTimestamp));
};
