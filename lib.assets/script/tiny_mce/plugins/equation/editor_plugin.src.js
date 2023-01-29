(function() {
    tinymce.PluginManager.requireLangPack("equation");
    tinymce.create("tinymce.plugins.EquationPlugin", {
        init: function(a, b) {
            a.addCommand("mceEquation", function() {
                a.windowManager.open({
                    file: b + "/equation.htm",
                    width: 760 + parseInt(a.getLang("equation.delta_width", 0)),
                    height: 460 + parseInt(a.getLang("equation.delta_height", 0)),
                    inline: 1
                }, {
                    plugin_url: b,
                    some_custom_arg: "custom arg"
                })
            });
            a.addButton("equation", {
                title: "equation.desc",
                cmd: "mceEquation",
                image: b + "/img/equation.png"
            });
            a.onNodeChange.add(function(d, c, e) {
                let classListArr = e.getAttribute('class') || '';
                let classList = classListArr.split(' ');
                c.setActive("equation", classList.includes("equation-image"));
            })
        },
        createControl: function(b, a) {
            return null
        },
        getInfo: function() {
            return {
                longname: "Equation plugin",
                author: "Some author",
                authorurl: "http://tinymce.moxiecode.com",
                infourl: "http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/equation",
                version: "1.0"
            }
        }
    });
    tinymce.PluginManager.add("equation", tinymce.plugins.EquationPlugin)
})();