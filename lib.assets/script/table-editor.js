let originalSelection = '';
$(document).ready(function(e){
    let url = window.location.toString();
    var data = url.substring(url.indexOf("#")+1);
    let data2 = LZString.decompressFromBase64(data);
    originalSelection = data2;
    let data3 = detectTable(data2);
    document.querySelector('#summernote').innerHTML = data3;
    $('#summernote').summernote({
        placeholder: '',
        tabsize: 2,
        height: 300,
        toolbar:[
            ['table', ['table', 'codeview']],
            ['view', ['insertToEditor']]
        ]
    });
})