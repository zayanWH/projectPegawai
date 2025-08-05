<?php

namespace App\Services;

use React\Socket\SocketServer;
use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\Stream\WritableResourceStream;

class WebSocketService
{
    private $clients = [];
    private $loop;
    private $server;
    
    public function __construct()
    {
        $this->loop = \React\EventLoop\Factory::create();
    }

    /**
     * Start WebSocket server
     */
    public function startServer($port = 8080)
    {
        $socket = new SocketServer($port, $this->loop);
        
        $this->server = new HttpServer($this->loop, function (ServerRequestInterface $request) {
            // Handle WebSocket upgrade
            if ($request->getHeaderLine('Upgrade') === 'websocket') {
                return $this->handleWebSocketUpgrade($request);
            }
            
            // Handle regular HTTP requests
            return new Response(200, ['Content-Type' => 'text/plain'], 'WebSocket Server Running');
        });

        $this->server->listen($socket);
        
        echo "WebSocket server started on port {$port}\n";
        $this->loop->run();
    }

    /**
     * Handle WebSocket upgrade
     */
    private function handleWebSocketUpgrade(ServerRequestInterface $request)
    {
        $key = $request->getHeaderLine('Sec-WebSocket-Key');
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        
        return new Response(101, [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $acceptKey,
        ]);
    }

    /**
     * Add client connection
     */
    public function addClient($connection, $userId = null)
    {
        $clientId = uniqid();
        $this->clients[$clientId] = [
            'connection' => $connection,
            'user_id' => $userId,
            'connected_at' => time()
        ];
        
        echo "Client {$clientId} connected" . ($userId ? " (User ID: {$userId})" : "") . "\n";
        
        return $clientId;
    }

    /**
     * Remove client connection
     */
    public function removeClient($clientId)
    {
        if (isset($this->clients[$clientId])) {
            unset($this->clients[$clientId]);
            echo "Client {$clientId} disconnected\n";
        }
    }

    /**
     * Send notification to specific user
     */
    public function sendNotificationToUser($userId, $notification)
    {
        $message = json_encode([
            'type' => 'notification',
            'data' => $notification,
            'timestamp' => time()
        ]);

        $sent = 0;
        foreach ($this->clients as $clientId => $client) {
            if ($client['user_id'] == $userId) {
                $this->sendMessage($clientId, $message);
                $sent++;
            }
        }
        
        echo "Notification sent to {$sent} client(s) for user {$userId}\n";
        return $sent;
    }

    /**
     * Broadcast notification to all connected clients
     */
    public function broadcastNotification($notification)
    {
        $message = json_encode([
            'type' => 'notification',
            'data' => $notification,
            'timestamp' => time()
        ]);

        $sent = 0;
        foreach ($this->clients as $clientId => $client) {
            $this->sendMessage($clientId, $message);
            $sent++;
        }
        
        echo "Notification broadcasted to {$sent} client(s)\n";
        return $sent;
    }

    /**
     * Send message to specific client
     */
    private function sendMessage($clientId, $message)
    {
        if (isset($this->clients[$clientId])) {
            try {
                $connection = $this->clients[$clientId]['connection'];
                $connection->write($message);
                return true;
            } catch (\Exception $e) {
                echo "Error sending message to client {$clientId}: " . $e->getMessage() . "\n";
                $this->removeClient($clientId);
                return false;
            }
        }
        return false;
    }

    /**
     * Get connected clients count
     */
    public function getClientsCount()
    {
        return count($this->clients);
    }

    /**
     * Get clients info
     */
    public function getClientsInfo()
    {
        $info = [];
        foreach ($this->clients as $clientId => $client) {
            $info[] = [
                'client_id' => $clientId,
                'user_id' => $client['user_id'],
                'connected_at' => date('Y-m-d H:i:s', $client['connected_at'])
            ];
        }
        return $info;
    }
}
