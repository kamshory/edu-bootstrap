(function() {
    tinymce.PluginManager.requireLangPack("latex");
    tinymce.create("tinymce.plugins.LatexPlugin", {
        init: function(a, b) {
            a.addCommand("mceLatex", function() {
                a.windowManager.open({
                    file: b + "/latex.htm",
                    width: 600 + parseInt(a.getLang("latex.delta_width", 0)),
                    height: 334 + parseInt(a.getLang("latex.delta_height", 0)),
                    inline: 1
                }, {
                    plugin_url: b,
                    some_custom_arg: "custom arg"
                })
            });
            a.addButton("latex", {
                title: "latex.desc",
                cmd: "mceLatex",
                image: b + "/img/latex.png"
            });
            a.onNodeChange.add(function(d, c, e) {
                let classListArr = e.getAttribute('class') || '';
                let classList = classListArr.split(' ');
                c.setActive("latex", classList.includes("latex-image"));
            })
        },
        createControl: function(b, a) {
            return null
        },
        getInfo: function() {
            return {
                longname: "Latex plugin",
                author: "Some author",
                authorurl: "http://tinymce.moxiecode.com",
                infourl: "http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/latex",
                version: "1.0"
            }
        }
    });
    tinymce.PluginManager.add("latex", tinymce.plugins.LatexPlugin)
})();