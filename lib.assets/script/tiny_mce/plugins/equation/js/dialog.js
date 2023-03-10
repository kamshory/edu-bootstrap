tinyMCEPopup.requireLangPack();
let generatePNG = true;
let gHTML = '';
let gEquation = null;
let EquationDialog = {
	init : function() {
		let ed = tinyMCEPopup.editor, 
			dom = ed.dom, 
			n = ed.selection.getNode();
		let data = decodeURIComponent(dom.getAttrib(n, 'data-equation'));
		
		if(data != '')
		{
			if(data.indexOf('{"') === -1)
			{
				try
				{
					data = LZString.decompressFromBase64(data);
				}
				catch(e)
				{
					// Do nothing
				}
			}
			let obj = JSON.parse(data);
			let jsonObj = obj.json;
			let equation = eqEd.Equation.constructFromJsonObj(jsonObj);
			let html = equation.domObj.value;
			gHTML = html;
			gEquation = equation;
			setTimeout(function(){
			}, 6000);
			$('.main-editor').empty();
			$('.main-editor').append(html);
			equation.updateAll();
		}
		document.getElementById('renderer').value = ed.getParam('equation_renderer_machine') || 'mathjax-svg';
	},

	insert : function() {
		// Insert the contents from the input into the document
		let ed = tinyMCEPopup.editor;
		let jsonObj = $('.eqEdEquation').data('eqObject').buildJsonObj();
		let latexData = generateLatex(jsonObj.operands.topLevelContainer);
		let latex = latexData.toString();
		let jsonData = {latex:latexData, json:jsonObj}
		let data = asciimath.latexToSVG(latex, true, true);
		let rendererSelector = document.getElementById('renderer').value;
		latex = latex.trim();
		latex = asciimath.reconstructSqrtWord(latex);
		latex = asciimath.filterData(latex);
		latex = asciimath.reconstructMatrix(latex);
		if(latex.length > 0)
		{
			if(rendererSelector == 'mathjax-svg')
			{
				let data = MathJax.tex2svg(latex).firstElementChild.outerHTML+'';
				let url = 'data:image/svg+xml;base64,'+Base64.encode(data);
				let img2 = document.createElement('img');
				img2.src = url;
				img2.setAttribute('alt', latex);
				img2.setAttribute('data-equation', LZString.compressToBase64(JSON.stringify(jsonData)));
				img2.setAttribute('data-latex', latex);
				img2.setAttribute('data-renderer', rendererSelector);
				img2.setAttribute('class', 'latex-image equation-image');
				img2.style.verticalAlign='middle';
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
				tinyMCEPopup.close();
			}
			else if(rendererSelector == 'mathjax-png')
			{
				let data = MathJax.tex2svg(latex).firstElementChild.outerHTML+'';
				svgToPNG(data, function(base64EncodedURL){
					let img2 = document.createElement('img');
					img2.src = base64EncodedURL;
					img2.setAttribute('alt', latex);
					img2.setAttribute('data-equation', LZString.compressToBase64(JSON.stringify(jsonData)));
					img2.setAttribute('data-latex', latex);
					img2.setAttribute('data-renderer', rendererSelector);
					img2.setAttribute('class', 'latex-image equation-image');
					img2.style.verticalAlign='middle';
					tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
					tinyMCEPopup.close();	
				});			
			}
			else if(rendererSelector == 'mathml-svg')
			{
				let url = 'data:image/svg+xml;base64,'+Base64.encode(data);
				let img2 = document.createElement('img');
				img2.src = url;
				img2.setAttribute('alt', latex);
				img2.setAttribute('data-equation', LZString.compressToBase64(JSON.stringify(jsonData)));
				img2.setAttribute('data-latex', latex);
				img2.setAttribute('data-renderer', rendererSelector);
				img2.setAttribute('class', 'latex-image equation-image');
				img2.style.verticalAlign='middle';
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
				tinyMCEPopup.close();
			}
			else
			{
				svgToPNG(data, function(base64EncodedURL){
					let img2 = document.createElement('img');
					img2.src = base64EncodedURL;
					img2.setAttribute('alt', latex);
					img2.setAttribute('data-equation', LZString.compressToBase64(JSON.stringify(jsonData)));
					img2.setAttribute('data-latex', latex);
					img2.setAttribute('data-renderer', rendererSelector);
					img2.setAttribute('class', 'latex-image equation-image');
					img2.style.verticalAlign='middle';
					tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
					tinyMCEPopup.close();
				});
				
			}				
		}
	}
};

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

tinyMCEPopup.onInit.add(EquationDialog.init, EquationDialog);
