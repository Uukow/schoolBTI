<?php
/**
 * Mobile-friendly QR scan check-in page
 */
require_once '../../config/config.php';
$token = sanitize($_GET['token'] ?? '');
$session = $token ? fetchOne(executeQuery(
    "SELECT * FROM hr_qr_sessions WHERE session_token = ? AND is_active = 1 AND valid_date = CURDATE()", 's', [$token]
)) : null;
$staffId = 0;
if (isLoggedIn()) {
    $u = getCurrentUser();
    $s = fetchOne(executeQuery("SELECT id FROM staff WHERE user_id = ?", 'i', [$u['id']]));
    $staffId = $s['id'] ?? 0;
}
?><!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>QR Check-in</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-5 text-center" style="max-width:400px">
<?php if (!$session): ?>
<div class="alert alert-danger">Invalid or expired QR code.</div>
<?php elseif (!$staffId): ?>
<div class="alert alert-warning">Please <a href="<?php echo APP_URL; ?>login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">login</a> as staff to check in.</div>
<?php else: ?>
<h4 class="mb-4">Attendance Check-in</h4>
<p><?php echo htmlspecialchars($session['location_name'] ?? 'Office'); ?></p>
<button class="btn btn-primary btn-lg w-100" id="checkInBtn">Check In Now</button>
<div id="result" class="mt-3"></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('checkInBtn').onclick=function(){
fetch('<?php echo APP_URL; ?>ajax/hr/qr-checkin.php',{method:'POST',headers:{'Content-Type':'application/json'},
body:JSON.stringify({token:'<?php echo $token; ?>',staff_id:<?php echo $staffId; ?>})})
.then(r=>r.json()).then(res=>{
document.getElementById('result').innerHTML=res.success?'<div class="alert alert-success">'+res.message+'</div>':'<div class="alert alert-danger">'+res.message+'</div>';
});};
</script>
<?php endif; ?>
</div></body></html>
