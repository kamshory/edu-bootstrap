function getLocalTime()
{
	var localTime = new Date();
	var yyyy, mm, dd, hh, ii, ss;
	yyyy = localTime.getFullYear();
	mm = localTime.getMonth()+1;
	dd = localTime.getDate();
	hh = localTime.getHours();
	ii = localTime.getMinutes();
	ss = localTime.getSeconds();
	if(mm < 10)
	mm = '0'+mm;
	if(dd < 10)
	dd = '0'+dd;
	if(hh < 10)
	hh = '0'+hh;
	if(ii < 10)
	ii = '0'+ii;
	if(ss < 10)
	ss = '0'+ss;
	var localTimeStr = yyyy+'-'+mm+'-'+dd+' '+hh+':'+ii+':'+ss;
	return localTimeStr;
}
window.onload = function()
{
	var localTime = getLocalTime();
	var xhr = null;
	if(window.XMLHttpRequest)
	{
		xhr = new XMLHttpRequest();
	}
	else
	{
		if (window.ActiveXObject)
		{
			xhr = new ActiveXObject('MSXML2.XMLHTTP.3.0');
		}
	}
	
	xhr.open('POST', 'lib.tools/ajax/set-server-time.php');
	xhr.setRequestHeader('X-Command-For-Server', 'Set-Server-Time');
	xhr.setRequestHeader('X-Set-Time-Token', setTimeToken); 
	xhr.setRequestHeader('X-Local-Time', localTime); 
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.onload = function()
	{
		if (xhr.status === 200 && xhr.responseText) 
		{
			console.log(xhr.responseText);
			window.location.reload();
		}
		else if (xhr.status !== 200) {
			console.log(xhr.status);
		}
		else
		{
		}
	};
	xhr.send('localtime='+encodeURIComponent(localTime));
}
