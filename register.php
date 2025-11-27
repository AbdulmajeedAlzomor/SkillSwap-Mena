<?php
require_once __DIR__ . '/backend/config/config.php';
require_once __DIR__ . '/backend/config/db.php';

$page_title = "تسجيل حساب جديد - SkillSwap";

$errors = [];
$success_message = "";
$full_name = "";
$email = "";
$bio = "";

// عند إرسال الفورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $bio = trim($_POST['bio'] ?? '');

    // تحقق من الحقول
    if ($full_name === '') {
        $errors[] = "الاسم الكامل مطلوب.";
    }

    if ($email === '') {
        $errors[] = "البريد الإلكتروني مطلوب.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "صيغة البريد الإلكتروني غير صحيحة.";
    }

    if ($password === '') {
        $errors[] = "كلمة المرور مطلوبة.";
    } elseif (strlen($password) < 6) {
        $errors[] = "كلمة المرور يجب أن تكون 6 أحرف على الأقل.";
    }

    if ($confirm_password === '') {
        $errors[] = "تأكيد كلمة المرور مطلوب.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "كلمتا المرور غير متطابقتين.";
    }

    // لو ما فيه أخطاء حتى الآن، نتحقق من تكرار الإيميل ونسجل
    if (empty($errors)) {
        try {
            $db = getDBConnection();

            // هل الإيميل موجود مسبقاً؟
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                $errors[] = "هذا البريد الإلكتروني مسجّل مسبقًا. جرّب تسجيل الدخول.";
            } else {
                // حفظ المستخدم
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $insertStmt = $db->prepare("
                    INSERT INTO users (full_name, email, password_hash, bio, created_at)
                    VALUES (:full_name, :email, :password_hash, :bio, NOW())
                ");

                $insertStmt->execute([
                    'full_name'     => $full_name,
                    'email'         => $email,
                    'password_hash' => $password_hash,
                    'bio'           => $bio !== '' ? $bio : null,
                ]);

                $success_message = "تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.";
                // إعادة تعيين الحقول
                $full_name = "";
                $email = "";
                $bio = "";
            }
        } catch (Exception $e) {
            $errors[] = "حدث خطأ غير متوقع، حاول مرة أخرى لاحقًا.";
            // يمكنك أثناء التطوير طباعة الخطأ لو حاب:
            // $errors[] = $e->getMessage();
        }
    }
}
?>

<?php include __DIR__ . '/backend/includes/header.php'; ?>

<section class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">إنشاء حساب جديد</h1>
        <p class="auth-subtitle">
            أنشئ حسابك وابدأ بتبادل مهاراتك مع الآخرين في SkillSwap.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?>
                <div class="alert-link">
                    <a href="login.php">الانتقال إلى صفحة تسجيل الدخول</a>
                </div>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" class="auth-form" novalidate>
            <div class="form-group">
                <label for="full_name">الاسم الكامل</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    required
                    value="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="اكتب اسمك الكامل هنا"
                >
            </div>

            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="example@mail.com"
                >
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="6"
                    placeholder="اختر كلمة مرور قوية"
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                    minlength="6"
                    placeholder="أعد كتابة كلمة المرور"
                >
            </div>

            <div class="form-group">
                <label for="bio">نبذة عنك (اختياري)</label>
                <textarea
                    id="bio"
                    name="bio"
                    rows="3"
                    placeholder="اكتب نبذة قصيرة عن مهاراتك واهتماماتك (اختياري)"
                ><?php echo htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary auth-submit">
                إنشاء الحساب
            </button>

            <p class="auth-switch">
                لديك حساب بالفعل؟
                <a href="login.php">تسجيل الدخول</a>
            </p>
        </form>
    </div>
</section>

<?php include __DIR__ . '/backend/includes/footer.php'; ?>
