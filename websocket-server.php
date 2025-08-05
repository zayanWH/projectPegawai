<?php

/**
 * WebSocket Server untuk Notifikasi Realtime
 * Jalankan dengan: php websocket-server.php
 */

// Load composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die("Composer autoloader not found. Please run 'composer install' first.\n");
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// WebSocket server runs independently without CodeIgniter bootstrap
// Database access will be handled through HTTP API calls to the main application

class NotificationServer implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        echo "🚀 Notification WebSocket Server initialized\n";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection
        $this->clients->attach($conn);
        
        echo "📱 New connection! ({$conn->resourceId})\n";
        echo "👥 Total connections: " . count($this->clients) . "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $data = json_decode($msg, true);
            
            if (!$data) {
                $from->send(json_encode(['error' => 'Invalid JSON']));
                return;
            }

            switch ($data['type'] ?? '') {
                case 'auth':
                    $this->handleAuth($from, $data);
                    break;
                    
                case 'ping':
                    $from->send(json_encode(['type' => 'pong', 'timestamp' => time()]));
                    break;
                    
                default:
                    echo "❓ Unknown message type: " . ($data['type'] ?? 'none') . "\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error processing message: " . $e->getMessage() . "\n";
            $from->send(json_encode(['error' => 'Message processing failed']));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // Remove connection
        $this->clients->detach($conn);
        
        // Remove from user connections
        foreach ($this->userConnections as $userId => $connections) {
            if (($key = array_search($conn, $connections, true)) !== false) {
                unset($this->userConnections[$userId][$key]);
                if (empty($this->userConnections[$userId])) {
                    unset($this->userConnections[$userId]);
                }
                echo "👤 User {$userId} disconnected\n";
                break;
            }
        }
        
        echo "📱 Connection {$conn->resourceId} has disconnected\n";
        echo "👥 Total connections: " . count($this->clients) . "\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "❌ An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Handle user authentication
     */
    private function handleAuth(ConnectionInterface $conn, $data)
    {
        $userId = $data['user_id'] ?? null;
        
        if ($userId) {
            // Store user connection
            if (!isset($this->userConnections[$userId])) {
                $this->userConnections[$userId] = [];
            }
            $this->userConnections[$userId][] = $conn;
            
            echo "✅ User {$userId} authenticated (Connection: {$conn->resourceId})\n";
            
            // Send authentication success
            $conn->send(json_encode([
                'type' => 'auth_success',
                'user_id' => $userId,
                'message' => 'Successfully authenticated'
            ]));
        } else {
            $conn->send(json_encode([
                'type' => 'auth_error',
                'message' => 'User ID required'
            ]));
        }
    }

    /**
     * Broadcast notification to all clients
     */
    public function broadcastNotification($notification)
    {
        $message = json_encode([
            'type' => 'notification',
            'data' => $notification,
            'timestamp' => time()
        ]);

        $sentCount = 0;
        foreach ($this->clients as $client) {
            try {
                $client->send($message);
                $sentCount++;
            } catch (Exception $e) {
                echo "❌ Failed to send to client: " . $e->getMessage() . "\n";
            }
        }

        echo "📢 Notification broadcasted to {$sentCount} clients\n";
        return $sentCount;
    }

    /**
     * Send notification to specific user
     */
    public function sendToUser($userId, $notification)
    {
        if (!isset($this->userConnections[$userId])) {
            echo "👤 User {$userId} not connected\n";
            return 0;
        }

        $message = json_encode([
            'type' => 'notification',
            'data' => $notification,
            'timestamp' => time()
        ]);

        $sentCount = 0;
        foreach ($this->userConnections[$userId] as $conn) {
            try {
                $conn->send($message);
                $sentCount++;
            } catch (Exception $e) {
                echo "❌ Failed to send to user {$userId}: " . $e->getMessage() . "\n";
            }
        }

        echo "📤 Notification sent to user {$userId} ({$sentCount} connections)\n";
        return $sentCount;
    }
}

// Start server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new NotificationServer()
        )
    ),
    8080
);

echo "🌐 WebSocket Server started on port 8080\n";
echo "📡 Waiting for connections...\n";
echo "🛑 Press Ctrl+C to stop\n\n";

$server->run();
