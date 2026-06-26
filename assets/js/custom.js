/**
 * Custom JavaScript for School ERP System
 * 
 * Global functions and AJAX handlers
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // ========================================
    // Global Variables
    // ========================================
    
    // Use APP_URL from PHP if defined, otherwise auto-detect
    const APP_URL = (typeof window.APP_URL !== 'undefined' && window.APP_URL) 
        ? window.APP_URL 
        : (function() {
            // Auto-detect base URL
            const origin = window.location.origin;
            const pathname = window.location.pathname;
            
            // Check if pathname contains /bti (local development)
            if (pathname.indexOf('/bti') !== -1) {
                return origin + '/bti/';
            }
            
            // For production, use root path
            return origin + '/';
        })();

    // ========================================
    // Document Ready
    // ========================================
    
    $(document).ready(function() {
        // Initialize tooltips
        initTooltips();
        
        // Initialize popovers
        initPopovers();
        
        // Initialize DataTables
        initDataTables();
        
        // Initialize form validation
        initFormValidation();
        
        // Setup AJAX defaults
        setupAjax();
    });

    // ========================================
    // Initialize Bootstrap Components
    // ========================================
    
    function initTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    function initPopovers() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    // ========================================
    // DataTables Initialization
    // ========================================
    
    function initDataTables() {
        if ($.fn.DataTable) {
            $('.datatable').DataTable({
                responsive: true,
                language: {
                    paginate: {
                        previous: "<i class='ri-arrow-left-s-line'></i>",
                        next: "<i class='ri-arrow-right-s-line'></i>"
                    },
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                pageLength: 25,
                order: [[0, 'asc']]
            });

            // DataTable with export buttons
            $('.datatable-export').each(function() {
                var $table = $(this);
                
                // Remove any rows with colspan (like "No data" messages) before initializing
                $table.find('tbody tr').each(function() {
                    var $row = $(this);
                    var $tdWithColspan = $row.find('td[colspan]');
                    if ($tdWithColspan.length > 0) {
                        $row.remove();
                    }
                });
                
                // Check if table has data rows after cleanup
                var dataRows = $table.find('tbody tr').filter(function() {
                    return $(this).find('td').length > 0 && !$(this).find('td[colspan]').length;
                });
                var hasData = dataRows.length > 0;
                
                // Get header column count
                var headerCols = $table.find('thead tr:first th').length;
                
                // Only initialize if table has data and proper structure
                if (hasData) {
                    // Verify all rows have correct column count
                    var allRowsValid = true;
                    dataRows.each(function() {
                        var rowCols = $(this).find('td').length;
                        if (rowCols !== headerCols) {
                            allRowsValid = false;
                            console.warn('Row column mismatch:', {
                                expected: headerCols,
                                actual: rowCols,
                                row: $(this).html().substring(0, 100)
                            });
                        }
                    });
                    
                    if (allRowsValid) {
                        try {
                            $table.DataTable({
                                responsive: true,
                                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>Brtip',
                                buttons: [
                                    {
                                        extend: 'copy',
                                        className: 'btn btn-sm btn-secondary'
                                    },
                                    {
                                        extend: 'csv',
                                        className: 'btn btn-sm btn-secondary'
                                    },
                                    {
                                        extend: 'excel',
                                        className: 'btn btn-sm btn-secondary'
                                    },
                                    {
                                        extend: 'pdf',
                                        className: 'btn btn-sm btn-secondary'
                                    },
                                    {
                                        extend: 'print',
                                        className: 'btn btn-sm btn-secondary'
                                    }
                                ],
                                language: {
                                    paginate: {
                                        previous: "<i class='ri-arrow-left-s-line'></i>",
                                        next: "<i class='ri-arrow-right-s-line'></i>"
                                    },
                                    emptyTable: "No data available in table"
                                },
                                pageLength: 25,
                                columnDefs: [
                                    {
                                        // Handle empty cells
                                        defaultContent: "-",
                                        targets: "_all"
                                    }
                                ]
                            });
                        } catch (e) {
                            console.error('DataTables initialization error:', e);
                            console.error('Table ID:', $table.attr('id') || 'unnamed');
                        }
                    } else {
                        console.warn('DataTables not initialized due to column mismatches in table:', $table.attr('id') || 'unnamed');
                    }
                } else {
                    // Table is empty, add empty message if not already present
                    if ($table.find('tbody tr').length === 0) {
                        $table.find('tbody').html('<tr><td colspan="' + headerCols + '" class="text-center text-muted">No data available</td></tr>');
                    }
                }
            });
        }
    }

    // ========================================
    // Form Validation
    // ========================================
    
    function initFormValidation() {
        // Bootstrap validation
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }

    // ========================================
    // AJAX Setup
    // ========================================
    
    function setupAjax() {
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function() {
                showLoader();
            },
            complete: function() {
                hideLoader();
            },
            error: function(xhr, status, error) {
                hideLoader();
                if (xhr.status === 401) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Session Expired',
                        text: 'Your session has expired. Please login again.',
                        confirmButtonText: 'Go to Login'
                    }).then(() => {
                        window.location.href = APP_URL + 'login.php';
                    });
                } else {
                    showToast('An error occurred. Please try again.', 'error');
                }
            }
        });
    }

    // ========================================
    // Loader Functions
    // ========================================
    
    function showLoader() {
        if ($('.spinner-overlay').length === 0) {
            $('body').append(`
                <div class="spinner-overlay">
                    <div class="spinner-border text-primary spinner-border-custom" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }
    }

    function hideLoader() {
        $('.spinner-overlay').remove();
    }

    // ========================================
    // SweetAlert2 Notifications
    // ========================================
    
    window.showToast = function(message, type = 'success') {
        const iconMap = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        const icon = iconMap[type] || 'success';
        
        Swal.fire({
            icon: icon,
            title: type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : type === 'warning' ? 'Warning!' : 'Info!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    };

    // Show success message
    window.showSuccess = function(message, title = 'Success!') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    };

    // Show error message
    window.showError = function(message, title = 'Error!') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message
        });
    };

    // Show warning message
    window.showWarning = function(message, title = 'Warning!') {
        Swal.fire({
            icon: 'warning',
            title: title,
            text: message
        });
    };

    // Show info message
    window.showInfo = function(message, title = 'Info') {
        Swal.fire({
            icon: 'info',
            title: title,
            text: message
        });
    };

    // ========================================
    // SweetAlert2 Confirmation Dialog
    // ========================================
    
    window.confirmAction = function(message, callback, options = {}) {
        const defaultOptions = {
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: options.confirmText || 'Yes, proceed!',
            cancelButtonText: options.cancelText || 'Cancel',
            reverseButtons: true
        };
        
        const finalOptions = Object.assign({}, defaultOptions, options);
        
        Swal.fire(finalOptions).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    };

    // ========================================
    // Delete Record (Generic)
    // ========================================
    
    window.deleteRecord = function(url, id, redirectUrl = null) {
        confirmAction(
            'Are you sure you want to delete this record? This action cannot be undone.',
            function() {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { id: id, action: 'delete' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                if (redirectUrl) {
                                    window.location.href = redirectUrl;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            showToast(response.message, 'error');
                        }
                    },
                    error: function() {
                        showToast('Failed to delete record', 'error');
                    }
                });
            },
            {
                title: 'Delete Record?',
                icon: 'warning',
                confirmButtonText: 'Yes, delete it!',
                confirmButtonColor: '#d33'
            }
        );
    };

    // ========================================
    // Image Preview
    // ========================================
    
    window.previewImage = function(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(previewId).attr('src', e.target.result).show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // ========================================
    // Form Submission Handler (AJAX)
    // ========================================
    
    window.submitFormAjax = function(formId, successCallback) {
        $(formId).on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            if (successCallback) {
                                successCallback(response);
                            }
                        });
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function() {
                    showToast('Form submission failed', 'error');
                }
            });
        });
    };

    // ========================================
    // Print Function
    // ========================================
    
    window.printDiv = function(divId) {
        var printContents = document.getElementById(divId).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    };

    // ========================================
    // Export Table to Excel
    // ========================================
    
    window.exportTableToExcel = function(tableId, filename = 'export') {
        var table = document.getElementById(tableId);
        var html = table.outerHTML;
        var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename + '.xls';
        link.click();
    };

    // ========================================
    // Format Currency
    // ========================================
    
    window.formatCurrency = function(amount) {
        return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };

    // ========================================
    // Date Formatting
    // ========================================
    
    window.formatDate = function(dateString) {
        var date = new Date(dateString);
        var day = String(date.getDate()).padStart(2, '0');
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var year = date.getFullYear();
        return day + '-' + month + '-' + year;
    };

    // ========================================
    // Password Toggle
    // ========================================
    
    $('.toggle-password').click(function() {
        var input = $($(this).attr('toggle'));
        if (input.attr('type') == 'password') {
            input.attr('type', 'text');
            $(this).removeClass('ri-eye-off-line').addClass('ri-eye-line');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('ri-eye-line').addClass('ri-eye-off-line');
        }
    });

    // ========================================
    // Select All Checkboxes
    // ========================================
    
    $('.select-all').change(function() {
        var target = $(this).data('target');
        $(target).prop('checked', $(this).prop('checked'));
    });

    // ========================================
    // Auto-hide Alerts
    // ========================================
    
    setTimeout(function() {
        $('.alert:not(.alert-permanent)').fadeOut('slow');
    }, 5000);

})(jQuery);


