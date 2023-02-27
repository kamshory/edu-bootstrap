function downloadArticle()
{
    let title = $('.article-title').text();
	let content = $('.article-content').html();
	let creator = $('.article-creator').text();
	let html = '<div><h1>'+title+'</h1>\r\n<div>'+creator+'</div>'+ content+'</div>';
	let doc = $(html);
	doc = convertImagesToBase64(doc);
	content = doc.html(); 
	let style = '<style type="text/css">body{font-family:"Times New Roman", Times, serif; font-size:16px; position:relative;} table[border="1"]{border-collapse:collapse; box-sizing:border-box; max-width:100%;} table[border="1"] td{padding:4px 5px;} table[border="0"] td{padding:4px 0;} p, li{line-height:1.5;} a{color:#000000; text-decoration:none;} h1{font-size:30px;} h2{font-size:26px;} h3{font-size:22px;} h4{font-size:16px;}</style>';
	content = '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>'+title+'</title>'+style+'</head><body style="position:relative;">'+content+'</body></html>';
	//let converted = new Blob([content], {type:'text/html'});
    let converted = htmlDocx.asBlob(content);

	saveAs(converted, title+'.docx');
}
function convertImagesToBase64 (doc) {
  let regularImages = doc.find ('img');
  let canvas = document.createElement ('canvas');
  let ctx = canvas.getContext ('2d');
  [].forEach.call (regularImages, function (obj) {
    let imgElement = obj;
    ctx.clearRect (0, 0, canvas.width, canvas.height);
    canvas.width = imgElement.width;
    canvas.height = imgElement.height;
    ctx.drawImage (imgElement, 0, 0, imgElement.width, imgElement.height);
    let dataURL = canvas.toDataURL ();
    imgElement.setAttribute ('src', dataURL);
    imgElement.style.width = canvas.width + 'px';
    imgElement.style.maxWidth = '100%';
    imgElement.style.height = 'auto';
    imgElement.removeAttribute ('height');
  });
  canvas.remove ();
  return doc;
}
