<?php
/**
 * Public Career Portal — Job listings & online applications
 */
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Careers — ' . APP_NAME;
$vacancies = fetchAll(executeQuery(
    "SELECT v.*, b.branch_name FROM hr_job_vacancies v
     LEFT JOIN branches b ON v.branch_id = b.id
     WHERE v.status = 'Published' AND (v.application_deadline IS NULL OR v.application_deadline >= CURDATE())
     ORDER BY v.published_at DESC"
));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f4f6fb; }
        .hero { background: linear-gradient(135deg, #1a56db, #0e3a8c); color: #fff; padding: 60px 0; }
        .job-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); transition: transform .2s; }
        .job-card:hover { transform: translateY(-3px); }
    </style>
</head>
<body>
<div class="hero text-center">
    <div class="container">
        <h1 class="display-5 fw-bold"><i class="ri-briefcase-line"></i> Join <?php echo htmlspecialchars(APP_NAME); ?></h1>
        <p class="lead opacity-75">Explore open positions and apply online</p>
        <a href="<?php echo APP_URL; ?>login.php" class="btn btn-outline-light btn-sm">Staff Login</a>
    </div>
</div>
<div class="container py-5">
    <?php if (empty($vacancies)): ?>
    <div class="alert alert-info text-center">No open positions at this time. Please check back later.</div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($vacancies as $v): ?>
        <div class="col-md-6">
            <div class="card job-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($v['job_title']); ?></h5>
                    <p class="text-muted mb-2">
                        <i class="ri-building-line"></i> <?php echo htmlspecialchars($v['branch_name'] ?? 'All Branches'); ?>
                        &nbsp;·&nbsp; <i class="ri-briefcase-2-line"></i> <?php echo htmlspecialchars($v['employment_type']); ?>
                        &nbsp;·&nbsp; <i class="ri-group-line"></i> <?php echo (int)$v['openings']; ?> opening(s)
                    </p>
                    <?php if ($v['application_deadline']): ?>
                    <p class="small text-danger"><i class="ri-calendar-line"></i> Apply by <?php echo date('d M Y', strtotime($v['application_deadline'])); ?></p>
                    <?php endif; ?>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($v['description'] ?? '', 0, 200))); ?>...</p>
                    <button class="btn btn-primary apply-btn" data-vacancy='<?php echo htmlspecialchars(json_encode($v), ENT_QUOTES); ?>'>
                        <i class="ri-send-plane-line"></i> Apply Now
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Job Application</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <form id="applyForm" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="vacancy_id" id="vacancyId">
                <h6 id="jobTitleDisplay" class="text-primary mb-3"></h6>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">First Name *</label><input name="first_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Last Name *</label><input name="last_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Phone *</label><input name="phone" class="form-control" required></div>
                    <div class="col-12 mb-3"><label class="form-label">Cover Letter</label><textarea name="cover_letter" class="form-control" rows="4"></textarea></div>
                    <div class="col-12 mb-3"><label class="form-label">CV / Resume (PDF, DOC) *</label><input type="file" name="cv_file" class="form-control" accept=".pdf,.doc,.docx" required></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line"></i> Submit Application</button>
            </div>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const APP_URL = '<?php echo APP_URL; ?>';
document.querySelectorAll('.apply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const v = JSON.parse(this.dataset.vacancy);
        document.getElementById('vacancyId').value = v.id;
        document.getElementById('jobTitleDisplay').textContent = v.job_title;
        new bootstrap.Modal(document.getElementById('applyModal')).show();
    });
});
document.getElementById('applyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch(APP_URL + 'ajax/public/submit-job-application.php', { method: 'POST', body: new FormData(this) })
        .then(r => r.json()).then(res => {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Application Submitted!', html: 'Your reference: <strong>' + res.data.application_no + '</strong><br>We will contact you soon.' });
                bootstrap.Modal.getInstance(document.getElementById('applyModal')).hide();
                this.reset();
            } else Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        });
});
</script>
</body>
</html>
