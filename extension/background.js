const PROJECT_ID = '814287454709';
const NOTIFICATION_ID = 'NOTIFICATION_ID';

var itemId = 0;

console.log('user_hash: ' + localStorage.getItem('USER_HASH'));

var ButtonIndex = {
    OPEN: 0,
    CLOSE: 1
}

var registerUserToServer = function(name, grade, registrationId, sendResponse) {
    $.get("http://katte.party/api_regist",
          {name: name, grade: grade, regist_id: registrationId},
          function(response) {
              console.log(response);
              if (response.code == 200) {
                  console.log('success');
                  sendResponse({code: response.code, hash: response.body.hash});
              } else {
                  console.log('failure');
                  sendResponse({code: response.code});
              }
          });
}

chrome.runtime.onMessage.addListener(
    function(request, sender, sendResponse) {
        if (request.method == 'user_hash') {
            sendResponse({user_hash: localStorage.getItem('USER_HASH')});
            return;
        }
        if (request.name == null || request.grade == null) {
            sendResponse({code: 400});
            return;
        }
        if (window.localStorage.getItem('registrationId')) {
            sendResponse({code: 400});
            return
        }
        chrome.gcm.register([PROJECT_ID], function(registrationId) {
            console.log('name: ' + name + ', gcm registrationId: ' + registrationId);
            registerUserToServer(request.name, request.grade, registrationId, sendResponse);
        });
        return true;
    }
);

chrome.gcm.onMessage.addListener(function(message) {
    chrome.notifications.clear(NOTIFICATION_ID);

    itemId = message.data.item_id;

    chrome.notifications.create(NOTIFICATION_ID, {
        title: message.data.title,
        message: message.data.message,
        type: 'basic',
        iconUrl: 'icon.png',
        buttons: [{
            title: "購入する",
        }]
    });
});

chrome.notifications.onButtonClicked.addListener(function(notificationId, buttonIndex) {
    if (NOTIFICATION_ID == notificationId) {
        if (ButtonIndex.OPEN == buttonIndex) {
            window.open('http://katte.party/#modal-' + itemId);
        }
        chrome.notifications.clear(notificationId);
    }
});
