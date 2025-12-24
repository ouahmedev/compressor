<?php

// مدة الاحتفاظ بالملفات (15 دقيقة)
$keepTime = 15 * 60;

// مجلد الحفظ
$uploadDir = 'uploads/';

// إنشاء المجلد إن لم يكن موجودًا
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// حذف الملفات القديمة
foreach (glob($uploadDir . "*") as $file) {
    if (is_file($file) && time() - filemtime($file) > $keepTime) {
        unlink($file);
    }
}

// التحقق من رفع الملف
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die("حدث خطأ أثناء رفع الصورة.");
}

$tmpName = $_FILES['image']['tmp_name'];
$format  = $_POST['format'] ?? 'jpg';

// معلومات الصورة
$imageInfo = getimagesize($tmpName);
if ($imageInfo === false) {
    die("الملف المرفوع ليس صورة.");
}

$mime = $imageInfo['mime'];

// إنشاء صورة من الملف
switch ($mime) {
    case 'image/jpeg':
        $img = imagecreatefromjpeg($tmpName);
        break;
    case 'image/png':
        $img = imagecreatefrompng($tmpName);
        break;
    case 'image/webp':
        $img = imagecreatefromwebp($tmpName);
        break;
    default:
        die("صيغة الصورة غير مدعومة.");
}

// اسم فريد للملف
$uniqueName = 'img_' . uniqid() . '.' . $format;
$outputPath = $uploadDir . $uniqueName;

// التحويل والضغط
switch ($format) {
    case 'jpg':
        imagejpeg($img, $outputPath, 75);
        break;
    case 'png':
        imagepng($img, $outputPath, 6);
        break;
    case 'webp':
        imagewebp($img, $outputPath, 75);
        break;
    default:
        imagedestroy($img);
        die("صيغة التحويل غير مدعومة.");
}

imagedestroy($img);

// حساب حجم الملفات
$originalSize = filesize($tmpName);
$compressedSize = filesize($outputPath);

// تحويل الحجم إلى صيغة مقروءة
function formatSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . " MB";
    if ($bytes >= 1024) return round($bytes / 1024, 2) . " KB";
    return $bytes . " B";
}

// صفحة النتيجة مع UX محسّن
echo "<div style='text-align:center; font-family:Arial;'>";
echo "<h2>✅ تم ضغط وتحويل الصورة بنجاح!</h2>";
echo "<p>حجم الصورة الأصلي: <strong>" . formatSize($originalSize) . "</strong></p>";
echo "<p>حجم الصورة بعد الضغط: <strong>" . formatSize($compressedSize) . "</strong></p>";
echo "<p>الملف سيبقى متاحًا لمدة: 15 دقيقة</p>";
echo "<a href='$outputPath' download style='display:inline-block;margin:10px;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>⬇️ تحميل الصورة</a><br>";
echo "<a href='index.php' style='display:inline-block;margin:10px;padding:10px 20px;background:#0073e6;color:white;text-decoration:none;border-radius:5px;'>🔄 رفع صورة أخرى</a>";
echo "</div>";