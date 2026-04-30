import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

console.log('🔧 Echo Configuration:', {
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    host: import.meta.env.VITE_PUSHER_HOST,
    scheme: import.meta.env.VITE_PUSHER_SCHEME,
});

// Create Echo instance
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: import.meta.env.VITE_PUSHER_HOST || `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT || 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT || 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME || 'https') === 'https',
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
    },
});

// Add event listeners for debugging
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Pusher connected!');
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ Pusher error:', err);
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.warn('⚠️  Pusher disconnected');
});

console.log('✅ Echo initialized and listening for connections');
