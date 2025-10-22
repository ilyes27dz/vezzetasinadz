<?php
// ملف لتصحيح المسارات تلقائياً

$directory = __DIR__;

// البحث عن جميع ملفات PHP
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory)
);

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $content = file_get_contents($filePath);
        
        // تصحيح المسارات
        $content = str_replace("require_once __DIR__ . '/../db.php'
", "require_once __DIR__ . '/../db.php'", $content);
        $content = str_replace("require_once 'db.php'", "require_once __DIR__ . '/db.php'", $content);
        $content = str_replace("include __DIR__ . '/../includes/header.php'", "include __DIR__ . '/../../includes/header.php'", $content);
        $content = str_replace("include 'includes/footer.php'", "include __DIR__ . '/../../includes/footer.php'", $content);
        
        // تصحيح Boolean
        $content = preg_replace('/WHERE\s+is_active\s*=\s*1/', 'WHERE is_active=TRUE', $content);
        $content = preg_replace('/WHERE\s+is_active\s*=\s*0/', 'WHERE is_active=FALSE', $content);
        $content = preg_replace('/SET\s+is_active\s*=\s*1/', 'SET is_active=TRUE', $content);
        $content = preg_replace('/SET\s+is_active\s*=\s*0/', 'SET is_active=FALSE', $content);
        
        file_put_contents($filePath, $content);
    }
}

echo "تم تصحيح المسارات!";
?>
