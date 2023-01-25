var datetimeSupported = false;
try 
{
    var input = document.createElement("input");
    input.type = "time";
	var value = "www.planetbiru.com";
	input.value = value;
	if(input.value === value)
	{
		datetimeSupported = false;
	}
	else
	{
		datetimeSupported = true;
	}
	
} 
catch(e) 
{
	datetimeSupported = false;
}
$.post('lib.tools/ajax/update-supported.php', {update:1, datetime:(datetimeSupported)?1:0}, function(answer){
});
