<?php

if (!defined('ABSPATH')) {
    exit;
}


class ContloUtils
{
    public static function isWooCommercePluginActivated()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }


    public static function createServiceWorker()
    {
        $js_file = 'service_worker.js';

        if (isset($_GET['filename'])) {
            $js_file = sanitize_file_name($_GET['filename']);
        }

        $js_dir = plugin_dir_path(__FILE__) . 'apps/contlo/';

        if (!file_exists($js_dir . $js_file)) {
            $js_content = "'use strict';


            async function postData(url = '', data = {}) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                return response.json(); // parses JSON response into native JavaScript objects
            }
            
            self.addEventListener('install', function (event) {
                event.waitUntil(self.skipWaiting()); //will install the service worker
            });
            
            self.addEventListener('activate', function (event) {
                event.waitUntil(self.clients.claim()); //will activate the serviceworker
            });
            
            // Register event listener for the 'notificationclick' event.
            self.addEventListener('notificationclick', function (event) {
                const clickedNotification = event.notification;
                event.notification.close();
                var redirectUrl = null;
                if (event.notification.data) {
                    redirectUrl = event.notification.data ? event.notification.data.url : null
                }
            
                if (event.action.includes('action0')) {
                    redirectUrl = event.notification.data ? event.notification.data.actions[0].link : null
                } else if (event.action.includes('action1')) {
                    redirectUrl = event.notification.data ? event.notification.data.actions[1].link : null
                }
            
                console.log('notificationclick url1:' + redirectUrl)
                event.waitUntil(
                    clients.matchAll({
                        type: 'window'
                    }).then(function (clientList) {
                        if (clients.openWindow) {
                            return clients.openWindow(redirectUrl);
                        }
                    })
                );
                sendClickEvent(event.notification.data)
            });
            
            self.addEventListener('notificationclose', function (event) {
                if (event.notification.data) {
                    let payload = event.notification.data
                    postData(payload.close_acknowledge_url, {short_url: payload.url})
                        .then(data => {
                            console.log('close_acknowledge_url success');
                        });
                }
            });
            
            
            function sendClickEvent(payload) {
                postData(payload.click_acknowledge_url, {short_url: payload.url})
                    .then(data => {
                        console.log('click_acknowledge_url success');
                    });
            }
            
            function sendViewEvent(payload) {
                postData(payload.open_acknowledge_url, {short_url: payload.url})
                    .then(data => {
                        console.log('open_acknowledge_url success');
                    });
            }
            
            self.addEventListener('push', function (event) {
                var payload = event.data ? JSON.parse(event.data.text()) : {};
                console.log('event_data', payload);
            
                let actions = []
                if (payload.actions) {
                    for (let i = 0; i < payload.actions.length; i++) {
                        if(payload.actions[i].title !== '' && payload.actions[i].title !== null && typeof payload.actions[i].title !== 'undefined') {
                            actions.push({action: 'action' + i, title: payload.actions[i].title})
                        }
                    }
                }
            
                sendViewEvent(payload)
                let showNotification = self.registration.showNotification(payload.title, {
                    title: payload.title,
                    body: payload.body,
                    icon: payload.icon,
                    url: payload.url,
                    actions: actions,
                    image: payload.image,
                    tag: payload.url + payload.body + payload.icon + payload.title,
                    data: payload,
                    requireInteraction: true
                });
                event.waitUntil(showNotification);
            });";

            file_put_contents($js_dir . $js_file, $js_content);
            ContloLogger::info("successfully created service worker");
        }
    }
}

?>