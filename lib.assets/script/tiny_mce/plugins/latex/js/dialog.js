tinyMCEPopup.requireLangPack();

var generatePNG = true;
var LatexDialog = {
	init : function() {
		var f = document.forms[0];
		var ed = tinyMCEPopup.editor, 
			dom = ed.dom, 
			n = ed.selection.getNode();
		var latex = decodeURIComponent(dom.getAttrib(n, 'data-latex')) || '';
		if(latex == '')
		{
			latex = decodeURIComponent(dom.getAttrib(n, 'alt')) || '';
		}
		latex = latex.trim();
		if(latex == '')
		{
			var obj1, obj2, obj3;
			latex = ed.selection.getContent();
			if(latex.indexOf('<sup') > -1 || latex.indexOf('<sub') > -1)
			{
				obj1 = $('<div>'+latex+'</div>');
				obj1.find('sub').each(function(index, element) {
                    obj2 = $(this)[0].outerHTML;
					obj3 = $(this).text();
					latex = latex.replace(obj2, '_{'+obj3+'}');
                });
				obj1.find('sup').each(function(index, element) {
                    obj2 = $(this)[0].outerHTML;
					obj3 = $(this).text();
					latex = latex.replace(obj2, '^{'+obj3+'}');
                });
				obj1 = $('<div>'+latex+'</div>');
				latex = obj1.text();
				latex = latex.trim();
			}
			else
			{
				obj1 = $('<div>'+latex+'</div>');
				latex = obj1.text();
				latex = latex.trim();
			}
		}
		latex = asciimath.reconstructSqrtWord(latex);
		latex = asciimath.filterData(latex);
		latex = asciimath.reconstructMatrix(latex);
		latex = asciimath.reconstructVector(latex);
		var svg = asciimath.latexToSVG(latex);
		var url = 'data:image/svg+xml;base64,'+Base64.encode(svg);
		var img = document.createElement('img');
		img.src = url;
		img.setAttribute('alt', latex);
		img.setAttribute('data-latex', latex);
		img.setAttribute('class', 'latex-image');
		img.style.verticalAlign='middle';
		var html = img.outerHTML;
		document.getElementById('latex-input').value = latex;
		document.getElementById('renderer').value = ed.getParam('equation_renderer_machine') || 'server-png';
	},

	insert : function() {
		// Insert the contents from the input into the document
		var ed = tinyMCEPopup.editor;
		var equgen = document.getElementById('renderer').value;
		var latex = document.getElementById('latex-input').value;
		latex = latex.trim();
		if(equgen == 'server-png')
		{
			if(latex.length > 0)
			{
				latex = asciimath.reconstructSqrtWord(latex);
				latex = asciimath.filterData(latex);
				latex = asciimath.reconstructMatrix(latex);
				data = asciimath.reconstructVector(data);

				var urlGenerator = ed.getParam('equation_generator_url') || '../../../../../../cgi-bin/equgen.cgi';
				var urlPreview = ed.getParam('equation_preview_url') || '../../../cgi-bin/equgen.cgi';
				var url = urlGenerator+''+latex;
	
				var img = new Image();
				var canvas = document.createElement('canvas');
				var ctx = canvas.getContext('2d');
				img.onload = function() {
					canvas.setAttribute('width', img.width);
					canvas.setAttribute('height', img.height);
					ctx.drawImage(img, 0, 0);
					var dataURL = canvas.toDataURL('png');
					var img2 = document.createElement('img');
					img2.src = dataURL;
					img2.setAttribute('alt', latex);
					img2.setAttribute('data-latex', latex);
					img2.setAttribute('class', 'latex-image');
					img2.style.verticalAlign='middle';
					var html = img2.outerHTML;
			
					tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
					tinyMCEPopup.close();
				}
				
				img.src = url;
			}
		}
		else if(equgen == 'browser-mathml')
		{
			var data = asciimath.latexToSVG(latex, true, true);
			var url = 'data:image/svg+xml;base64,'+Base64.encode(data);
			var img2 = document.createElement('img');
			img2.src = url;
			img2.setAttribute('alt', latex);
			img2.setAttribute('data-latex', latex);
			img2.setAttribute('class', 'latex-image');
			img2.style.verticalAlign='middle';
			var html = img2.outerHTML;
	
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
			tinyMCEPopup.close();

		}
		else
		{
			var data = asciimath.latexToSVG(latex, true, true);
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

				var url = canvas.toDataURL('png');
				var img2 = document.createElement('img');
				img2.src = url;
				img2.setAttribute('alt', latex);
				img2.setAttribute('data-latex', latex);
				img2.setAttribute('class', 'latex-image');
				img2.style.verticalAlign='middle';
				var html = img2.outerHTML;
		
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
				tinyMCEPopup.close();

			}
			
			img.src = url;
		}
	}
};

tinyMCEPopup.onInit.add(LatexDialog.init, LatexDialog);
window.onload = function(e)
{
	document.getElementById('latex-input').addEventListener('change', function(e){
		var data = e.target.value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('keyup', function(e){
		var data = e.target.value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('blur', function(e){
		var data = e.target.value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('focus', function(e){
		var data = e.target.value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('renderer').addEventListener('change', function(e){
		var data = document.getElementById('latex-input').value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('paste', handlePasteImage);
	document.getElementById('latex-input').focus();
	var latex = document.getElementById('latex-input').value;
	var data = latex;
	data = asciimath.filterData(data);
	data = asciimath.reconstructMatrix(data);
	data = asciimath.reconstructVector(data);
	renderLatex(data);
}
function renderLatex(latex){
	latex = latex.trim();
	var ed = tinyMCEPopup.editor;
	var urlPreview = ed.getParam('equation_preview_url') || '../../../../../../cgi-bin/equgen.cgi';
	var equgen = document.getElementById('renderer').value;
	if(equgen == 'server-png')
	{
		if(latex != '')
		{
			var img = document.createElement('img');
			img.src = urlPreview+latex;
			img.setAttribute('alt', latex);
			img.setAttribute('data-latex', latex);
			img.setAttribute('class', 'latex-image');
			img.style.verticalAlign='middle';
			var html = img.outerHTML;
			document.getElementById('image-container').innerHTML = html;
		}
		else
		{
			document.getElementById('image-container').innerHTML = '';
		}
	}
	else
	{
		var svg = asciimath.latexToSVG(latex);
		var url = 'data:image/svg+xml;base64,'+Base64.encode(svg);
		var img = document.createElement('img');
		img.src = url;
		img.setAttribute('alt', latex);
		img.setAttribute('data-latex', latex);
		img.setAttribute('class', 'latex-image');
		img.style.verticalAlign='middle';
		var html = img.outerHTML;
		document.getElementById('image-container').innerHTML = html;
	}
}
function handlePasteImage(e) 
{
	if (e && e.clipboardData && e.clipboardData.getData) 
	{
		if(/text\/html/.test(e.clipboardData.types))
		{
			var data = e.clipboardData.getData('text/plain');
			try{
				data = asciimath.reconstructSqrtWord(data);
				data = asciimath.filterData(data);
				data = asciimath.reconstructMatrix(data);
				data = asciimath.reconstructVector(data);
				e.clipboardData.setData('text/html', data);
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
	else 
	{
	}
}
