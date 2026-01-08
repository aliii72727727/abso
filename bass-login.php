<?php
// ملف تخزين بيانات المستخدم المشفرة
// هذا ملف آمن لا يمكن الوصول إليه مباشرة

// بيانات المستخدم المشفرة باستخدام التجزئة القوية
$storedUsername = 'abso';
$storedPasswordHash = password_hash('Abso1998absi', PASSWORD_DEFAULT);

// دالة للتحقق من بيانات تسجيل الدخول
function verifyLogin($username, $password) {
    global $storedUsername, $storedPasswordHash;
    
    // التحقق من اسم المستخدم
    if ($username !== $storedUsername) {
        return false;
    }
    
    // التحقق من كلمة المرور باستخدام password_verify
    if (password_verify($password, $storedPasswordHash)) {
        return true;
    }
    
    return false;
}

// منع الوصول المباشر إلى هذا الملف
if (basename($_SERVER['PHP_SELF']) == 'bass-login.php') {
    header('HTTP/1.0 403 Forbidden');
    exit('الوصول إلى هذا الملف غير مسموح');
}
?>