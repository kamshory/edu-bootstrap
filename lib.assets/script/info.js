$(document).ready(function(e) {
	$(document).on('click', '.delete-post', function(e){
		var article_id = $(this).attr('data-id');
		if(confirm('Apakah Anda akan menghapus artikel ini?'))
		{
			$.post('ajax-delete-artikel.php', {article_id:article_id, option:'delete'}, function(asnwer){
				window.location = 'artikel.php';
			});
		}
		e.preventDefault();
	});
	$(document).on('click', '.download-word', function(e){
		var title = $('.article-title').text();
		var content = $('.article-content').html();
		var creator = $('.article-creator').text();
		var html = '<div><h1>'+title+'</h1>\r\n<div>'+creator+'</div>'+ content+'</div>';
		var doc = $(html);
		doc = convertImagesToBase64(doc);
		doc = replaceBase(doc);
		var content = doc.html(); 
		var style = '<style type="text/css">body{font-family:"Times New Roman", Times, serif; font-size:16px; position:relative;} table[border="1"]{border-collapse:collapse; box-sizing:border-box; max-width:100%;} table[border="1"] td{padding:4px 5px;} table[border="0"] td{padding:4px 0;} p, li{line-height:1.5;} a{color:#000000; text-decoration:none;} h1{font-size:30px;} h2{font-size:26px;} h3{font-size:22px;} h4{font-size:16px;}</style>';
		content = '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>'+title+'</title>'+style+'</head><body style="position:relative;">'+content+'</body></html>';
		var converted = new Blob([content], {type:'text/html'});
		saveAs(converted, title+'.html');
		e.preventDefault();
	});
});
function convertImagesToBase64 (doc) {
	var regularImages = doc.find('img');
	var canvas = document.createElement('canvas');
	var ctx = canvas.getContext('2d');
	[].forEach.call(regularImages, function (obj) {
		var imgElement = obj;
		ctx.clearRect(0, 0, canvas.width, canvas.height);
		canvas.width = imgElement.width;
		canvas.height = imgElement.height;
		ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
		var dataURL = canvas.toDataURL();
		imgElement.setAttribute('src', dataURL);
		imgElement.style.width = canvas.width+'px';
		imgElement.style.maxWidth = '100%';
		imgElement.style.height = 'auto';
		imgElement.removeAttribute('height');
	});
	canvas.remove();
	return doc;
}
function replaceBase (doc) {
	var regularLinks = doc.find('a');
	var lnk = "";
	[].forEach.call(regularLinks, function (obj) {
		var aElement = obj;
		lnk = aElement.getAttribute('href');
		lnk = convertRelToAbsUrl(lnk);
		aElement.setAttribute('href', lnk);
	});
	return doc;
}
function convertRelToAbsUrl(url) 
{
    var baseUrl = null;
    if (/^(https?|file|ftps?|mailto|javascript|data:image\/[^;]{2,9};):/i.test(url)) {
        return url; // url is already absolute
    }
    baseUrl = window.location.href.match(/^(.+)\/?(?:#.+)?$/)[0] + '/';
    if (url.substring(0, 2) === '//') {
        return location.protocol + url;
    }
    if (url.charAt(0) === '/') {
        return location.protocol + '//' + location.host + url;
    }
    if (url.substring(0, 2) === './') {
        url = '.' + url;
    } else if (/^\s*$/.test(url)) {
        return ''; // empty = return nothing
    }
    url = baseUrl + '../' + url;
    while (/\/\.\.\//.test(url)) {
        url = url.replace(/[^\/]+\/+\.\.\//g, '');
    }
    url = url.replace(/\.$/, '').replace(/\/\./g, '').replace(/"/g, '%22')
            .replace(/'/g, '%27').replace(/</g, '%3C').replace(/>/g, '%3E');
    return url;
}
