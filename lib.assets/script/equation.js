window.onload = function()
{
	let equgen = window.parent.equationRenderer || 'browser';
	document.getElementById('renderer').value = equgen;
};
let generatePNG = true;
function replaceAllText(input, findStr, replaceStr)
{
  let arr = input.split(findStr);
  return arr.join(replaceStr);
}
function insertEquation(includeLatex)
{
	let jsonObj = $('.eqEdEquation').data('eqObject').buildJsonObj();
	let latex = generateLatex(jsonObj.operands.topLevelContainer);
	latex = asciimath.reconstructSqrtWord(latex);
	latex = asciimath.filterData(latex);
	latex = asciimath.reconstructMatrix(latex);
	latex = asciimath.reconstructVector(latex);
	let equgen = document.getElementById('renderer').value;
	if(equgen == 'server-png')
	{
		if(latex.length > 0)
		{
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
	else if(equgen == 'browser-mathml')
	{
		let data = asciimath.latexToSVG(latex, true, true);
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
		let data = asciimath.latexToSVG(latex, true, true);
		
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
				window.parent.uploadBase64ImageFromLatex(canvas.toDataURL('png'), 1, 'png', 'latex|'+latex);
			}
			else
			{
				window.parent.uploadBase64ImageFromLatex(canvas.toDataURL('png'), 1, 'png');
			}
		}
		
		img.src = url;
	}
}
