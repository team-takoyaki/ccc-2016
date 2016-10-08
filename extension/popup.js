const KEY_USER_HASH = "USER_HASH"

var showLoading = function() {
    $("#register_user_layout").hide();
    $("#button_register").hide();
    $("#loading").show();
}

var dismissLoading = function() {
    $("#register_user_layout").show();
    $("#button_register").show();
    $("#loading").hide();
}

var showRegisterUserFailure = function() {
    $("#register_user_failure").show();
}

var registerUser = function(name, grade) {
    chrome.runtime.sendMessage({
        name: name,
        grade: grade
    }, function(response) {
        dismissLoading();
        if (response.code == 200) {
            console.log("success");
            localStorage.setItem(KEY_USER_HASH, response.hash);
            $("#already_register_user").show()
            $("#register_user_layout").hide();
            window.open('http://katte.party/');
        } else {
            dismissLoading();
            console.log("failure");
        }
    });
}

$("#button_register").click(function(event) {
    var name = $("#input_name").val();
    var grade = $("#input_grade").val();
    console.log("name: " + name + ", grade: " + grade);
    if (name.length == 0) {
        showRegisterUserFailure();
        return
    }
    showLoading();
    registerUser(name, grade);
});

$('#button_open_katte').click(function(event) {
    console.log('open');
    window.open('http://katte.party/');
});

$('#button_logout').click(function(event) {
    localStorage.removeItem(KEY_USER_HASH);
    $("#already_register_user").hide();
    $("#register_user_layout").show();
    window.open('http://katte.party');
});

var userHash = localStorage.getItem(KEY_USER_HASH);
if (userHash) {
    $("#already_register_user").show()
    $("#register_user_layout").hide();
} else {
    $("#already_register_user").hide()
    $("#register_user_layout").show();
}
