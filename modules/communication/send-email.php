<?php
/**
 * Send Email
 * 
 * Send emails to students, parents, or staff
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Send Email';

// Get current user
$currentUser = getCurrentUser();

// Get students
$studentsSql = "SELECT s.*, c.class_name FROM students s 
                LEFT JOIN classes c ON s.current_class_id = c.id 
                WHERE s.status = 'Active' 
                ORDER BY s.first_name";
$students = fetchAll(executeQuery($studentsSql));

// Get staff
$staffSql = "SELECT * FROM staff WHERE status = 'Active' ORDER BY first_name";
$staff = fetchAll(executeQuery($staffSql));

// Get communication logs
$logsSql = "SELECT * FROM communication_logs 
            WHERE communication_type = 'Email' 
            ORDER BY sent_at DESC 
            LIMIT 50";
$logs = fetchAll(executeQuery($logsSql));

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Send Email</h4>
                    </div>
                </div>
            </div>

            <!-- Send Email Form -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Compose Email</h4>
                            
                            <form id="sendEmailForm">
                                <div class="mb-3">
                                    <label class="form-label required">Recipient Type</label>
                                    <select class="form-select" name="recipient_type" id="recipientType" required>
                                        <option value="all">All (Students + Parents + Staff)</option>
                                        <option value="students">Students Only</option>
                                        <option value="parents">Parents Only</option>
                                        <option value="staff">Staff Only</option>
                                        <option value="custom">Custom Recipients</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3" id="customRecipients" style="display: none;">
                                    <label class="form-label">Select Recipients</label>
                                    <div class="border p-3" style="max-height: 300px; overflow-y: auto;">
                                        <div class="mb-2">
                                            <strong>Students:</strong>
                                            <?php foreach ($students as $student): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="recipients[]" 
                                                           value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" 
                                                           id="student_<?php echo $student['id']; ?>"
                                                           <?php echo empty($student['email']) ? 'disabled' : ''; ?>>
                                                    <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                                        <?php if ($student['email']): ?>
                                                            - <?php echo htmlspecialchars($student['email']); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">(No email)</span>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Staff:</strong>
                                            <?php foreach ($staff as $s): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="recipients[]" 
                                                           value="<?php echo htmlspecialchars($s['email'] ?? ''); ?>" 
                                                           id="staff_<?php echo $s['id']; ?>"
                                                           <?php echo empty($s['email']) ? 'disabled' : ''; ?>>
                                                    <label class="form-check-label" for="staff_<?php echo $s['id']; ?>">
                                                        <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?>
                                                        <?php if ($s['email']): ?>
                                                            - <?php echo htmlspecialchars($s['email']); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">(No email)</span>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label required">Subject</label>
                                    <input type="text" class="form-control" name="subject" required placeholder="Email subject">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label required">Message</label>
                                    <textarea class="form-control" name="message" rows="8" required 
                                              placeholder="Type your email message here..."></textarea>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-mail-send-line"></i> Send Email
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Quick Info</h5>
                            <p class="text-muted">
                                <i class="ri-information-line"></i> Emails are sent using PHPMailer.
                            </p>
                            <p class="text-muted">
                                <i class="ri-information-line"></i> All emails are logged for record keeping.
                            </p>
                            <p class="text-muted">
                                <i class="ri-information-line"></i> Make sure SMTP is configured in settings.
                            </p>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Recent Emails</h5>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($logs, 0, 5) as $log): ?>
                                <div class="list-group-item px-0">
                                    <small class="text-muted"><?php echo formatDateTime($log['sent_at']); ?></small>
                                    <p class="mb-1"><strong><?php echo htmlspecialchars($log['subject']); ?></strong></p>
                                    <p class="mb-1 small"><?php echo htmlspecialchars(substr($log['message'], 0, 50)); ?>...</p>
                                    <span class="badge bg-<?php echo $log['status'] == 'Sent' ? 'success' : 'danger'; ?>">
                                        <?php echo htmlspecialchars($log['status']); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php include '../../includes/footer.php'; ?>

<script>
// Toggle custom recipients
$('#recipientType').on('change', function() {
    if ($(this).val() == 'custom') {
        $('#customRecipients').show();
    } else {
        $('#customRecipients').hide();
    }
});

// Send Email
$('#sendEmailForm').on('submit', function(e) {
    e.preventDefault();
    
    const recipientType = $('#recipientType').val();
    const subject = $('input[name="subject"]').val();
    const message = $('textarea[name="message"]').val();
    
    let recipients = [];
    if (recipientType == 'custom') {
        $('input[name="recipients[]"]:checked').each(function() {
            recipients.push($(this).val());
        });
        if (recipients.length == 0) {
            Swal.fire({
                icon: 'error',
                title: 'No Recipients',
                text: 'Please select at least one recipient.'
            });
            return;
        }
    }
    
    Swal.fire({
        title: 'Send Email?',
        text: recipientType == 'custom' ? 
            `Send email to ${recipients.length} recipient(s)?` : 
            `Send email to all ${recipientType}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Send!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = $(this).serialize();
            if (recipientType == 'custom') {
                formData += '&custom_recipients=' + JSON.stringify(recipients);
            }
            
            $.ajax({
                url: '<?php echo APP_URL; ?>ajax/communication/send-email.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sent!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                }
            });
        }
    });
});
</script>

