var CHROME_EXTENSION_ID = 'alpphdcgnkkpafmlhllecaganiekhjcp';
var CHROME_EXTENSION_URL = 'https://chrome.google.com/webstore/detail/' + CHROME_EXTENSION_ID;
var TokboxInstance = require('@opentok/client');
TokboxInstance.registerScreenSharingExtension('chrome', CHROME_EXTENSION_ID, 2);

module.exports.CHROME_EXTENSION_ID = CHROME_EXTENSION_ID;
module.exports.CHROME_EXTENSION_URL = CHROME_EXTENSION_URL;
module.exports.TokboxInstance = TokboxInstance;
