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

// صفحة النتيجة
echo "<h2>✅ تم ضغط وتحويل الصورة بنجاح</h2>";
echo "<p>سيتم حذف الصورة تلقائيًا بعد 15 دقيقة.</p>";
echo "<a href='$outputPath' download>⬇️ تحميل الصورة</a>";
