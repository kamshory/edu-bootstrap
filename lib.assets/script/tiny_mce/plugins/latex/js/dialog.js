tinyMCEPopup.requireLangPack();
let generatePNG = true;
let LatexDialog = {
	init : function() {
		let ed = tinyMCEPopup.editor, 
			dom = ed.dom, 
			n = ed.selection.getNode();
		let latex = decodeURIComponent(dom.getAttrib(n, 'data-latex')) || '';
		if(latex == '')
		{
			latex = decodeURIComponent(dom.getAttrib(n, 'alt')) || '';
		}
		latex = latex.trim();
		if(latex == '')
		{
			let obj1, obj2, obj3;
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
		let svg = asciimath.latexToSVG(latex);
		let url = 'data:image/svg+xml;base64,'+Base64.encode(svg);
		let img = document.createElement('img');
		img.src = url;
		img.setAttribute('alt', latex);
		img.setAttribute('data-latex', latex);
		img.setAttribute('class', 'latex-image');
		img.style.verticalAlign='middle';
		document.getElementById('latex-input').value = latex;
		document.getElementById('renderer').value = ed.getParam('equation_renderer_machine') || 'browser-mathjax';
	},

	insert : function() {
		// Insert the contents from the input into the document
		let ed = tinyMCEPopup.editor;
		let rendererSelector = document.getElementById('renderer').value;
		let latex = document.getElementById('latex-input').value;
		latex = latex.trim();
		if(rendererSelector == 'server-png')
		{
			if(latex.length > 0)
			{
				latex = asciimath.reconstructSqrtWord(latex);
				latex = asciimath.filterData(latex);
				latex = asciimath.reconstructMatrix(latex);

				let urlGenerator = ed.getParam('equation_generator_url') || '../../../../../../cgi-bin/equgen.cgi';
				let url = urlGenerator+''+latex;
	
				let img = new Image();
				let canvas = document.createElement('canvas');
				let ctx = canvas.getContext('2d');
				img.onload = function() {
					canvas.setAttribute('width', img.width);
					canvas.setAttribute('height', img.height);
					ctx.drawImage(img, 0, 0);
					let dataURL = canvas.toDataURL('png');
					let img2 = document.createElement('img');
					img2.src = dataURL;
					img2.setAttribute('alt', latex);
					img2.setAttribute('data-latex', latex);
					img2.setAttribute('data-renderer', rendererSelector);
					img2.setAttribute('class', 'latex-image');
					img2.style.verticalAlign='middle';
					tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
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
			img2.setAttribute('data-renderer', rendererSelector);
			img2.setAttribute('class', 'latex-image');
			img2.style.verticalAlign='middle';
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
			tinyMCEPopup.close();
		}
		else if(rendererSelector == 'browser-mathml')
		{
			let data = asciimath.latexToSVG(latex, true, true);
			let url = 'data:image/svg+xml;base64,'+Base64.encode(data);
			let img2 = document.createElement('img');
			img2.src = url;
			img2.setAttribute('alt', latex);
			img2.setAttribute('data-latex', latex);
			img2.setAttribute('data-renderer', rendererSelector);
			img2.setAttribute('class', 'latex-image');
			img2.style.verticalAlign='middle';
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
			tinyMCEPopup.close();
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
				let url2 = canvas.toDataURL('png');
				let img2 = document.createElement('img');
				img2.src = url2;
				img2.setAttribute('alt', latex);
				img2.setAttribute('data-latex', latex);
				img2.setAttribute('data-renderer', rendererSelector);
				img2.setAttribute('class', 'latex-image');
				img2.style.verticalAlign='middle';
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
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
	document.getElementById('renderer').addEventListener('change', function(e){
		let data = document.getElementById('latex-input').value;
		data = asciimath.filterData(data);
		data = asciimath.reconstructMatrix(data);
		data = asciimath.reconstructVector(data);
		renderLatex(data);
	});
	document.getElementById('latex-input').addEventListener('paste', handlePasteImage);
	document.getElementById('latex-input').focus();
	let latex = document.getElementById('latex-input').value;
	let data = latex;
	data = asciimath.filterData(data);
	data = asciimath.reconstructMatrix(data);
	data = asciimath.reconstructVector(data);
	renderLatex(data);
}
function renderLatex(latex){
	latex = latex.trim();
	let ed = tinyMCEPopup.editor;
	let urlPreview = ed.getParam('equation_preview_url') || '../../../../../../cgi-bin/equgen.cgi';
	let rendererSelector = document.getElementById('renderer').value;
	if(rendererSelector == 'server-png')
	{
		if(latex != '')
		{
			let img = document.createElement('img');
			img.src = urlPreview+latex;
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
	else if(rendererSelector == 'browser-mathjax')
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
		img.setAttribute('class', 'latex-image');
		img.style.verticalAlign='middle';
		document.getElementById('image-container').innerHTML = img.outerHTML;
	}
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
function handlePasteImage(e) 
{
	if (e && e.clipboardData && e.clipboardData.getData) 
	{
		let data;
		if(contains(e.clipboardData.types, 'text/html') || contains(e.clipboardData.types, 'text/rtf'))
		{
			let data = e.clipboardData.getData('text/plain');
			try{
				data = asciimath.reconstructSqrtWord(data);
				data = asciimath.filterData(data);
				data = asciimath.reconstructMatrix(data);
				data = asciimath.reconstructVector(data);
				e.clipboardData.setData('text/html', data);
				renderLatex(data);
				document.getElementById('latex-input').value = data;
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
