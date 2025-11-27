<?php
require_once __DIR__ . '/backend/config/config.php';
// تضمين ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/backend/config/db.php';

// اختبار اتصال (مؤقتًا أثناء التطوير)
try {
    $db = getDBConnection();
    // echo "<!-- DB Connected OK -->";
} catch (Exception $e) {
    echo "خطأ في الاتصال بقاعدة البيانات";
}

// تضمين ملف الإعدادات
require_once __DIR__ . '/backend/config/config.php';

// عنوان مخصص للصفحة الرئيسية
$page_title = "SkillSwap - تبادل المهارات";
?>

<?php include __DIR__ . '/backend/includes/header.php'; ?>

    <section class="hero-card">
        <h1>مرحبًا بك في SkillSwap 👋</h1>
        <p>
            منصة لتبادل المهارات بين المستخدمين بدون مقابل مادي.<br>
            شارك مهاراتك، وتعلم من الآخرين، وابنِ شبكة من العلاقات المفيدة.
        </p>
        <div class="hero-actions">
            <a href="register.php" class="btn btn-primary">ابدأ الآن بالتسجيل</a>
            <a href="login.php" class="btn btn-outline">لديك حساب؟ سجل الدخول</a>
        </div>
    </section>

<?php include __DIR__ . '/backend/includes/footer.php'; ?>
