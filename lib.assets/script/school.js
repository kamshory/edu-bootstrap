$(document).ready(function(e) {
$(document).on('click', '.mobile-menu-trigger', function(e){
var disp = $(this).siblings('ul').attr('data-mobile-display') || 'false';
if(disp == 'false') disp = 'true';
else disp = 'false';
$(this).siblings('ul').attr('data-mobile-display', disp);
e.preventDefault();
});
});