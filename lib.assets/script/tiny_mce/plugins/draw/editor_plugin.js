(function() {
    tinymce.PluginManager.requireLangPack("draw");
    tinymce.create("tinymce.plugins.DrawPlugin", {
        init: function(a, b) {
            a.addCommand("mceDraw", function() {
                a.windowManager.open({
                    file: b + "/draw.htm",
                    width: 840 + parseInt(a.getLang("draw.delta_width", 0)),
                    height: 600 + parseInt(a.getLang("draw.delta_height", 0)),
                    inline: 1
                }, {
                    plugin_url: b,
                    some_custom_arg: "custom arg"
                })
            });
            a.addButton("draw", {
                title: "draw.desc",
                cmd: "mceDraw",
                image: b + "/img/draw.png"
            });
            a.onNodeChange.add(function(d, c, e) {
                let classListArr = e.getAttribute('class') || '';
                let classList = classListArr.split(' ');
                c.setActive("draw", classList.includes("draw-image"));
            })
        },
        createControl: function(b, a) {
            return null
        },
        getInfo: function() {
            return {
                longname: "Drawing plugin",
                author: "Some author",
                authorurl: "http://tinymce.moxiecode.com",
                infourl: "http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/draw",
                version: "1.0"
            }
        }
    });
    tinymce.PluginManager.add("draw", tinymce.plugins.DrawPlugin)
})();