<?php
session_start();

// مدة الاحتفاظ بالملفات (15 دقيقة)
$keepTime = 15 * 60;

// مجلد الحفظ
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// حذف الملفات القديمة
foreach (glob($uploadDir . "*") as $file) {
    if (is_file($file) && time() - filemtime($file) > $keepTime) unlink($file);
}

// التحقق من رفع الملف
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die("حدث خطأ أثناء رفع الصورة.");
}

$tmpName = $_FILES['image']['tmp_name'];
$format  = $_POST['format'] ?? 'jpg';
$transparent = isset($_POST['transparent']) ? true : false;

// معلومات الصورة
$imageInfo = getimagesize($tmpName);
if ($imageInfo === false) die("الملف المرفوع ليس صورة.");
$mime = $imageInfo['mime'];

// توليد اسم فريد للملف
$uniqueName = 'img_' . uniqid() . ($transparent ? '_transparent' : '') . '.' . ($transparent ? 'png' : $format);
$outputPath = $uploadDir . $uniqueName;

// معالجة الصورة
if($transparent){
    // إرسال الصورة إلى Remove.bg API
    $apiKey = "YOUR_API_KEY_HERE"; // ضع مفتاحك هنا
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.remove.bg/v1.0/removebg");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $postData = [
        "image_file" => new CURLFile($tmpName),
        "size" => "auto",
        "format" => "png"
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Api-Key: $apiKey"]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($httpCode == 200){
        file_put_contents($outputPath, $response);
    } else die("حدث خطأ أثناء إزالة الخلفية.");
} else {
    // تحويل عادي مع ضغط محسّن
    switch ($mime) {
        case 'image/jpeg': $img = imagecreatefromjpeg($tmpName); break;
        case 'image/png': $img = imagecreatefrompng($tmpName); break;
        case 'image/webp': $img = imagecreatefromwebp($tmpName); break;
        default: die("صيغة الصورة غير مدعومة.");
    }

    $jpegQuality = 60; 
    $pngCompression = 6;

    switch ($format){
        case 'jpg': imagejpeg($img, $outputPath, $jpegQuality); break;
        case 'png': imagepng($img, $outputPath, $pngCompression); break;
        case 'webp': imagewebp($img, $outputPath, $jpegQuality); break;
        default: imagedestroy($img); die("صيغة التحويل غير مدعومة.");
    }
    imagedestroy($img);
}

// حساب الأحجام
$originalSize = filesize($tmpName);
$compressedSize = filesize($outputPath);
function formatSize($bytes){
    if ($bytes >= 1048576) return round($bytes / 1048576,2)." MB";
    if ($bytes >= 1024) return round($bytes / 1024,2)." KB";
    return $bytes." B";
}

// عرض النتيجة UX
echo "<div style='text-align:center; font-family:Arial;'>";
echo "<h2>✅ تم معالجة الصورة بنجاح!</h2>";
echo "<p>حجم الصورة الأصلي: <strong>".formatSize($originalSize)."</strong></p>";
echo "<p>حجم الصورة بعد المعالجة: <strong>".formatSize($compressedSize)."</strong></p>";
echo "<p>الملف سيبقى متاحًا لمدة: 15 دقيقة</p>";
echo "<a href='$outputPath' download style='display:inline-block;margin:10px;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>⬇️ تحميل الصورة</a><br>";
echo "<a href='index.php' style='display:inline-block;margin:10px;padding:10px 20px;background:#0073e6;color:white;text-decoration:none;border-radius:5px;'>🔄 رفع صورة أخرى</a>";
echo "</div>";
