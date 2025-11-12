self.addEventListener("install", () => {
    self.skipWaiting();
});

self.addEventListener("push", (event) => {
    console.log("[Service Worker] Push Received.");
    if(!event.data) return;

    const { title, body, is_ri } = event.data.json();

    const options = {
        //data: "something you want to send within the notification, such an URL to open",
        body: body,
        icon: "icons/icon-256.png",
        vibrate: [200, 100, 200],
        //image: image,
        badge: "https://spyna.it/icons/favicon.ico",
        actions: [{ action: "Detail", title: "View", icon: "icons/icon-256.png" }]
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", (event) => {
    console.log("[Service Worker] Notification click Received.", event);

    const is_ri = false//event.data.json().is_ri;
    event.notification.close();
    event.waitUntil(openUrl("https://utils.bde-insa-lyon.fr/menu/" + (is_ri ? "" : "?rest=olivier")));
});

async function openUrl(url) {
    console.log("Opening URL " + url)
    const windowClients = await self.clients.matchAll({
        type: "window",
        includeUncontrolled: true,
    });
    for (let i = 0; i < windowClients.length; i++) {
        const client = windowClients[i];
        if (client.url === url && "focus" in client) {
            return client.focus();
        }
    }
    if (self.clients.openWindow) {
        return self.clients.openWindow(url);
    }
    return null;
}
