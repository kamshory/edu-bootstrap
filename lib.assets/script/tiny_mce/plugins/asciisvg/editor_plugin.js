/**
 * ASCIIsvg plugin for TinyMCE.
 *   port of ASCIIsvg plugin for HTMLArea written by 
 *   David Lippman and Peter Jipsen
 *
 * @author David Lippman
 * @copyright Copyright © 2008 David Lippman.
 *
 * Plugin format based on code that is:
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('asciisvg');

	tinymce.create('tinymce.plugins.AsciisvgPlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var t= this;

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceAsciisvg');
			ed.addCommand('mceAsciisvg', function() {
				el = ed.selection.getNode();
				
				//if (el.nodeName == 'IMG' && ed.dom.getAttrib(el,"data-sscr")!='') {
				if (el.nodeName == 'IMG' && (ed.dom.getAttrib(el,"data-sscr")!='' || ed.dom.getAttrib(el,"src").match(/data-sscr=/))) {
					sscr = decodeURIComponent(ed.dom.getAttrib(el,"data-sscr"));
					if (sscr == '') {
						sscr = ed.dom.getAttrib(el,"src");
						sscr = decodeURIComponent(sscr.replace(/.*data-sscr=(.*)/,"$1"));
					}
					isnew = false;
					elwidth = parseInt(ed.dom.getStyle(el,"width"));
					elheight = parseInt(ed.dom.getStyle(el,"height"));
					alignm = ed.dom.getStyle(el,"float");
					if (alignm == "none") {
						alignm = ed.dom.getStyle(el,"vertical-align");
					}
				} else {
					isnew = true;
					sscr = "-7.5,7.5,-5,5,1,1,1,1,1,300,200";
					elwidth = 300;
					elheight = 200;
					alignm = "middle";
				}
				
				ed.windowManager.open({
					file : url + '/asciisvgdlg.htm',
					width : 720 + parseInt(ed.getLang('asciisvg.delta_width', 0)),
					height : 465 + parseInt(ed.getLang('asciisvg.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					isnew : isnew, // Custom argument
					sscr : sscr,
					width : elwidth,
					height : elheight,
					alignm : alignm, 
					ascii_svg_server : ed.getParam('ascii_svg_server')
				});
			});

			// Register asciisvg button
			ed.addButton('asciisvg', {
				title : 'asciisvg.desc',
				cmd : 'mceAsciisvg',
				image : url + '/img/ed_asciisvg.gif'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('asciisvg', n.nodeName == 'IMG' && ed.dom.getAttrib(n,"data-sscr")!='');
			});
			
			
			ed.onPostProcess.add(function(ed,o) {
				if (o.get) {
					var imgs = o.content.match(/<img[^>]*data-sscr[^>]*>/gi);
					if (imgs != null) {
						for (var i=0; i<imgs.length; i++) {
							if (imgs[i].match(/data-sscr=\"/)) {
								sscr = decodeURIComponent(imgs[i].replace(/.*data-sscr=\"(.*?)\".*/,"$1"));
							} else {
								sscr = decodeURIComponent(imgs[i].replace(/.*data-sscr=(.*?)\s.*/,"$1"));
							}
							if (imgs[i].match(/style=\"/)) {
								style = imgs[i].replace(/.*style=\"(.*?)\".*/,"$1");
							} else {
								style = '';
							}
							style = style.replace('"', '');
							sscr = sscr.replace('"', '');
							rep = '<img class="ascii-svg" src="'+ed.getParam('ascii_svg_server')+'?sscr='+encodeURIComponent(sscr)+'" style="'+style+'" data-sscr="'+encodeURIComponent(sscr)+'" />';
							o.content = o.content.replace(imgs[i],rep);
						}
					}
				} 
			});
			
			ed.onBeforeSetContent.add(function(ed,o) {
				var imgs = o.content.match(/<embed[^>]*data-sscr[^>]*>/gi);
					if (imgs != null) {
						for (var i=0; i<imgs.length; i++) {
							//sscr = imgs[i].replace(/.*data-sscr=\"?(.*?)[\"\s].*/,"$1");
							if (imgs[i].match(/data-sscr=\"/)) {
								sscr = decodeURIComponent(imgs[i].replace(/.*data-sscr=\"(.*?)\".*/,"$1"));
							} else {
								sscr = decodeURIComponent(imgs[i].replace(/.*data-sscr=(.*?)\s.*/,"$1"));
							}
							if (imgs[i].match(/style=\"/)) {
								style = imgs[i].replace(/.*style=\"(.*?)\".*/,"$1");
							} else {
								style = '';
							}
							sscr = sscr.replace('"', '');
							style = style.replace('"', '');
							rep = '<img class="ascii-svg" src="'+ed.getParam('ascii_svg_server')+'?sscr='+encodeURIComponent(sscr)+'" style="'+style+'" data-sscr="'+encodeURIComponent(sscr)+'" />';
							o.content = o.content.replace(imgs[i],rep);
						}
					}
			});
			/*
			ed.onInit.add(function(ed) {
				ems = ed.dom.select('img');
				for (var i=0; i<ems.length; i++) {
					if (ems[i].getAttribute("data-sscr")!='') {
						var sscr = ems[i].getAttribute("data-sscr");
						n = ed.dom.create('img');
						ed.dom.setAttrib(n,"style",ed.dom.getAttrib(ems[i],"style"));
						ed.dom.setAttrib(n,"data-sscr", ems[i].getAttribute("data-sscr"));
						ed.dom.setAttrib(n,"src",ed.getParam('ascii_svg_server')+'?sscr='+encodeURIComponent( ems[i].getAttribute("data-sscr")));
						ed.dom.replace(n,ems[i]);
					}
				}
			});
			*/
			ed.onEvent.add(function(ed,e) {
				if (e.type=="mouseup") {
					el = ed.selection.getNode();
					if (el.nodeName == 'IMG' && ed.dom.getAttrib(el,"data-sscr")!='') {
						setTimeout(function() { t.processresize(ed,el)},50);
					}
				}
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Asciisvg plugin',
				author : 'David Lippman',
				authorurl : '',
				infourl : '',
				version : "1.0"
			};
		}, 
		
		processresize : function(ed,el) {
			var width = parseInt(ed.dom.getAttrib(el,'width') || 0);
			var height = parseInt(ed.dom.getAttrib(el,'height') || 0);
			var style = ed.dom.getAttrib(el,"style") || '';
			if(style != '' && width == 0 && height == 0)
			{
				height = parseInt(el.style.height || 0);
				width = parseInt(el.style.width || 0);
			}
			if (width>0 && height>0) {
				var sscra = decodeURIComponent(ed.dom.getAttrib(el,"data-sscr")).split(',');
				sscra[9] = width;
				sscra[10] = height;
				var sscr = sscra.join(',');
				ed.dom.setAttrib(el,"data-mce-data-sscr", sscr);
				ed.dom.setAttrib(el,"data-sscr", sscr);
				ed.dom.setAttrib(el,"src",ed.getParam('ascii_svg_server')+'?sscr='+encodeURIComponent(sscr));
			
				ed.dom.setStyle(el,"width",width+"px");
				ed.dom.setStyle(el,"height",height+"px");
			}
		}
	});

	// Register plugin
	tinymce.PluginManager.add('asciisvg', tinymce.plugins.AsciisvgPlugin);
})();