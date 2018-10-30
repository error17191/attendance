// import firebase from 'firebase/app';
//
// require('firebase/messaging');
//
// var config = {
//     apiKey: "AIzaSyBEqP7_rgdd23STADNVz8vMafo3e1eKfRM",
//     authDomain: "attendance-16307.firebaseapp.com",
//     databaseURL: "https://attendance-16307.firebaseio.com",
//     projectId: "attendance-16307",
//     storageBucket: "attendance-16307.appspot.com",
//     messagingSenderId: "480654102977"
// };
//
// firebase.initializeApp(config);
//
// // Retrieve Firebase Messaging object.
// const messaging = firebase.messaging();
//
// messaging.usePublicVapidKey("BP-G7rFXqXNDHVsZjYwt_Fc_E5JJVJX-EdelNULHfnMNZpXM300jehcuPaTuWeP3yXmim0n_VxYs7vB3cqqgI3Q");
//
// messaging.requestPermission().then(function() {
//     console.log('Notification permission granted.');
//     // TODO(developer): Retrieve an Instance ID token for use with FCM.
//     // ...
//     messaging.getToken().then(function(currentToken) {
//         if (currentToken) {
//             axios.post('/browser/token',{token: currentToken})
//                 .then((response) => {
//                     console.log(response.data.status);
//                 });
//         }
//     });
//
// }).catch(function(err) {
//     console.log('Unable to get permission to notify.', err);
// });
//
// messaging.onMessage(function(payload) {
//     console.log('Message received. ', payload);
// });
//
