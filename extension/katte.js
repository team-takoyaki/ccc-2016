chrome.runtime.sendMessage({method: 'user_hash'}, function (response) {
    console.log(response);
    if (response.user_hash) {
        $("#not_login_layout").show();
        $("#login_layout").show();
    }
});
