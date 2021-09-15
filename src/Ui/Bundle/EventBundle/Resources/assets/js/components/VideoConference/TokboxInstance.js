const CHROME_EXTENSION_ID = 'alpphdcgnkkpafmlhllecaganiekhjcp';
const CHROME_EXTENSION_URL = 'https://chrome.google.com/webstore/detail/' + CHROME_EXTENSION_ID;
import TokboxInstance from '@opentok/client';
TokboxInstance.registerScreenSharingExtension('chrome', CHROME_EXTENSION_ID, 2);

export {CHROME_EXTENSION_ID, CHROME_EXTENSION_URL, TokboxInstance};
