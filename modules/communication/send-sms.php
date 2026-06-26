<?php
/**
 * Send SMS
 * 
 * Send SMS to students, parents, or staff
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

requireLogin();
requireRole(['Super Admin', 'Admin', 'Teacher']);

$pageTitle = 'Send SMS';

// Get current user
$currentUser = getCurrentUser();

// Get recipients
$recipientType = $_GET['type'] ?? 'all'; // all, students, parents, staff, custom

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
            WHERE communication_type = 'SMS' 
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
                        <h4 class="page-title">Send SMS</h4>
                    </div>
                </div>
            </div>

            <!-- Send SMS Form -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Compose SMS</h4>
                            
                            <form id="sendSmsForm">
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
                                                           value="<?php echo htmlspecialchars($student['phone']); ?>" 
                                                           id="student_<?php echo $student['id']; ?>">
                                                    <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' - ' . $student['phone']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Staff:</strong>
                                            <?php foreach ($staff as $s): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="recipients[]" 
                                                           value="<?php echo htmlspecialchars($s['phone']); ?>" 
                                                           id="staff_<?php echo $s['id']; ?>">
                                                    <label class="form-check-label" for="staff_<?php echo $s['id']; ?>">
                                                        <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name'] . ' - ' . $s['phone']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label required">Message</label>
                                    <textarea class="form-control" name="message" rows="5" required 
                                              placeholder="Type your SMS message here..." maxlength="160"></textarea>
                                    <small class="text-muted">
                                        <span id="charCount">0</span>/160 characters
                                    </small>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="ri-send-plane-line"></i> Send SMS
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
                                <i class="ri-information-line"></i> SMS messages are limited to 160 characters.
                            </p>
                            <p class="text-muted">
                                <i class="ri-information-line"></i> All SMS are logged for record keeping.
                            </p>
                            <p class="text-muted">
                                <i class="ri-information-line"></i> Make sure SMS gateway is configured.
                            </p>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Recent SMS</h5>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($logs, 0, 5) as $log): ?>
                                <div class="list-group-item px-0">
                                    <small class="text-muted"><?php echo formatDateTime($log['sent_at']); ?></small>
                                    <p class="mb-1"><?php echo htmlspecialchars(substr($log['message'], 0, 50)); ?>...</p>
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

// Character count
$('textarea[name="message"]').on('input', function() {
    const count = $(this).val().length;
    $('#charCount').text(count);
    if (count > 160) {
        $('#charCount').addClass('text-danger');
    } else {
        $('#charCount').removeClass('text-danger');
    }
});

// Send SMS
$('#sendSmsForm').on('submit', function(e) {
    e.preventDefault();
    
    const recipientType = $('#recipientType').val();
    const message = $('textarea[name="message"]').val();
    
    if (message.length > 160) {
        Swal.fire({
            icon: 'error',
            title: 'Message Too Long',
            text: 'SMS messages cannot exceed 160 characters.'
        });
        return;
    }
    
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
        title: 'Send SMS?',
        text: recipientType == 'custom' ? 
            `Send SMS to ${recipients.length} recipient(s)?` : 
            `Send SMS to all ${recipientType}?`,
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
                url: '<?php echo APP_URL; ?>ajax/communication/send-sms.php',
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

