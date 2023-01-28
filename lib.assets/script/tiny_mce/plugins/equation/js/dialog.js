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
		document.getElementById('renderer').value = ed.getParam('equation_renderer_machine') || 'server-png';
	},

	insert : function() {
		// Insert the contents from the input into the document
		let ed = tinyMCEPopup.editor;
		let jsonObj = $('.eqEdEquation').data('eqObject').buildJsonObj();
		let latexData = generateLatex(jsonObj.operands.topLevelContainer);
		let latex = latexData.toString();
		let jsonData = {latex:latexData, json:jsonObj}
		let data = asciimath.latexToSVG(latex, true, true);
		if(generatePNG)
		{
			let rendererSelector = document.getElementById('renderer').value;
			latex = latex.trim();
			latex = asciimath.reconstructSqrtWord(latex);
			latex = asciimath.filterData(latex);
			latex = asciimath.reconstructMatrix(latex);
			if(rendererSelector == 'server-png')
			{
				if(latex.length > 0)
				{	
					let urlGenerator = ed.getParam('equation_generator_url') || '../../../../../../cgi-bin/equgen.cgi';
					let url = urlGenerator+''+latex;		
					let img = new Image();
					let canvas = document.createElement('canvas');
					let ctx = canvas.getContext('2d');
					img.onload = function() {
						canvas.setAttribute('width', img.width);
						canvas.setAttribute('height', img.height);
						ctx.drawImage(img, 0, 0);
						let url = canvas.toDataURL('png');
						let html = '<img class="equation-image" style="vertical-align:middle" src="'+url+'" alt="'+latex+'" data-equation="'+encodeURIComponent(JSON.stringify(jsonData))+'">';
						tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
						tinyMCEPopup.close();
					}				
					img.src = url;
				}
			}
			else if(rendererSelector == 'browser-mathjax')
			{
				let data = MathJax.tex2svg(latex).firstElementChild.outerHTML+'';
				let url = 'data:image/svg+xml;base64,'+Base64.encode(data);
				let img2 = document.createElement('img');
				img2.src = url;
				img2.setAttribute('alt', latex);
				img2.setAttribute('data-latex', latex);
				img2.setAttribute('class', 'latex-image');
				img2.style.verticalAlign='middle';
				let html = img2.outerHTML;
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
				tinyMCEPopup.close();
	
			}
			else if(rendererSelector == 'browser-mathml')
			{
				let url = 'data:image/svg+xml;base64,'+Base64.encode(data);
				let html = '<img class="equation-image" style="vertical-align:middle" src="'+url+'" alt="'+latex+'" data-equation="'+encodeURIComponent(JSON.stringify(jsonData))+'">';
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
				tinyMCEPopup.close();
			}
			else
			{	
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
					let url2 = canvas.toDataURL('png');
					let html = '<img class="equation-image" style="vertical-align:middle" src="'+url2+'" alt="'+latex+'" data-equation="'+encodeURIComponent(JSON.stringify(jsonData))+'">';
					tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
					tinyMCEPopup.close();
				}				
				img.src = url;
			}
		}
		else
		{		
			let url = 'data:image/svg+xml;base64,'+Base64.encode(data);	
			let html = '<img class="equation-image" style="vertical-align:middle" src="'+url+'" alt="'+latex+'" data-equation="'+encodeURIComponent(JSON.stringify(data))+'">';
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
			tinyMCEPopup.close();
		}
	}
};

tinyMCEPopup.onInit.add(EquationDialog.init, EquationDialog);
