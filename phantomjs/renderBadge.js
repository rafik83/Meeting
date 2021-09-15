"use strict";

var system = require('system');
var page = require('webpage').create();
var args = system.args;

var address = args[1];
var output = args[2];
var httpUser = args[3];
var httpPassword = args[4];

page.settings.userName = httpUser;
page.settings.password = httpPassword;

page.paperSize = {
    format: "A4",
    orientation: "portrait",
    margin: {left: "0cm", right: "0cm", top: "0cm", bottom: "0cm"}
};

page.viewportSize = {
    width: 1920,
    height: 1080
};

page.open(address, function () {
    window.setTimeout(function () {
        page.render(output);
        phantom.exit();
    }, 200);
});
