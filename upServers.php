<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

include 'firebase.php';

// معالجة إضافة سيرفر جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serverNumber = $_POST['serverNumber'];
    $serverName = $_POST['serverName'];
    $serverUrl = $_POST['serverUrl'];
    $serverImage = $_POST['serverImage'];
    $serverStatus = $_POST['serverStatus'];
    $serverRegion = $_POST['serverRegion'];
    
    // إنشاء الاسم النهائي مع HTML
    $finalName = $serverNumber . '. ' . $serverName;
    
    $serverData = [
        'id' => generateServerId(),
        'name' => $finalName,
        'region' => $serverRegion,
        'serverUrl' => $serverUrl,
        'status' => $serverStatus === 'ON' ? 1 : 0,
        'image' => $serverImage,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $result = saveServerToFirebase($serverData);
    
    if ($result) {
        updateServersApi();
        header('Location: ' . $_SERVER['PHP_SELF'] . '?message=added');
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?message=error');
    }
    exit;
}

// معالجة حذف سيرفر
if (isset($_GET['delete'])) {
    $serverId = $_GET['delete'];
    $result = deleteServerFromFirebase($serverId);
    
    if ($result) {
        updateServersApi();
        header('Location: ' . $_SERVER['PHP_SELF'] . '?message=deleted');
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?message=delete_error');
    }
    exit;
}

$servers = getServersFromFirebase();

// دالة تحديث ملف API
function updateServersApi() {
    $servers = getServersFromFirebase();
    
    $apiData = [
        "success" => true,
        "servers" => []
    ];
    
    foreach ($servers as $server) {
        $apiData["servers"][] = [
            "id" => intval($server['id']),
            "name" => $server['name'], // الاسم كما هو مع HTML
            "region" => $server['region'],
            "serverUrl" => $server['serverUrl'],
            "status" => intval($server['status'])
        ];
    }
    
    if (!is_dir('api')) {
        mkdir('api', 0777, true);
    }
    
    $apiContent = "<?php\nheader('Content-Type: application/json');\n?>" . 
                  json_encode($apiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    file_put_contents('api/servers.php', $apiContent);
    file_put_contents('api/servers.json', json_encode($apiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

updateServersApi();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF9800;
            --primary-dark: #F57C00;
            --primary-light: #FFE0B2;
            --text-color: #333;
            --bg-color: #FFF3E0;
            --success-color: #4CAF50;
            --danger-color: #F44336;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            position: relative;
        }
        
        .back-btn {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) scale(1.1);
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .description {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .server-form {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--primary-dark);
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        .btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-danger {
            background: var(--danger-color);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .servers-list {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        
        .servers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .server-card {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            background: white;
            position: relative;
            overflow: hidden;
        }
        
        .server-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .server-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }
        
        .server-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .server-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-dark);
        }
        
        .server-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status-on {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-off {
            background-color: var(--danger-color);
            color: white;
        }
        
        .server-details {
            margin-bottom: 15px;
        }
        
        .detail-item {
            margin-bottom: 10px;
            display: flex;
        }
        
        .detail-label {
            font-weight: bold;
            color: var(--primary-dark);
            min-width: 100px;
        }
        
        .server-image {
            max-width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-top: 10px;
            background: #f5f5f5;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: #777;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .server-id-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .server-count {
            background: var(--primary-color);
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        @media (max-width: 768px) {
            .servers-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .server-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .server-status {
                margin-top: 10px;
            }
        }
        
        .html-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <button class="back-btn" onclick="window.location.href='dashboard.php'">
                <i class="fas fa-arrow-left"></i>
            </button>
            <h1>Server Management System</h1>
            <p class="description">Add and manage game servers with HTML support</p>
        </header>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="alert <?php echo $_GET['message'] === 'added' || $_GET['message'] === 'deleted' ? 'alert-success' : 'alert-error'; ?>">
                <i class="fas <?php echo $_GET['message'] === 'added' || $_GET['message'] === 'deleted' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php
                switch ($_GET['message']) {
                    case 'added': echo 'Server added successfully!'; break;
                    case 'deleted': echo 'Server deleted successfully!'; break;
                    case 'error': echo 'Error adding server!'; break;
                    case 'delete_error': echo 'Error deleting server!'; break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <section class="server-form">
            <h2 style="color: var(--primary-dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-plus-circle"></i> Add New Server
            </h2>
            <form id="serverForm" method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="serverNumber">Server Number</label>
                        <input type="text" id="serverNumber" name="serverNumber" placeholder="e.g., 06" required>
                        <small style="color: #666;">Server number (e.g., 01, 02, 03)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="serverRegion">Server Region</label>
                        <select id="serverRegion" name="serverRegion" required>
                            <option value="peru">Peru</option>
                            <option value="australia">Australia</option>
                            <option value="europe">Europe</option>
                            <option value="asia">Asia</option>
                            <option value="usa">USA</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="serverName">Server Name (Supports HTML)</label>
                    <input type="text" id="serverName" name="serverName" placeholder="e.g., WFT - NEW&lt;img class='team' src='https://example.com/logo.png'&gt;" required>
                    <small style="color: #666;">You can use HTML tags in the name</small>
                    <div class="html-preview" id="namePreview">
                        Preview will appear here
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="serverUrl">Server URL</label>
                    <input type="url" id="serverUrl" name="serverUrl" placeholder="e.g., wss://mum-a.wormate.io:32609/wormy" required>
                </div>
                
                <div class="form-group">
                    <label for="serverImage">Server Image URL</label>
                    <input type="url" id="serverImage" name="serverImage" placeholder="https://example.com/server-image.jpg" required>
                    <div id="imagePreview" style="margin-top: 10px; display: none;">
                        <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 6px;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="serverStatus">Server Status</label>
                    <select id="serverStatus" name="serverStatus" required>
                        <option value="ON">ON</option>
                        <option value="OFF">OFF</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Add Server
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearForm()">
                        <i class="fas fa-eraser"></i> Clear Form
                    </button>
                </div>
            </form>
        </section>
        
        <section class="servers-list">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: var(--primary-dark); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-server"></i> Servers List
                </h2>
                <div class="server-count">
                    <i class="fas fa-list"></i>
                    Total Servers: <?php echo count($servers); ?>
                </div>
            </div>
            
            <div class="servers-grid" id="serversContainer">
                <?php if (empty($servers)): ?>
                    <div class="empty-state">
                        <i class="fas fa-server"></i>
                        <h3>No Servers Added</h3>
                        <p>Start by adding your first server using the form above.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($servers as $server): ?>
                        <div class="server-card">
                            <div class="server-header">
                                <div class="server-name">
                                    Server #<?php echo $server['id']; ?>
                                    <span class="server-id-badge">ID: <?php echo $server['id']; ?></span>
                                </div>
                                <div class="server-status <?php echo $server['status'] ? 'status-on' : 'status-off'; ?>">
                                    <?php echo $server['status'] ? 'ON' : 'OFF'; ?>
                                </div>
                            </div>
                            <div class="server-details">
                                <div class="detail-item">
                                    <span class="detail-label">Number:</span>
                                    <span><?php echo htmlspecialchars(explode('.', $server['name'])[0]); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Region:</span>
                                    <span><?php echo htmlspecialchars($server['region']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Name:</span>
                                    <span><?php echo $server['name']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">URL:</span>
                                    <span style="word-break: break-all;"><?php echo htmlspecialchars($server['serverUrl']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Image:</span>
                                    <div>
                                        <img src="<?php echo htmlspecialchars($server['image']); ?>" 
                                             alt="Server Image" class="server-image"
                                             onerror="this.style.display='none'">
                                    </div>
                                </div>
                            </div>
                            <div class="actions">
                                <button class="btn btn-danger" onclick="deleteServer(<?php echo $server['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        // معاينة الاسم مع HTML
        document.getElementById('serverName').addEventListener('input', function(e) {
            const name = e.target.value;
            document.getElementById('namePreview').innerHTML = name || 'Preview will appear here';
        });

        // معاينة الصورة
        document.getElementById('serverImage').addEventListener('input', function(e) {
            const url = e.target.value;
            if (url) {
                document.getElementById('previewImg').src = url;
                document.getElementById('imagePreview').style.display = 'block';
            } else {
                document.getElementById('imagePreview').style.display = 'none';
            }
        });

        // مسح النموذج
        function clearForm() {
            document.getElementById('serverForm').reset();
            document.getElementById('namePreview').innerHTML = 'Preview will appear here';
            document.getElementById('imagePreview').style.display = 'none';
        }

        // حذف سيرفر
        function deleteServer(serverId) {
            if (confirm('Are you sure you want to delete this server?')) {
                window.location.href = '?delete=' + serverId;
            }
        }

        // تأثيرات البطاقات
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.server-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
</body>
</html>