tinyMCEPopup.requireLangPack();

var generatePNG = true;
var gHTML = '';
var gEquation = null;
var EquationDialog = {
	init : function() {
		var f = document.forms[0];
		var ed = tinyMCEPopup.editor, 
			dom = ed.dom, 
			n = ed.selection.getNode();
		var data = decodeURIComponent(dom.getAttrib(n, 'data-equation'));
		if(data != '')
		{
			var obj = JSON.parse(data);
			var jsonObj = obj.json;
			var equation = eqEd.Equation.constructFromJsonObj(jsonObj);
			var html = equation.domObj.value;
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
		var ed = tinyMCEPopup.editor;
		var latexURL = ed.getParam('equation_preview_url'); 
		var jsonObj = $('.eqEdEquation').data('eqObject').buildJsonObj();
		var latexData = generateLatex(jsonObj.operands.topLevelContainer);
		var latex = latexData.toString();
		var jsonData = {latex:latexData, json:jsonObj}
		var data = asciimath.latexToSVG(latex, true, true);
		if(generatePNG)
		{
			var equgen = document.getElementById('renderer').value;
			latex = latex.trim();
			latex = asciimath.reconstructSqrtWord(latex);
			latex = asciimath.filterData(latex);
			latex = asciimath.reconstructMatrix(latex);
			if(equgen == 'server-png')
			{
				if(latex.length > 0)
				{
	
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
						var url = canvas.toDataURL('png');
						var html = '<img class="equation-image" style="vertical-align:middle" src="'+url+'" alt="'+latex+'" data-equation="'+encodeURIComponent(JSON.stringify(jsonData))+'">';
						tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
						tinyMCEPopup.close();
					}
					
					img.src = url;
				}
			}
			else if(equgen == 'browser-mathml')
			{
				var url = 'data:image/svg+xml;base64,'+Base64.encode(data);
				var html = '<img class="equation-image" style="vertical-align:middle" src="'+url+'" alt="'+latex+'" data-equation="'+encodeURIComponent(JSON.stringify(jsonData))+'">';
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
				tinyMCEPopup.close();

			}
			else
			{
	
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

					var html = '<img class="equation-image" style="vertical-align:middle" src="'+url+'" alt="'+latex+'" data-equation="'+encodeURIComponent(JSON.stringify(jsonData))+'">';
					tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
					tinyMCEPopup.close();
				}
				
				img.src = url;
			}
		}
		else
		{
		
			var url = 'data:image/svg+xml;base64,'+Base64.encode(data);
			
			var html = '<img class="equation-image" style="vertical-align:middle" src="'+url+'" alt="'+latex+'" data-equation="'+encodeURIComponent(JSON.stringify(data))+'">';
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
			tinyMCEPopup.close();
		}
	}
};

tinyMCEPopup.onInit.add(EquationDialog.init, EquationDialog);
