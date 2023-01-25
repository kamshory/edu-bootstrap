tinyMCEPopup.requireLangPack();

var generatePNG = true;
var ChemsitryEditorDialog = {
	init : function() {
	},

	insert : function() {
		// Insert the contents from the input into the document
		var ed = tinyMCEPopup.editor;
		var parentNode = document.getElementsByClassName("objContextParentElem")[0];
		var canvas = parentNode.getElementsByTagName('canvas')[0];
		var canvas2 = trimCanvas(canvas);
		var html = '<img src="'+canvas2.toDataURL()+'" style="vertical-align:text-top">';
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
		tinyMCEPopup.close();

	}
};

tinyMCEPopup.onInit.add(ChemsitryEditorDialog.init, ChemsitryEditorDialog);
window.onload = function(e)
{
}
var chemEditor;
var chemComposer		
function init()
{
	var elem = document.getElementById('chemComposer');
	var chemEditor = new Kekule.Editor.ChemSpaceEditor(document, null, Kekule.Render.RendererType.R2D);
	chemComposer = new Kekule.Editor.Composer(elem, chemEditor);
	adjustSize();
	window.onresize = adjustSize;
}
function adjustSize()
{
	var dim = Kekule.HtmlElementUtils.getViewportDimension(document);
	chemComposer.setWidth(dim.width - 24 + 'px').setHeight(dim.height - 55 + 'px');
}
Kekule.X.domReady(init);
function trimCanvas(t){var l,a,e,o=t.getContext("2d"),n=document.createElement("canvas").getContext("2d"),g=o.getImageData(0,0,t.width,t.height),h=g.data.length,i={top:null,left:null,right:null,bottom:null};for(l=0;l<h;l+=4)0!==g.data[l+3]&&(a=l/4%t.width,e=~~(l/4/t.width),null===i.top&&(i.top=e),null===i.left?i.left=a:a<i.left&&(i.left=a),null===i.right?i.right=a:i.right<a&&(i.right=a),null===i.bottom?i.bottom=e:i.bottom<e&&(i.bottom=e));i.bottom<t.height-1&&i.bottom++,i.right<t.width-1&&i.right++;var m,r=i.bottom-i.top,u=i.right-i.left;return m=0==u||0==r||null==i.left||null==i.top?o.getImageData(0,0,1,1):o.getImageData(i.left,i.top,u,r),n.canvas.width=u,n.canvas.height=r,n.putImageData(m,0,0),n.canvas}
