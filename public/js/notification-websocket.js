/**
 * WebSocket Client untuk Notifikasi Realtime
 * Sistem Dokumen HRD
 */

class NotificationWebSocket {
    constructor(serverUrl = 'ws://localhost:8080', userId = null) {
        this.serverUrl = serverUrl;
        this.userId = userId;
        this.socket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000;
        this.isConnected = false;
        this.messageQueue = [];
        
        // Event callbacks
        this.onNotification = null;
        this.onConnected = null;
        this.onDisconnected = null;
        this.onError = null;
        
        console.log('🔔 NotificationWebSocket initialized');
    }

    /**
     * Connect to WebSocket server
     */
    connect() {
        try {
            console.log(`🔌 Connecting to WebSocket server: ${this.serverUrl}`);
            
            this.socket = new WebSocket(this.serverUrl);
            
            this.socket.onopen = (event) => {
                console.log('✅ WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                
                // Authenticate user if userId provided
                if (this.userId) {
                    this.authenticate(this.userId);
                }
                
                // Process queued messages
                this.processMessageQueue();
                
                // Trigger connected callback
                if (this.onConnected) {
                    this.onConnected(event);
                }
            };
            
            this.socket.onmessage = (event) => {
                this.handleMessage(event.data);
            };
            
            this.socket.onclose = (event) => {
                console.log('❌ WebSocket disconnected');
                this.isConnected = false;
                
                // Trigger disconnected callback
                if (this.onDisconnected) {
                    this.onDisconnected(event);
                }
                
                // Attempt reconnection
                this.attemptReconnect();
            };
            
            this.socket.onerror = (error) => {
                console.error('❌ WebSocket error:', error);
                
                // Trigger error callback
                if (this.onError) {
                    this.onError(error);
                }
            };
            
        } catch (error) {
            console.error('❌ Failed to create WebSocket connection:', error);
        }
    }

    /**
     * Disconnect from WebSocket server
     */
    disconnect() {
        if (this.socket) {
            console.log('🔌 Disconnecting WebSocket');
            this.socket.close();
            this.socket = null;
            this.isConnected = false;
        }
    }

    /**
     * Authenticate user
     */
    authenticate(userId) {
        const authMessage = {
            type: 'auth',
            user_id: userId
        };
        
        this.sendMessage(authMessage);
        console.log(`🔐 Authenticating user: ${userId}`);
    }

    /**
     * Send message to server
     */
    sendMessage(message) {
        if (this.isConnected && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify(message));
        } else {
            // Queue message for later
            this.messageQueue.push(message);
            console.log('📝 Message queued (not connected)');
        }
    }

    /**
     * Process queued messages
     */
    processMessageQueue() {
        while (this.messageQueue.length > 0) {
            const message = this.messageQueue.shift();
            this.sendMessage(message);
        }
    }

    /**
     * Handle incoming messages
     */
    handleMessage(data) {
        try {
            const message = JSON.parse(data);
            
            switch (message.type) {
                case 'notification':
                    this.handleNotification(message.data);
                    break;
                    
                case 'auth_success':
                    console.log('✅ Authentication successful');
                    break;
                    
                case 'auth_error':
                    console.error('❌ Authentication failed:', message.message);
                    break;
                    
                case 'pong':
                    console.log('🏓 Pong received');
                    break;
                    
                default:
                    console.log('📨 Unknown message type:', message.type);
            }
            
        } catch (error) {
            console.error('❌ Failed to parse message:', error);
        }
    }

    /**
     * Handle notification
     */
    handleNotification(notification) {
        console.log('🔔 Notification received:', notification);
        
        // Show browser notification if permission granted
        this.showBrowserNotification(notification);
        
        // Show in-app notification
        this.showInAppNotification(notification);
        
        // Trigger custom callback
        if (this.onNotification) {
            this.onNotification(notification);
        }
    }

    /**
     * Show browser notification
     */
    showBrowserNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const browserNotification = new Notification(notification.title || 'Notifikasi Baru', {
                body: notification.message,
                icon: '/assets/images/notification-icon.png',
                tag: 'document-notification',
                requireInteraction: false
            });
            
            browserNotification.onclick = () => {
                window.focus();
                if (notification.url) {
                    window.location.href = notification.url;
                }
                browserNotification.close();
            };
            
            // Auto close after 5 seconds
            setTimeout(() => {
                browserNotification.close();
            }, 5000);
        }
    }

    /**
     * Show in-app notification
     */
    showInAppNotification(notification) {
        // Create notification element
        const notificationEl = document.createElement('div');
        notificationEl.className = 'fixed top-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50 max-w-sm';
        notificationEl.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h0z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">${notification.title || 'Notifikasi Baru'}</p>
                    <p class="text-sm opacity-90 mt-1">${notification.message}</p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        // Add click handler for navigation
        if (notification.url) {
            notificationEl.style.cursor = 'pointer';
            notificationEl.onclick = () => {
                window.location.href = notification.url;
            };
        }
        
        // Add to page
        document.body.appendChild(notificationEl);
        
        // Auto remove after 8 seconds
        setTimeout(() => {
            if (notificationEl.parentNode) {
                notificationEl.remove();
            }
        }, 8000);
        
        // Animate in
        setTimeout(() => {
            notificationEl.style.transform = 'translateX(0)';
        }, 100);
    }

    /**
     * Attempt reconnection
     */
    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`🔄 Attempting reconnection (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            setTimeout(() => {
                this.connect();
            }, this.reconnectDelay);
        } else {
            console.log('❌ Max reconnection attempts reached');
        }
    }

    /**
     * Send ping to keep connection alive
     */
    ping() {
        this.sendMessage({ type: 'ping' });
    }

    /**
     * Start ping interval
     */
    startPingInterval(interval = 30000) {
        setInterval(() => {
            if (this.isConnected) {
                this.ping();
            }
        }, interval);
    }

    /**
     * Request notification permission
     */
    static requestNotificationPermission() {
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    console.log('🔔 Notification permission:', permission);
                });
            }
        }
    }
}

// Global notification manager
window.NotificationManager = {
    websocket: null,
    
    init: function(userId = null) {
        // Request notification permission
        NotificationWebSocket.requestNotificationPermission();
        
        // Initialize WebSocket
        this.websocket = new NotificationWebSocket('ws://localhost:8080', userId);
        
        // Set up event handlers
        this.websocket.onConnected = () => {
            console.log('🔔 Notification system connected');
            this.updateConnectionStatus(true);
        };
        
        this.websocket.onDisconnected = () => {
            console.log('🔔 Notification system disconnected');
            this.updateConnectionStatus(false);
        };
        
        this.websocket.onNotification = (notification) => {
            console.log('🔔 New notification:', notification);
            // Update notification badge/counter if exists
            this.updateNotificationBadge();
        };
        
        // Connect
        this.websocket.connect();
        
        // Start ping interval
        this.websocket.startPingInterval();
    },
    
    updateConnectionStatus: function(connected) {
        const statusEl = document.getElementById('notification-status');
        if (statusEl) {
            statusEl.className = connected ? 'text-green-500' : 'text-red-500';
            statusEl.textContent = connected ? '🟢 Terhubung' : '🔴 Terputus';
        }
    },
    
    updateNotificationBadge: function() {
        const badgeEl = document.getElementById('notification-badge');
        if (badgeEl) {
            // You can implement badge counter logic here
            badgeEl.style.display = 'block';
        }
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Get user ID from session or data attribute
    const userId = document.body.dataset.userId || window.currentUserId || null;
    
    if (userId) {
        console.log('🔔 Auto-initializing notification system for user:', userId);
        window.NotificationManager.init(userId);
    } else {
        console.log('⚠️ No user ID found, notification system not initialized');
    }
});
