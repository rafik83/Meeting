var CHROME_EXTENSION_ID = 'alpphdcgnkkpafmlhllecaganiekhjcp';
var TokboxInstance = require('@opentok/client');
TokboxInstance.registerScreenSharingExtension('chrome', CHROME_EXTENSION_ID, 2);

module.exports.CHROME_EXTENSION_ID = CHROME_EXTENSION_ID;
module.exports.TokboxInstance = TokboxInstance;
