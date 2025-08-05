/**
 * Simple WebSocket Client untuk Notifikasi Realtime
 * Kompatibel dengan simple-websocket-server.php
 */

class SimpleNotificationClient {
    constructor(serverUrl = 'ws://localhost:8080') {
        this.serverUrl = serverUrl;
        this.websocket = null;
        this.userId = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000;
        this.isConnected = false;
        
        this.onConnected = null;
        this.onDisconnected = null;
        this.onNotification = null;
        this.onMessage = null;
        
        console.log('🚀 Simple Notification Client initialized');
    }

    connect(userId = null) {
        this.userId = userId;
        
        try {
            console.log(`🔗 Connecting to ${this.serverUrl}...`);
            this.websocket = new WebSocket(this.serverUrl);
            
            this.websocket.onopen = (event) => {
                this.isConnected = true;
                this.reconnectAttempts = 0;
                console.log('✅ WebSocket connected successfully');
                
                // Authenticate if userId provided
                if (this.userId) {
                    this.authenticate(this.userId);
                }
                
                if (this.onConnected) {
                    this.onConnected(event);
                }
            };

            this.websocket.onmessage = (event) => {
                this.handleMessage(event.data);
            };

            this.websocket.onclose = (event) => {
                this.isConnected = false;
                console.log('❌ WebSocket connection closed');
                
                if (this.onDisconnected) {
                    this.onDisconnected(event);
                }
                
                // Auto-reconnect
                this.attemptReconnect();
            };

            this.websocket.onerror = (error) => {
                console.error('❌ WebSocket error:', error);
                this.isConnected = false;
            };

        } catch (error) {
            console.error('❌ Failed to create WebSocket connection:', error);
        }
    }

    handleMessage(data) {
        try {
            const message = JSON.parse(data);
            console.log('📨 Received message:', message);

            switch (message.type) {
                case 'welcome':
                    console.log('👋 Welcome message:', message.message);
                    break;
                    
                case 'auth_success':
                    console.log('🔐 Authentication successful for user:', message.userId);
                    break;
                    
                case 'notification':
                case 'user_notification':
                    this.handleNotification(message);
                    break;
                    
                case 'pong':
                    console.log('🏓 Pong received');
                    break;
                    
                default:
                    if (this.onMessage) {
                        this.onMessage(message);
                    }
            }
        } catch (error) {
            console.error('❌ Error parsing message:', error);
        }
    }

    handleNotification(message) {
        console.log('🔔 Notification received:', message);
        
        const notification = message.notification || message;
        
        // Show browser notification if permission granted
        this.showBrowserNotification(notification);
        
        // Show in-app notification
        this.showInAppNotification(notification);
        
        // Call custom handler
        if (this.onNotification) {
            this.onNotification(notification);
        }
    }

    authenticate(userId) {
        if (this.isConnected) {
            const authMessage = {
                type: 'auth',
                userId: userId
            };
            this.send(authMessage);
        }
    }

    send(message) {
        if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
            this.websocket.send(JSON.stringify(message));
            return true;
        } else {
            console.warn('⚠️ WebSocket not connected, cannot send message');
            return false;
        }
    }

    ping() {
        return this.send({ type: 'ping' });
    }

    sendNotification(title, message) {
        return this.send({
            type: 'notification',
            title: title,
            message: message
        });
    }

    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`🔄 Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
            
            setTimeout(() => {
                this.connect(this.userId);
            }, this.reconnectDelay);
        } else {
            console.error('❌ Max reconnection attempts reached');
        }
    }

    showBrowserNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title || 'Notifikasi Baru', {
                body: notification.message || '',
                icon: '/assets/images/notification-icon.png',
                tag: 'hrd-notification'
            });
        }
    }

    showInAppNotification(notification) {
        // Create notification element
        const notificationEl = document.createElement('div');
        notificationEl.className = 'fixed top-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50 max-w-sm';
        notificationEl.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h0z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">${notification.title || 'Notifikasi'}</p>
                    <p class="text-xs text-blue-100 mt-1">${notification.message || ''}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-blue-200 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;

        document.body.appendChild(notificationEl);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notificationEl.parentElement) {
                notificationEl.remove();
            }
        }, 5000);
    }

    disconnect() {
        if (this.websocket) {
            this.websocket.close();
            this.websocket = null;
            this.isConnected = false;
        }
    }

    getStatus() {
        return {
            connected: this.isConnected,
            userId: this.userId,
            reconnectAttempts: this.reconnectAttempts
        };
    }
}

// Global instance
window.SimpleNotificationManager = {
    client: null,
    
    init: function(userId = null) {
        this.client = new SimpleNotificationClient();
        
        // Set event handlers
        this.client.onConnected = function(event) {
            console.log('🟢 Connected to notification server');
            document.dispatchEvent(new CustomEvent('websocket-connected'));
        };
        
        this.client.onDisconnected = function(event) {
            console.log('🔴 Disconnected from notification server');
            document.dispatchEvent(new CustomEvent('websocket-disconnected'));
        };
        
        this.client.onNotification = function(notification) {
            console.log('🔔 New notification:', notification);
            document.dispatchEvent(new CustomEvent('new-notification', { detail: notification }));
        };
        
        // Connect
        this.client.connect(userId);
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    },
    
    send: function(message) {
        return this.client ? this.client.send(message) : false;
    },
    
    ping: function() {
        return this.client ? this.client.ping() : false;
    },
    
    sendNotification: function(title, message) {
        return this.client ? this.client.sendNotification(title, message) : false;
    },
    
    getStatus: function() {
        return this.client ? this.client.getStatus() : { connected: false };
    }
};

console.log('📡 Simple Notification Client loaded');
