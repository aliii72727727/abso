<?php
// firebase.php - Simple JSON-based database
class FirebaseDB {
    private $serversFile = 'servers_data.json';
    
    public function __construct() {
        if (!file_exists($this->serversFile)) {
            file_put_contents($this->serversFile, json_encode([]));
        }
    }
    
    public function getServers() {
        try {
            $data = file_get_contents($this->serversFile);
            return json_decode($data, true) ?: [];
        } catch (Exception $e) {
            error_log('getServers error: ' . $e->getMessage());
            return [];
        }
    }
    
    public function addServer($serverData) {
        try {
            $servers = $this->getServers();
            $servers[] = $serverData;
            return file_put_contents($this->serversFile, json_encode($servers, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            error_log('addServer error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function deleteServer($serverId) {
        try {
            $servers = $this->getServers();
            $newServers = array_filter($servers, function($server) use ($serverId) {
                return $server['id'] != $serverId;
            });
            return file_put_contents($this->serversFile, json_encode(array_values($newServers), JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            error_log('deleteServer error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function generateId() {
        return rand(1000, 9999);
    }
}

function getServersFromFirebase() {
    $firebase = new FirebaseDB();
    return $firebase->getServers();
}

function saveServerToFirebase($serverData) {
    $firebase = new FirebaseDB();
    return $firebase->addServer($serverData);
}

function deleteServerFromFirebase($serverId) {
    $firebase = new FirebaseDB();
    return $firebase->deleteServer($serverId);
}

function generateServerId() {
    $firebase = new FirebaseDB();
    return $firebase->generateId();
}
?>