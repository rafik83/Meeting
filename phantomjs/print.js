var system = require('system');

var page = new WebPage();
var args = system.args;

page.paperSize = {
    format      : "A4",
    orientation : "portrait",
    margin      : { left: "1cm", right: "1cm", top: "1cm", bottom: "1cm" }
};

page.viewportSize = {
    width: 1200,
    height: 800
};

page.open(args[1], function() {
    page.render(args[2]);
    phantom.exit();
});
