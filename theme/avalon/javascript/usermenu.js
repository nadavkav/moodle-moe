YUI().use("node-base","node-event-simulate", function(Y) {

    Y.use('node-menunav', function(Y) {
        var menus = Y.all('.region-content .profilepicture .useractionmenu');

        menus.each(function(menu) {
            Y.on("contentready", function() {
                this.plug(Y.Plugin.NodeMenuNav, {autoSubmenuDisplay: true});
                var submenus = this.all('.yui3-loading');
                submenus.each(function (n) {
                    n.removeClass('yui3-loading');
                });

            }, "#" + menu.getAttribute('id'));
        });
    });

});
