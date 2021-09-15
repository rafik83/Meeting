var session = ['screen', 'window'];

function getSourceID(sender, callback) {
  // requires Chrome 40+
  var tab = sender.tab;
  tab.url = sender.url;
  chrome.desktopCapture.chooseDesktopMedia(session, tab, function(sourceId) {
    // "sourceId" will be empty if permission is denied
    if (!sourceId || !sourceId.length) {
      callback({ error: 'permissionDenied' });
      return;
    }

    callback({ sourceId: sourceId });
  });
}

chrome.runtime.onMessageExternal.addListener(function (message, sender, callback) {
  if (message.type === 'getSourceId') {
    getSourceID(sender, callback);
    return true;
  } else if (message.type === 'isInstalled') {
    callback(true);
  } else if (message.type === 'apiVersion') {
    callback(2);
  } else {
    callback({ error: 'unsupportedMessage', type: message.type });
  }
});
