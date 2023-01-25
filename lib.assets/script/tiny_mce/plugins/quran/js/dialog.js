tinyMCEPopup.requireLangPack();

var QuranDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		//f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		//f.somearg.value = tinyMCEPopup.getWindowArg('some_custom_arg');
	},

	insert : function() {
		// Insert the contents from the input into the document
		var surah = document.forms[0].surah.value;
		var verse = document.forms[0].verse.value;
		var verse2 = document.forms[0].verse2.value;
		var lang = document.forms[0].lang.value;
		var val = '';
		var ed = tinyMCEPopup.editor;
		var base_src = ed.getParam('quran_server');
		if(base_src == '../quran')
		{
			var uri = '../../../../'+base_src+'/'+lang+'/'+surah+'/'+verse+'-'+verse2+'.htm';
		}
		else
		{
			var uri = base_src+'/'+lang+'/'+surah+'/'+verse+'-'+verse2+'.htm';
		}
 		if(document.getElementById('versenumber').checked)
			uri+='?versenumber=1';
		$.get(uri, {}, function(answer){
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, answer);
		tinyMCEPopup.close();
		});
	}
};

tinyMCEPopup.onInit.add(QuranDialog.init, QuranDialog);
