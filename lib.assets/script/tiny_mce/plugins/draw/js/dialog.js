tinyMCEPopup.requireLangPack();
let generatePNG = true;
let gHTML = '';
let gDraw = null;
let DrawDialog = {
	init : function() {
		let ed = tinyMCEPopup.editor, 
			dom = ed.dom, 
			n = ed.selection.getNode();
		let data = decodeURIComponent(dom.getAttrib(n, 'src')).toString();
		
		if(data != '')
		{
			if(data.indexOf('data:image') == -1)
			{
				// URL is a file
				if(data.indexOf('://') == -1)
				{
					data = '../../../../../'+data;
				}
				$.ajax({
					method:'GET',
					url:data,
					dataType:'text',
					cache:false,
					success:function(answer)
					{
						placeSVGToEditor(answer);
					}
				});
			}
			else
			{
				// URL is base64 encoded
				data = data.substring(data.indexOf(',')+1);
				placeSVGToEditor(Base64.decode(data));
				
			}
			
		}
	},

	insert : function() {
		// Insert the contents from the input into the document

		let data = svgCanvas.svgCanvasToString()+'';
		let url = 'data:image/svg+xml;base64,'+Base64.encode(data);
		let img2 = document.createElement('img');
		img2.src = url;
		img2.setAttribute('class', 'draw-image');
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, img2.outerHTML);
		tinyMCEPopup.close();
	
	}
};

function placeSVGToEditor(data)
{
	svgCanvas.setSvgString(data);
}

tinyMCEPopup.onInit.add(DrawDialog.init, DrawDialog);
