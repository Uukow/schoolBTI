<?php
/**
 * Footer Component
 * 
 * Reusable footer for all pages
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}
?>

            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <script>document.write(new Date().getFullYear())</script> © <b><?php echo APP_NAME; ?></b> - Developed with <i class="mdi mdi-heart text-danger"></i> by Uukow Technology Solutions UTECH
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-end footer-links d-none d-md-block">
                                <a href="javascript: void(0);">About</a>
                                <a href="javascript: void(0);">Support</a>
                                <a href="javascript: void(0);">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/vendor.min.js"></script>

    <!-- Daterangepicker js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/daterangepicker/moment.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Apex Charts js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Datatables js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/dataTables.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/responsive.bootstrap5.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/dataTables.buttons.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/buttons.bootstrap5.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/jszip.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/pdfmake.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/vfs_fonts.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/buttons.html5.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/datatables/buttons.print.min.js"></script>

    <!-- Vector Map js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/jsvectormap/jsvectormap.min.js"></script>
    <script src="<?php echo APP_URL; ?>template_extracted/assets/vendor/jsvectormap/world-merc.js"></script>

    <!-- App js -->
    <script src="<?php echo APP_URL; ?>template_extracted/assets/js/app.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="<?php echo APP_URL; ?>assets/js/custom.js"></script>
    
    <!-- Permissions JS -->
    <script src="<?php echo APP_URL; ?>assets/js/permissions.js"></script>
    
    <?php if (isset($additionalJS)) echo $additionalJS; ?>

    <script>
        // Base URL for AJAX requests (set globally for all scripts)
        window.APP_URL = '<?php echo APP_URL; ?>';
        const APP_URL = window.APP_URL;
        
        // Load notifications on page load
        $(document).ready(function() {
            loadNotifications();
            
            // Reload notifications every 60 seconds
            setInterval(loadNotifications, 60000);
        });

        // Load notifications via AJAX
        function loadNotifications() {
            $.ajax({
                url: APP_URL + 'ajax/get-notifications.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateNotificationList(response.data);
                    }
                },
                error: function() {
                    console.error('Failed to load notifications');
                }
            });
        }

        // Update notification list in dropdown
        function updateNotificationList(notifications) {
            const container = $('#notificationList');
            
            if (notifications.length === 0) {
                container.html('<div class="text-center p-3"><small class="text-muted">No new notifications</small></div>');
                return;
            }

            let html = '';
            notifications.forEach(function(notification) {
                html += `
                    <a href="javascript:void(0);" onclick="markAsRead(${notification.id})" class="dropdown-item p-0 notify-item card unread-noti shadow-none mb-1">
                        <div class="card-body">
                            <span class="float-end noti-close-btn text-muted"><i class="mdi mdi-close"></i></span>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="notify-icon bg-primary">
                                        <i class="ri-notification-line font-18"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-truncate ms-2">
                                    <h5 class="noti-item-title fw-semibold font-14">${notification.title}</h5>
                                    <small class="noti-item-subtitle text-muted">${notification.message}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                `;
            });
            
            container.html(html);
        }

        // Mark notification as read
        function markAsRead(notificationId) {
            $.ajax({
                url: APP_URL + 'ajax/mark-notification-read.php',
                type: 'POST',
                data: { notification_id: notificationId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadNotifications();
                    }
                }
            });
        }

        // Mark all notifications as read
        function markAllRead() {
            $.ajax({
                url: APP_URL + 'ajax/mark-all-notifications-read.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadNotifications();
                        location.reload();
                    }
                }
            });
        }

    </script>

</body>
</html>


