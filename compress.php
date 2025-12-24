<?php
if(isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $tmpName = $file['tmp_name'];
    $format = $_POST['format'];

    // تحميل الصورة
    $imageInfo = getimagesize($tmpName);
    $mime = $imageInfo['mime'];

    // إنشاء صورة من الملف المرفوع
    switch($mime) {
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
            die("الصيغة غير مدعومة.");
    }

    // إنشاء اسم للملف الجديد
    $outputFile = 'compressed_image.' . $format;

    // ضغط وتحويل
    switch($format) {
        case 'jpg':
            imagejpeg($img, $outputFile, 75); // جودة 75%
            break;
        case 'png':
            imagepng($img, $outputFile, 6);  // ضغط متوسط
            break;
        case 'webp':
            imagewebp($img, $outputFile, 75);
            break;
        default:
            die("الصيغة غير مدعومة.");
    }

    imagedestroy($img);

    // رابط التحميل
    echo "<h2>تم الضغط والتحويل!</h2>";
    echo "<a href='$outputFile' download>تحميل الصورة</a>";
} else {
    echo "لم يتم رفع أي صورة.";
}
?>
