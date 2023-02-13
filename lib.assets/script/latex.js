window.onload = function(e)
{
	let equgen = 'mathjax-svg';
	document.getElementById('renderer').value = equgen;
	document.getElementById('renderer').addEventListener('change', function(e){
		let data = document.getElementById('latex-input').value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('change', function(e){
		let data = e.target.value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('keyup', function(e){
		let data = e.target.value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('blur', function(e){
		let data = e.target.value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('focus', function(e){
		let data = e.target.value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('paste', handlePasteImage);
	let url = document.location.toString();
	if(url.indexOf('arg=') > -1)
	{
		let textSelected = url.substr(url.indexOf('arg=') + 4);
		textSelected = decodeURIComponent(textSelected);
		textSelected = textSelected.trim();
		if(textSelected.indexOf('$$') === 0)
		{
			textSelected = textSelected.substr(2);
		}
		if(textSelected.substr(textSelected.length - 2, 2) == '$$')
		{
			textSelected = textSelected.substr(0, textSelected.length - 2);
		}
		textSelected = decodeLatexFromURI(textSelected);
		textSelected = asciimath.reconstructSqrtWord(textSelected);
		textSelected = asciimath.filterData(textSelected);
		textSelected = asciimath.reconstructMatrix(textSelected);
		textSelected = asciimath.reconstructVector(textSelected);
		document.getElementById('latex-input').value = textSelected;
	}
	document.getElementById('latex-input').focus();
	let data = document.getElementById('latex-input').value;
	renderLatex(data);
}
function renderLatex(latex){
	let rendererSelector = document.getElementById('renderer').value;
	let urlGenerator = window.parent.equationURLGenerator || '../cgi-bin/equgen.cgi';
	let urlPreview = window.parent.equationURLPreview || '../cgi-bin/equgen.cgi';
	if(rendererSelector == 'server-png')
	{
		if(latex.length > 0)
		{
			let equationURL = urlPreview+'?'+latex;
			let img = document.createElement('img');
			img.src = equationURL;
			img.setAttribute('alt', latex);
			img.setAttribute('data-latex', latex);
			img.setAttribute('class', 'latex-image');
			img.style.verticalAlign='middle';
			let html = img.outerHTML;
			document.getElementById('image-container').innerHTML = html;
		}
		else
		{
			document.getElementById('image-container').innerHTML = '';
		}
	}
	else if(rendererSelector == 'mathjax-svg')
	{
		if(latex != '')
		{
			let img = document.createElement('img');
			let data = MathJax.tex2svg(latex).firstElementChild.outerHTML+'';
			let url = 'data:image/svg+xml;base64,'+Base64.encode(data);
			img.src = url;
			img.setAttribute('alt', latex);
			img.setAttribute('class', 'latex-image');
			img.style.verticalAlign='middle';
			document.getElementById('image-container').innerHTML = img.outerHTML;
		}
		else
		{
			document.getElementById('image-container').innerHTML = '';
		}
	}
	else
	{
		let svg = asciimath.latexToSVG(latex);
		let url = 'data:image/svg+xml;base64,'+Base64.encode(svg);
		let img = document.createElement('img');
		img.src = url;
		img.setAttribute('alt', latex);
		img.setAttribute('data-latex', latex);
		img.setAttribute('class', 'latex-image');
		img.style.verticalAlign='middle';
		let html = img.outerHTML;
		document.getElementById('image-container').innerHTML = html;
	}
}

function decodeLatexFromURI(data)
{
	if(data.indexOf('#latex%7C') > -1)
	{
		data = data.substr(data.indexOf('#latex%7C') + 9);
		data = decodeURIComponent(data);
	}
	return data;
}
function contains(arr, text)
{
	for(let i in arr)
	{
		if(arr[i].toLowerCase() == text.toLowerCase())
		{
			return true;
		}
	}
	return false;
}

function isContetRTF(types)
{
	return contains(types, 'text/html') || contains(types, 'text/rtf');
}

function handlePasteImage(e) 
{
	let data = '';
	if (e && e.clipboardData && e.clipboardData.getData) 
	{
		if(isContetRTF(e.clipboardData.types))
		{
			document.getElementById('renderer').value = 'mathml-png';
			data = e.clipboardData.getData('text/plain');
			try{
				data = asciimath.reconstructSqrtWord(data);
				data = asciimath.filterData(data);
				data = asciimath.reconstructMatrix(data);
				data = asciimath.reconstructVector(data);
				e.clipboardData.setData('text/html', data);
				document.getElementById('latex-input').value = data;
				renderLatex(data);
			}
			catch(e){
				data = asciimath.reconstructSqrtWord(data);
				data = asciimath.filterData(data);
				data = asciimath.reconstructMatrix(data);
				data = asciimath.reconstructVector(data);
				renderLatex(data);
				document.getElementById('latex-input').value = data;
			}
			if(e.preventDefault)
			{
				e.stopPropagation();
				e.preventDefault();
			}
		}
		else
		{
			data = e.clipboardData.getData('text/plain');
			data = asciimath.filterData(data);
			data = asciimath.reconstructMatrix(data);
			data = asciimath.reconstructVector(data);
			setTimeout(function(){
				if(data != '')
				{
					document.getElementById('latex-input').value = data;
				}
			}, 10);
			renderLatex(data);
		}
	}
}
let generatePNG = true;
function insertEquation(includeLatex)
{
	let latex = document.getElementById('latex-input').value;
	let rendererSelector = document.getElementById('renderer').value;
	
	if(rendererSelector == 'server-png')
	{
		if(latex.length > 0)
		{
			latex = asciimath.reconstructSqrtWord(latex);
			latex = asciimath.filterData(latex);
			latex = asciimath.reconstructMatrix(latex);
			latex = asciimath.reconstructVector(latex);
			let urlGenerator = window.parent.equationURLGenerator || '../cgi-bin/equgen.cgi';
			let url = urlGenerator+'?'+latex;
			let img = new Image();
			let canvas = document.createElement('canvas');
			let ctx = canvas.getContext('2d');
			img.onload = function() {
				canvas.setAttribute('width', img.width);
				canvas.setAttribute('height', img.height);
				ctx.drawImage(img, 0, 0);
				let dataURL = canvas.toDataURL('png');
				if(includeLatex)
				{
					window.parent.uploadBase64ImageFromLatex(dataURL, 2, 'png', 'latex|'+latex);
				}
				else
				{
					window.parent.uploadBase64ImageFromLatex(dataURL, 2, 'png');
				}
			}
			img.src = url;
		}
	}
	else if(rendererSelector == 'mathjax-svg')
	{
		if(latex != '')
		{
			let img = document.createElement('img');
			let data = MathJax.tex2svg(latex).firstElementChild.outerHTML+'';
			let url = 'data:image/svg+xml;base64,'+Base64.encode(data);
			if(includeLatex)
			{
				window.parent.uploadBase64ImageFromLatex(url, 1, 'svg', 'latex|'+latex);
			}
			else
			{
				window.parent.uploadBase64ImageFromLatex(url, 1, 'svg');
			}
		}
	}
	else if(rendererSelector == 'mathjax-png')
	{
		let data = MathJax.tex2svg(latex).firstElementChild.outerHTML+'';
		svgToPNG(data, function(base64EncodedURL){
			if(includeLatex)
			{
				window.parent.uploadBase64ImageFromLatex(base64EncodedURL, 2, 'png', 'latex|'+latex);
			}
			else
			{
				window.parent.uploadBase64ImageFromLatex(base64EncodedURL, 2, 'png');
			}
		});
		/*
		let DOMURL = window.URL || window.webkitURL || window;		
		let img = new Image();
		let svg = new Blob([data], {type: 'image/svg+xml'});
		let url = DOMURL.createObjectURL(svg);
		let canvas = document.createElement('canvas');
		let ctx = canvas.getContext('2d');		
		img.onload = function() {
			canvas.setAttribute('width', img.width);
			canvas.setAttribute('height', img.height);
			ctx.drawImage(img, 0, 0);
			DOMURL.revokeObjectURL(url);
			if(includeLatex)
			{
				window.parent.uploadBase64ImageFromLatex(canvas.toDataURL('png'), 2, 'png', 'latex|'+latex);
			}
			else
			{
				window.parent.uploadBase64ImageFromLatex(canvas.toDataURL('png'), 2, 'png');
			}
		}
		img.src = url;
		*/
	}
	else if(rendererSelector == 'mathml-svg')
	{
		let data = asciimath.latexToSVG(latex, true, true);
		if(includeLatex)
		{
			window.parent.uploadBase64ImageFromLatex('data:image/svg+xml;base64,'+Base64.encode(data), 2, 'svg', 'latex|'+latex);
		}
		else
		{
			window.parent.uploadBase64ImageFromLatex('data:image/svg+xml;base64,'+Base64.encode(data), 2, 'svg');
		}
	}
	else 
	{
		let data = asciimath.latexToSVG(latex, true, true);
		svgToPNG(data, function(base64EncodedURL){
			if(includeLatex)
			{
				window.parent.uploadBase64ImageFromLatex(base64EncodedURL, 2, 'png', 'latex|'+latex);
			}
			else
			{
				window.parent.uploadBase64ImageFromLatex(base64EncodedURL, 2, 'png');
			}
		});
		/*
		let DOMURL = window.URL || window.webkitURL || window;		
		let img = new Image();
		let svg = new Blob([data], {type: 'image/svg+xml'});
		let url = DOMURL.createObjectURL(svg);
		let canvas = document.createElement('canvas');
		let ctx = canvas.getContext('2d');		
		img.onload = function() {
			canvas.setAttribute('width', img.width);
			canvas.setAttribute('height', img.height);
			ctx.drawImage(img, 0, 0);
			DOMURL.revokeObjectURL(url);
			if(includeLatex)
			{
				window.parent.uploadBase64ImageFromLatex(canvas.toDataURL('png'), 2, 'png', 'latex|'+latex);
			}
			else
			{
				window.parent.uploadBase64ImageFromLatex(canvas.toDataURL('png'), 2, 'png');
			}
		}
		img.src = url;
		*/
	}
}

/**
 * Convert SVG document to PNG without resize it
 * @param {string} svgData String of SVG document
 * @param {function} onloadCallback Callback function when process has been finished
 */
function svgToPNG(svgData, onloadCallback)
{
	var DOMURL = window.URL || window.webkitURL || window;	
	var img = new Image();
	var svg = new Blob([svgData], {type: 'image/svg+xml'});
	var url = DOMURL.createObjectURL(svg);
	var canvas = document.createElement('canvas');
	var ctx = canvas.getContext('2d');
	img.onload = function() {
		canvas.setAttribute('width', img.width);
		canvas.setAttribute('height', img.height);
		ctx.drawImage(img, 0, 0);
		DOMURL.revokeObjectURL(url);
		onloadCallback(canvas.toDataURL('png'));	
	}
	img.src = url;
}