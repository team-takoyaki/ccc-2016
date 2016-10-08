var request = require('request');

request.post({
    url     : "https://fcm.googleapis.com/fcm/send",
    headers : {
        'Content-Type': 'application/json',
        Authorization: "key=AIzaSyAIcfdASSRX96kR8njsk3POc1GOxc2rb9k"
    },
    json: true,
    body    : {
        data: {
            title: "hello",
            message: "world"
        },
        to: "APA91bF6Y90SFHW2GHjeOY6vkw9ZA5sbmizyqIfa7gn2OMGLtsLwftsqyKB-VTlha69y9XHrbhw4qfpOg6pr8BiXXVnWZD1C8CvipwarFxn8U_LElpsPH6WkGkwUAJRFeFwCUuXdAzaxKs9sMGjdbDi89pR2JlKgBw"
    }
}, function(err, response, body){
    console.log(body);
});
