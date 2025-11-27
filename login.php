<?php
// يجب أن يكون session_start أول شيء قبل أي مخرجات HTML
session_start();

require_once __DIR__ . '/backend/config/config.php';
require_once __DIR__ . '/backend/config/db.php';

$page_title = "تسجيل الدخول - SkillSwap";

$errors = [];
$email = "";

// لو المستخدم مسجّل دخول أصلاً نحوله مباشرة للداشبورد
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// عند إرسال الفورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $errors[] = "البريد الإلكتروني مطلوب.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "صيغة البريد الإلكتروني غير صحيحة.";
    }

    if ($password === '') {
        $errors[] = "كلمة المرور مطلوبة.";
    }

    if (empty($errors)) {
        try {
            $db = getDBConnection();

            $stmt = $db->prepare("SELECT id, full_name, email, password_hash FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // كلمة المرور صحيحة -> ننشئ جلسة
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];

                // تحديث آخر تسجيل دخول
                $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $updateStmt->execute(['id' => $user['id']]);

                // تحويل للداشبورد
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
            }
        } catch (Exception $e) {
            $errors[] = "حدث خطأ غير متوقع، حاول مرة أخرى.";
            // للتطوير فقط:
            // $errors[] = $e->getMessage();
        }
    }
}
?>

<?php include __DIR__ . '/backend/includes/header.php'; ?>

<section class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title">تسجيل الدخول</h1>
        <p class="auth-subtitle">
            ادخل إلى حسابك وابدأ في استكشاف المهارات ومشاركتها مع الآخرين.
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

        <form action="login.php" method="post" class="auth-form" novalidate>
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
                    placeholder="أدخل كلمة المرور"
                >
            </div>

            <button type="submit" class="btn btn-primary auth-submit">
                تسجيل الدخول
            </button>

            <p class="auth-switch">
                ليس لديك حساب بعد؟
                <a href="register.php">إنشاء حساب جديد</a>
            </p>
        </form>
    </div>
</section>

<?php include __DIR__ . '/backend/includes/footer.php'; ?>
