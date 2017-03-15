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
    margin: {left: "1cm", right: "1cm", top: "1cm", bottom: "1cm"},
    header: {},
    footer: {}
};

page.viewportSize = {
    width: 1200,
    height: 600
};

page.open(address, function () {
    var isPhantomJSPrinting = page.evaluate(function () {
        return typeof PhantomJSPrinting == "object";
    });

    if (isPhantomJSPrinting) {
        var paperSize = page.paperSize;

        paperSize.header.height = page.evaluate(function () {
            return PhantomJSPrinting.header.height;
        });

        paperSize.header.contents = phantom.callback(function (pageNum, numPages) {
            return page.evaluate(function (pageNum, numPages) {
                return PhantomJSPrinting.header.contents(pageNum, numPages);
            }, pageNum, numPages);
        });

        paperSize.footer.height = page.evaluate(function () {
            return PhantomJSPrinting.footer.height;
        });

        paperSize.footer.contents = phantom.callback(function (pageNum, numPages) {
            return page.evaluate(function (pageNum, numPages) {
                return PhantomJSPrinting.footer.contents(pageNum, numPages);
            }, pageNum, numPages);
        });

        page.paperSize = paperSize;
    }

    window.setTimeout(function () {
        page.render(output);
        phantom.exit();
    }, 200);
});
