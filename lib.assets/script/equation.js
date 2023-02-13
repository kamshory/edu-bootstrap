window.onload = function()
{
	var rendererSelector = 'mathjax-svg';
	document.getElementById('renderer').value = rendererSelector;
};
var generatePNG = true;
function replaceAllText(input, findStr, replaceStr)
{
  var arr = input.split(findStr);
  return arr.join(replaceStr);
}
function insertEquation(includeLatex)
{
	var jsonObj = $('.eqEdEquation').data('eqObject').buildJsonObj();
	var latex = generateLatex(jsonObj.operands.topLevelContainer);
	latex = asciimath.reconstructSqrtWord(latex);
	latex = asciimath.filterData(latex);
	latex = asciimath.reconstructMatrix(latex);
	latex = asciimath.reconstructVector(latex);
	var rendererSelector = document.getElementById('renderer').value;
	if(rendererSelector == 'server-png')
	{
		if(latex.length > 0)
		{
			var urlGenerator = window.parent.equationURLGenerator || '../cgi-bin/equgen.cgi';
			var urlPreview = window.parent.equationURLPreview || '../cgi-bin/equgen.cgi';
			var url = urlGenerator+'?'+latex;
			var img = new Image();
			var canvas = document.createElement('canvas');
			var ctx = canvas.getContext('2d');
			
			img.onload = function() {
				canvas.setAttribute('width', img.width);
				canvas.setAttribute('height', img.height);
				ctx.drawImage(img, 0, 0);
				var dataURL = canvas.toDataURL('png');
				if(includeLatex)
				{
					window.parent.uploadBase64ImageFromLatex(dataURL, 2, 'png', 'latex|'+latex);
				}
				else
				{
					window.parent.uploadBase64ImageFromLatex(dataURL, 2, 'png',);
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
		if(latex != '')
		{
			let img = document.createElement('img');
			let data = MathJax.tex2svg(latex).firstElementChild.outerHTML+'';
			svgToPNG(data, function(base64EncodedURL){
				if(includeLatex)
				{
					window.parent.uploadBase64ImageFromLatex(base64EncodedURL, 1, 'png', 'latex|'+latex);
				}
				else
				{
					window.parent.uploadBase64ImageFromLatex(base64EncodedURL, 1, 'png');
				}
			});
		}
	}
  	else if(rendererSelector == 'browser-svg')
  	{
		var data = asciimath.latexToSVG(latex, true, true);
		if(includeLatex)
		{
			window.parent.uploadBase64ImageFromLatex('data:image/svg+xml;base64,'+Base64.encode(data), 1, 'svg', 'latex|'+latex);
		}
		else
		{
			window.parent.uploadBase64ImageFromLatex('data:image/svg+xml;base64,'+Base64.encode(data), 1, 'svg');
		}
		
  	}
	else
	{
		var data = asciimath.latexToSVG(latex, true, true);
		svgToPNG(data, function(base64EncodedURL){
			if(includeLatex)
			{
				window.parent.uploadBase64ImageFromLatex(base64EncodedURL, 1, 'png', 'latex|'+latex);
			}
			else
			{
				window.parent.uploadBase64ImageFromLatex(base64EncodedURL, 1, 'png');
			}
		});

		/**
		
		var DOMURL = window.URL || window.webkitURL || window;
		
		var img = new Image();
		var svg = new Blob([data], {type: 'image/svg+xml'});
		var url = DOMURL.createObjectURL(svg);
		
		var canvas = document.createElement('canvas');
		var ctx = canvas.getContext('2d');
		
		img.onload = function() {
			canvas.setAttribute('width', img.width);
			canvas.setAttribute('height', img.height);
			ctx.drawImage(img, 0, 0);
			DOMURL.revokeObjectURL(url);
			if(includeLatex)
			{
				window.parent.uploadBase64ImageFromLatex(canvas.toDataURL('png'), 1, 'png', 'latex|'+latex);
			}
			else
			{
				window.parent.uploadBase64ImageFromLatex(canvas.toDataURL('png'), 1, 'png');
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