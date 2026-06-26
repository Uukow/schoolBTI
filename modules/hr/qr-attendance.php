<?php
require_once '../../config/config.php';
hrRequirePage('hr_attendance', 'view');
$pageTitle = 'QR Attendance';
$branches = fetchAll(executeQuery("SELECT * FROM branches WHERE is_active = 1"));
include '../../includes/header.php'; include '../../includes/sidebar.php';
?>
<div class="content-page"><div class="content"><div class="container-fluid">
<div class="page-title-box"><div class="page-title-right"><button class="btn btn-primary" id="genQrBtn"><i class="ri-qr-code-line"></i> Generate Today's QR</button></div><h4 class="page-title">QR Code Attendance</h4></div>
<div class="row"><div class="col-md-6">
<div class="card"><div class="card-body text-center" id="qrDisplay"><p class="text-muted">Generate a QR code for staff to scan</p></div></div>
</div><div class="col-md-6">
<div class="card"><div class="card-body">
<h5>Biometric Integration</h5>
<p class="text-muted small">POST to <code>ajax/hr/biometric-punch.php</code> with header <code>X-API-Key</code></p>
<pre class="bg-light p-2 small">{
  "biometric_id": "BIO001",
  "punch_time": "2026-06-26 08:00:00",
  "punch_type": "IN"
}</pre>
</div></div></div></div>
</div></div></div>
<?php include '../../includes/footer.php'; ?>
<script src="<?php echo APP_URL; ?>assets/js/hr-module.js"></script>
<script>
(function(){var H=HrModule;
document.getElementById('genQrBtn').onclick=function(){
H.post('ajax/hr/generate-qr-session.php',{location_name:'Office Check-in',valid_date:new Date().toISOString().slice(0,10)}).then(function(r){
if(r.success){
document.getElementById('qrDisplay').innerHTML='<img src="'+r.data.qr_image+'" class="img-fluid mb-3" style="max-width:280px"><p><a href="'+r.data.qr_url+'" target="_blank">'+r.data.qr_url+'</a></p><p class="small text-muted">Valid today only</p>';
H.success('QR session created');
}else H.error(r.message);});};})();
</script>
