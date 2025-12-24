<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>ضغط وتحويل الصور</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>ضغط وتحويل الصور</h1>
        <form action="compress.php" method="post" enctype="multipart/form-data">
            <label>اختر صورة للضغط والتحويل:</label>
            <input type="file" name="image" accept="image/png, image/jpeg, image/webp" required>
            <label>اختر صيغة التحويل:</label>
            <select name="format">
                <option value="jpg">JPG</option>
                <option value="png">PNG</option>
                <option value="webp">WEBP</option>
            </select>
            <button type="submit">ضغط وتحويل</button>
        </form>
    </div>
</body>
</html>
