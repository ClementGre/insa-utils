const pushServerPublicKey = "BMxpl8rDvkKFL3TV2Y3_ItgmCZST7lAnp2nFSQvUCwszLUhXdAgucM6fIAGvAZF89a0lTwH-ktyBTWqL21D2ta4";

function isPushNotificationSupported() {
    return "serviceWorker" in navigator && "PushManager" in window && "Notification" in window;
}

export function initializePushNotifications(form_data) {
    if(!isPushNotificationSupported()){
        alert("Ce navigateur ne supporte pas les notifications.");
    }else if(Notification.permission === "granted"){
        console.log("Notifications already granted")
        registerServiceWorker(form_data)
    }else if(Notification.permission !== "denied"){
        Notification.requestPermission((permission) => {
            if(permission === "granted"){
                registerServiceWorker(form_data)
            }
        });
    }
}

function sendNotification() {
    //const img = "/images/jason-leung-HM6TMmevbZQ-unsplash.jpg";
    const text = "Take a look at this brand new t-shirt!";
    const title = "New Product Available";
    const options = {
        body: text,
        //icon: "/images/jason-leung-HM6TMmevbZQ-unsplash.jpg",
        vibrate: [200, 100, 200],
        tag: "new-product",
        //image: img,
        //badge: "https://spyna.it/icons/android-icon-192x192.png",
        actions: [{ action: "Detail", title: "View", icon: "https://via.placeholder.com/128/ff0000" }]
    };
    navigator.serviceWorker.ready.then(function(serviceWorker) {
        serviceWorker.showNotification(title, options);
    });
}

function registerServiceWorker(form_data) {
    navigator.serviceWorker.register("sw.js").then(function(swRegistration) {
        subscribeUser(form_data);
    });
}

function subscribeUser(form_data) {
    navigator.serviceWorker.ready.then(function(serviceWorker) {
        return serviceWorker.pushManager
            .subscribe({
                userVisibleOnly: true,
                applicationServerKey: pushServerPublicKey
            })
            .then(function(subscription) {
                console.log("User is subscribed.", subscription);
                return subscription;
            });
    }).then((subscription) => {
        const request = fetch("subscribe.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({
                'csrf_js': getCsrfToken(),
                'form_data': form_data,
                'subscription': subscription,
            })
        })
        request.then((response) => response.json())
            .then((data) => {
                console.log("Subscription response", data)
                if(data['status'] === 'done'){
                    alert("Vous recevrez une notification lorsque le menu sera disponible.")
                    return;
                }
                if(data['status'] === 'error' && data['error'] === 'invalid_csrf'){
                    alert("Le formulaire a expiré, veuillez réessayer.")
                    location.reload()
                    return;
                }
                alert("Impossible de vous enregistrer auprès du serveur.")
            });
    });
}
