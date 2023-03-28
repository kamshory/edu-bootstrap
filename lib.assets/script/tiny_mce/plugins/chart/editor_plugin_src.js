(function() {
    tinymce.PluginManager.requireLangPack("chart");
    tinymce.create("tinymce.plugins.ChartPlugin", {
        init: function(a, b) {
            a.addCommand("mceChart", function() {
                a.windowManager.open({
					file : b + '/chart.htm',
					width : 700 + parseInt(a.getLang('chart.delta_width', 0)),
					height : 460 + parseInt(a.getLang('chart.delta_height', 0)),
					inline : 1
                }, {
					plugin_url : b, // Plugin absolute URL
					some_custom_arg : '' // Custom argument
                })
            });
            a.addButton("chart", {
                title: "chart.desc",
                cmd: "mceChart",
                image: b + "/img/chart.gif"
            });
            a.onNodeChange.add(function(d, c, e) {
                c.setActive("chart", e.getAttribute('class')=="chart-image");
            })
        },
        createControl: function(b, a) {
            return null
        },
        getInfo: function() {
            return {
                longname: "Chart plugin",
                author: "Kamshory",
                authorurl: "https://www.planetbiru.com",
                infourl: "https://www.planetbiru.com/kamshory",
                version: "1.0"
            }
        }
    });
    tinymce.PluginManager.add("chart", tinymce.plugins.ChartPlugin)
})();

