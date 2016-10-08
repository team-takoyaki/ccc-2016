chrome.runtime.sendMessage({method: 'user_hash'}, function (response) {
    console.log('hash: ' + response);
    if (response.user_hash) {
        $("#user_hash").val(response.user_hash);
        $("#not_login_layout").hide();
        $("#login_layout").show();
    }
});
