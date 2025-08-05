<?php

/**
 * Simple WebSocket Server untuk Notifikasi Realtime
 * Menggunakan ReactPHP Socket Server (sudah terinstall)
 * Jalankan dengan: php simple-websocket-server.php
 */

// Load composer autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die("Composer autoloader not found. Please run 'composer install' first.\n");
}

use React\EventLoop\Loop;
use React\Socket\SocketServer;

class SimpleNotificationServer
{
    private $loop;
    private $server;
    private $clients = [];
    private $userConnections = [];

    public function __construct()
    {
        $this->loop = Loop::get();
        echo "🚀 Simple Notification Server initialized\n";
    }

    public function start($port = 8080)
    {
        $this->server = new SocketServer("0.0.0.0:$port", [], $this->loop);
        
        echo "🌐 Simple WebSocket Server started on port $port\n";
        echo "📡 Waiting for connections...\n";
        echo "🔗 Connect via: ws://localhost:$port\n\n";

        $this->server->on('connection', function ($connection) {
            $this->handleConnection($connection);
        });

        $this->loop->run();
    }

    private function handleConnection($connection)
    {
        $clientId = uniqid('client_');
        $this->clients[$clientId] = $connection;
        
        echo "✅ New connection: $clientId\n";

        $connection->on('data', function ($data) use ($clientId, $connection) {
            $this->handleMessage($clientId, $data, $connection);
        });

        $connection->on('close', function () use ($clientId) {
            $this->handleDisconnection($clientId);
        });

        // Send welcome message
        $welcomeMessage = json_encode([
            'type' => 'welcome',
            'message' => 'Connected to Simple Notification Server',
            'clientId' => $clientId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        $connection->write($welcomeMessage . "\n");
    }

    private function handleMessage($clientId, $data, $connection)
    {
        $data = trim($data);
        echo "📨 Message from $clientId: $data\n";

        try {
            $message = json_decode($data, true);
            
            if ($message) {
                switch ($message['type'] ?? '') {
                    case 'auth':
                        $this->handleAuth($clientId, $message, $connection);
                        break;
                    case 'notification':
                        $this->broadcastNotification($message);
                        break;
                    case 'ping':
                        $this->sendPong($connection);
                        break;
                    default:
                        $this->broadcastMessage($clientId, $message);
                }
            }
        } catch (Exception $e) {
            echo "❌ Error processing message: " . $e->getMessage() . "\n";
        }
    }

    private function handleAuth($clientId, $message, $connection)
    {
        $userId = $message['userId'] ?? null;
        if ($userId) {
            $this->userConnections[$userId] = $clientId;
            echo "🔐 User $userId authenticated as $clientId\n";
            
            $response = json_encode([
                'type' => 'auth_success',
                'message' => 'Authentication successful',
                'userId' => $userId,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            $connection->write($response . "\n");
        }
    }

    private function sendPong($connection)
    {
        $pong = json_encode([
            'type' => 'pong',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        $connection->write($pong . "\n");
    }

    private function broadcastNotification($message)
    {
        $notification = json_encode([
            'type' => 'notification',
            'title' => $message['title'] ?? 'New Notification',
            'message' => $message['message'] ?? '',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $this->broadcast($notification);
        echo "📢 Notification broadcasted to " . count($this->clients) . " clients\n";
    }

    private function broadcastMessage($fromClientId, $message)
    {
        $broadcastMessage = json_encode([
            'type' => 'message',
            'from' => $fromClientId,
            'data' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $this->broadcast($broadcastMessage);
    }

    private function broadcast($message)
    {
        foreach ($this->clients as $clientId => $connection) {
            try {
                $connection->write($message . "\n");
            } catch (Exception $e) {
                echo "❌ Error sending to $clientId: " . $e->getMessage() . "\n";
                $this->handleDisconnection($clientId);
            }
        }
    }

    private function handleDisconnection($clientId)
    {
        unset($this->clients[$clientId]);
        
        // Remove from user connections
        foreach ($this->userConnections as $userId => $connectedClientId) {
            if ($connectedClientId === $clientId) {
                unset($this->userConnections[$userId]);
                break;
            }
        }
        
        echo "❌ Client disconnected: $clientId\n";
        echo "📊 Active connections: " . count($this->clients) . "\n";
    }

    public function sendNotificationToUser($userId, $notification)
    {
        if (isset($this->userConnections[$userId])) {
            $clientId = $this->userConnections[$userId];
            if (isset($this->clients[$clientId])) {
                $message = json_encode([
                    'type' => 'user_notification',
                    'notification' => $notification,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
                try {
                    $this->clients[$clientId]->write($message . "\n");
                    return true;
                } catch (Exception $e) {
                    echo "❌ Error sending to user $userId: " . $e->getMessage() . "\n";
                }
            }
        }
        return false;
    }
}

// Start server
try {
    $server = new SimpleNotificationServer();
    $server->start(8080);
} catch (Exception $e) {
    echo "❌ Server error: " . $e->getMessage() . "\n";
    exit(1);
}
