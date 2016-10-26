var system = require('system');
var page   = require('webpage').create();
var args   = system.args;

page.settings.userName = "phantomjs";
page.settings.password = args[3];

page.paperSize = {
    format      : "A4",
    orientation : "portrait",
    margin      : { left: "1cm", right: "1cm", top: "1cm", bottom: "1cm" }
};

page.viewportSize = {
    width:  1200,
    height: 600
};

page.open(args[1], function() {
    page.render(args[2]);
    phantom.exit();
});
