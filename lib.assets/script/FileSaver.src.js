var Base64={toBase64Table:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",base64Pad:"=",encode:function(e){"use strict";if(window.btoa)return window.btoa(e);var a,r="",t=Base64.toBase64Table.split(""),o=Base64.base64Pad,s=e.length,n=s-2,i=s%3;for(a=0;a<n;a+=3)r+=t[e[a]>>2],r+=t[((3&e[a])<<4)+(e[a+1]>>4)],r+=t[((15&e[a+1])<<2)+(e[a+2]>>6)],r+=t[63&e[a+2]];return i&&(r+=t[e[a=s-i]>>2],2===i?(r+=t[((3&e[a])<<4)+(e[a+1]>>4)],r+=t[(15&e[a+1])<<2],r+=o):(r+=t[(3&e[a])<<4],r+=o+o)),r},toBinaryTable:[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,62,-1,-1,-1,63,52,53,54,55,56,57,58,59,60,61,-1,-1,-1,0,-1,-1,-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,-1,-1,-1,-1,-1,-1,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,-1,-1,-1,-1,-1],decode:function(e,a){"use strict";a=void 0!==a?a:0;var r,t,o,s,n,i,d=Base64.toBinaryTable,l=Base64.base64Pad,c=0,b=0,B=e.indexOf("=")-a,h=e.length;for(B<0&&(B=e.length-a),t=3*(B>>2)+Math.floor(B%4/1.5),r=new Array(t),o=0,s=a;s<h;s++)n=d[127&e.charCodeAt(s)],i=e.charAt(s)===l,-1!==n?(b=b<<6|n,(c+=6)>=8&&(c-=8,i||(r[o++]=b>>c&255),b&=(1<<c)-1)):console.error("Illegal character '"+e.charCodeAt(s)+"'");if(c)throw{name:"Base64-Error",message:"Corrupted base64 string"};return r}};  
function saveAs(blob, filename)
{
	var reader = new FileReader();
	reader.onloadend = function () {
		$('#saverform input[name="data"]').val(Base64.encode(reader.result));
		$('#saverform input[name="filename"]').val(filename);
		$('#saverform').submit();
		$('#saverform').remove();
	}
	$('body').append('<form id="saverform" target="_blank" method="post" action="lib.ajax/ajax-file-saver.php"><input type="hidden" name="data"><input type="hidden" name="filename"></form>');
	reader.readAsBinaryString(blob);
}