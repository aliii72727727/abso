<?php
include 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// جلب الإحصائيات من قاعدة البيانات
try {
    // إحصائيات المستخدمين (تعديل الاستعلام حسب هيكل قاعدة البيانات الخاصة بك)
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    // إحصائيات السيرفرات
    $stmt = $pdo->query("SELECT COUNT(*) as total_servers FROM servers");
    $total_servers = $stmt->fetch(PDO::FETCH_ASSOC)['total_servers'];
    
    // إحصائيات السكينز
    $stmt = $pdo->query("SELECT COUNT(*) as total_skins FROM skins");
    $total_skins = $stmt->fetch(PDO::FETCH_ASSOC)['total_skins'];
    
    // إحصائيات الفرق
    $stmt = $pdo->query("SELECT COUNT(*) as total_teams FROM teams");
    $total_teams = $stmt->fetch(PDO::FETCH_ASSOC)['total_teams'];
    
    // إحصائيات إضافية
    $stmt = $pdo->query("SELECT COUNT(*) as active_users FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $active_users = $stmt->fetch(PDO::FETCH_ASSOC)['active_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as flag_servers FROM servers WHERE type = 'flag'");
    $flag_servers = $stmt->fetch(PDO::FETCH_ASSOC)['flag_servers'];
    
} catch(PDOException $e) {
    // في حالة وجود خطأ، استخدم قيم افتراضية
    $total_users = 0;
    $total_servers = 0;
    $total_skins = 0;
    $total_teams = 0;
    $active_users = 0;
    $flag_servers = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ABSO EXTENSION</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff8c00; /* برتقالي داكن */
            --secondary-color: #ffa733;
            --accent-color: #ffd700; /* أصفر ذهبي */
            --light-color: #ffffff; /* أبيض */
            --dark-color: #e67e00;
            --text-color: #333333;
            --border-radius: 8px;
            --transition: all 0.3s ease;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* الشريط الجانبي */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--dark-color) 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: var(--transition);
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--light-color);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        
        .sidebar-header p {
            font-size: 14px;
            opacity: 0.9;
            color: var(--accent-color);
            font-weight: 500;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            transition: var(--transition);
            cursor: pointer;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: var(--accent-color);
        }
        
        .menu-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left-color: var(--accent-color);
        }
        
        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
            width: 20px;
            text-align: center;
            color: var(--accent-color);
        }
        
        .menu-item.sign-out {
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            color: #ffebee;
        }
        
        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
            transition: var(--transition);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .header h1 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 28px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--light-color);
            padding: 10px 15px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        /* بطاقات الإحصائيات */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card h3 {
            color: var(--dark-color);
            margin-bottom: 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .stat-card .trend {
            font-size: 14px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .trend.up {
            color: var(--success-color);
        }
        
        .trend.down {
            color: var(--danger-color);
        }
        
        .stat-card .icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 40px;
            opacity: 0.2;
            color: var(--primary-color);
        }
        
        /* أزرار الإجراءات */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .action-btn {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-color);
            background: linear-gradient(135deg, #fff8e1 0%, white 100%);
        }
        
        .action-btn i {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .action-btn h3 {
            color: var(--dark-color);
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .action-btn p {
            color: #666;
            font-size: 14px;
        }
        
        /* مخططات الإحصائيات */
        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-header h3 {
            color: var(--dark-color);
        }
        
        .chart-placeholder {
            height: 250px;
            background: #f9f9f9;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            border: 1px dashed #ddd;
        }
        
        /* زر القائمة المتنقلة */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            width: 40px;
            height: 40px;
            font-size: 20px;
            cursor: pointer;
            z-index: 1100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        /* تصميم متجاوب */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 20px 15px;
            }
            
            .stats-cards,
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
        
        /* تأثيرات إضافية */
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 140, 0, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 140, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 140, 0, 0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-in {
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        /* تقدم دائري */
        .progress-ring {
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            transition: stroke-dashoffset 0.5s;
            transform-origin: 50% 50%;
        }
        
        /* قسم الإحصائيات المتقدمة */
        .advanced-stats {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .advanced-stats h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }
        
        .stat-item:hover {
            background: #fff8e1;
            transform: translateY(-3px);
        }
        
        .stat-item .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 5px 0;
        }
        
        .stat-item .stat-label {
            font-size: 14px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- زر القائمة المتنقلة -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
    
    <!-- الشريط الجانبي -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>ABSO EXTENSION</h2>
            <p>Control Panel</p>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item" onclick="window.location.href='wep.php'">
                <i class="fas fa-globe"></i>
                <span>ABSO WEP</span>
            </div>
            <div class="menu-item" onclick="window.location.href='Extension_exTt.php'">
                <i class="fas fa-cogs"></i>
                <span>Extension exTt.php</span>
            </div>
            <div class="menu-item sign-out" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </div>
        </div>
    </div>
    
    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <div class="header">
            <h1>Dashboard Overview</h1>
            <div class="user-info">
                <div class="user-avatar">W</div>
                <span>WORMATEABSOIrak</span>
            </div>
        </div>
        
        <!-- بطاقات الإحصائيات الرئيسية -->
        <div class="stats-cards">
            <div class="stat-card fade-in">
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3><i class="fas fa-users"></i> Total Users</h3>
                <div class="value"><?php echo $total_users; ?></div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i> 12% from last month
                </div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                <div class="icon">
                    <i class="fas fa-server"></i>
                </div>
                <h3><i class="fas fa-server"></i> Active Servers</h3>
                <div class="value"><?php echo $total_servers; ?></div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i> 5% from last week
                </div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                <div class="icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3><i class="fas fa-palette"></i> Available Skins</h3>
                <div class="value"><?php echo $total_skins; ?></div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i> 8 new this month
                </div>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                <div class="icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3><i class="fas fa-users-cog"></i> Active Teams</h3>
                <div class="value"><?php echo $total_teams; ?></div>
                <div class="trend down">
                    <i class="fas fa-arrow-down"></i> 2 less than last month
                </div>
            </div>
        </div>
        
        <!-- إحصائيات متقدمة -->
        <div class="advanced-stats slide-in">
            <h2><i class="fas fa-chart-line"></i> Advanced Statistics</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <i class="fas fa-user-clock" style="color: var(--primary-color); font-size: 24px;"></i>
                    <div class="stat-value"><?php echo $active_users; ?></div>
                    <div class="stat-label">Active Users Now</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-flag" style="color: var(--primary-color); font-size: 24px;"></i>
                    <div class="stat-value"><?php echo $flag_servers; ?></div>
                    <div class="stat-label">Flag Servers</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-download" style="color: var(--primary-color); font-size: 24px;"></i>
                    <div class="stat-value">1,248</div>
                    <div class="stat-label">Today's Downloads</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-eye" style="color: var(--primary-color); font-size: 24px;"></i>
                    <div class="stat-value">5,672</div>
                    <div class="stat-label">Page Views</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-share-alt" style="color: var(--primary-color); font-size: 24px;"></i>
                    <div class="stat-value">342</div>
                    <div class="stat-label">Social Shares</div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-comments" style="color: var(--primary-color); font-size: 24px;"></i>
                    <div class="stat-value">89</div>
                    <div class="stat-label">New Comments</div>
                </div>
            </div>
        </div>
        
        <!-- مخططات الإحصائيات -->
        <div class="charts-container">
            <div class="chart-card slide-in">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-bar"></i> User Activity</h3>
                    <select>
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Last 3 months</option>
                    </select>
                </div>
                <div class="chart-placeholder">
                    <i class="fas fa-chart-line" style="font-size: 48px; margin-right: 15px; color: var(--primary-color);"></i>
                    User Activity Chart
                </div>
            </div>
            
            <div class="chart-card slide-in" style="animation-delay: 0.2s;">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-pie"></i> Server Status</h3>
                </div>
                <div class="chart-placeholder">
                    <i class="fas fa-chart-pie" style="font-size: 48px; margin-right: 15px; color: var(--primary-color);"></i>
                    Server Status Chart
                </div>
            </div>
        </div>
        
        <!-- أزرار الإجراءات -->
        <div class="action-buttons">
            <div class="action-btn pulse" onclick="window.location.href='upSkins.php'">
                <i class="fas fa-upload"></i>
                <h3>Upload Skins</h3>
                <p>Manage and add new skins</p>
            </div>
            <div class="action-btn" onclick="window.location.href='upServers.php'">
                <i class="fas fa-server"></i>
                <h3>Upload Servers</h3>
                <p>Add and manage servers</p>
            </div>
            <div class="action-btn" onclick="window.location.href='usUsers.php'">
                <i class="fas fa-user-cog"></i>
                <h3>User Management</h3>
                <p>Manage user accounts</p>
            </div>
            <div class="action-btn" onclick="window.location.href='upTeams.php'">
                <i class="fas fa-users"></i>
                <h3>Manage Teams</h3>
                <p>Manage and create teams</p>
            </div>
            <div class="action-btn" onclick="window.location.href='flServers.php'">
                <i class="fas fa-flag"></i>
                <h3>Flag Servers</h3>
                <p>Manage flag servers</p>
            </div>
            <div class="action-btn" onclick="window.location.href='analytics.php'">
                <i class="fas fa-chart-bar"></i>
                <h3>Advanced Analytics</h3>
                <p>View detailed statistics</p>
            </div>
        </div>
    </div>
    
    <script>
        // التحكم في القائمة المتنقلة
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // إغلاق القائمة عند النقر خارجها (للأجهزة المحمولة)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth <= 992 && 
                !sidebar.contains(event.target) && 
                !mobileBtn.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });
        
        // إضافة تأثيرات للبطاقات عند التمرير
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // تطبيق التأثير على البطاقات
        document.querySelectorAll('.action-btn').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
            observer.observe(card);
        });
        
        // تأثيرات إضافية للرسوم البيانية
        document.querySelectorAll('.chart-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateX(-20px)';
            card.style.transition = `opacity 0.5s ease ${index * 0.2}s, transform 0.5s ease ${index * 0.2}s`;
            observer.observe(card);
        });
        
        // تأثيرات للإحصائيات المتقدمة
        document.querySelectorAll('.stat-item').forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(10px)';
            item.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
            observer.observe(item);
        });
    </script>
</body>
</html>