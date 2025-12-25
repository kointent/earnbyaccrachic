function checkRole($allowed_roles) {
    session_start();
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}

// Usage in admin/payouts.php
// checkRole(['super_admin', 'finance_manager']);
