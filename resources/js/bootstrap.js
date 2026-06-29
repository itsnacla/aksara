import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import Echo from "laravel-echo";

import Pusher from "pusher-js";
window.Pusher = Pusher;

const broadcaster = document.querySelector('meta[name="broadcaster"]')?.getAttribute('content') || import.meta.env.VITE_BROADCAST_CONNECTION || 'reverb';

function createMockEcho() {
    const mockChannel = {
        listen: () => mockChannel,
        notification: () => mockChannel,
        listenToAll: () => mockChannel,
        whisper: () => mockChannel,
    };

    window.Echo = {
        private: () => mockChannel,
        channel: () => mockChannel,
        join: () => mockChannel,
        leave: () => {},
        socketId: () => '',
        options: {}
    };
}

if (broadcaster === 'reverb') {
    const reverbKey = document.querySelector('meta[name="reverb-key"]')?.getAttribute('content') || import.meta.env.VITE_REVERB_APP_KEY;
    const reverbHost = document.querySelector('meta[name="reverb-host"]')?.getAttribute('content') || import.meta.env.VITE_REVERB_HOST;
    const reverbPort = document.querySelector('meta[name="reverb-port"]')?.getAttribute('content') || import.meta.env.VITE_REVERB_PORT;
    const reverbScheme = document.querySelector('meta[name="reverb-scheme"]')?.getAttribute('content') || import.meta.env.VITE_REVERB_SCHEME;

    if (reverbHost && reverbHost !== 'null' && reverbHost !== '') {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost === 'localhost' || reverbHost === '127.0.0.1' ? window.location.hostname : reverbHost,
            wsPort: reverbPort ?? 80,
            wssPort: reverbPort ?? 443,
            forceTLS: reverbScheme === 'https' || window.location.protocol === 'https:',
            enabledTransports: ['ws', 'wss'],
        });
    } else {
        createMockEcho();
    }
} else if (broadcaster === 'pusher') {
    const pusherKey = document.querySelector('meta[name="pusher-key"]')?.getAttribute('content') || import.meta.env.VITE_PUSHER_APP_KEY;
    const pusherCluster = document.querySelector('meta[name="pusher-cluster"]')?.getAttribute('content') || import.meta.env.VITE_PUSHER_APP_CLUSTER;

    if (pusherKey && pusherKey !== 'null' && pusherKey !== '') {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: true
        });
    } else {
        createMockEcho();
    }
} else {
    createMockEcho();
}
