import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:awesome_dialog/awesome_dialog.dart';
import '../../providers/hr_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class LeaveManagementPage extends StatefulWidget {
  const LeaveManagementPage({super.key});

  @override
  State<LeaveManagementPage> createState() => _LeaveManagementPageState();
}

class _LeaveManagementPageState extends State<LeaveManagementPage> {
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<HrProvider>();
      provider.loadLeaveApplications(userId: user?.id);
      provider.loadLeaveTypes(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Leave Management',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.pushNamed(context, '/hr/leave/apply'),
            tooltip: 'Apply Leave',
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: DropdownButtonFormField<String>(
              decoration: InputDecoration(
                labelText: 'Filter by Status',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                prefixIcon: const Icon(Icons.filter_list),
              ),
              initialValue: _selectedStatus,
              items: const [
                DropdownMenuItem<String>(
                  value: null,
                  child: Text('All Statuses'),
                ),
                DropdownMenuItem(value: 'Pending', child: Text('Pending')),
                DropdownMenuItem(value: 'Approved', child: Text('Approved')),
                DropdownMenuItem(value: 'Rejected', child: Text('Rejected')),
                DropdownMenuItem(value: 'Cancelled', child: Text('Cancelled')),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedStatus = value;
                });
                _loadApplications();
              },
            ),
          ),

          // Leave Applications List
          Expanded(
            child: Consumer<HrProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 48,
                          color: Colors.red,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          provider.error ?? 'Error loading leave applications',
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadApplications,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var applications = provider.leaveApplications;
                if (_selectedStatus != null) {
                  applications = applications
                      .where((app) => app.status == _selectedStatus)
                      .toList();
                }

                if (applications.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.event_note_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No leave applications found',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: applications.length,
                  itemBuilder: (context, index) {
                    final application = applications[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                CircleAvatar(
                                  backgroundColor: _getStatusColor(
                                    application.status,
                                  ).withOpacity(0.1),
                                  child: Icon(
                                    Icons.event_note,
                                    color: _getStatusColor(application.status),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Expanded(
                                            child: Text(
                                              application.staffName,
                                              style: GoogleFonts.montserrat(
                                                fontWeight: FontWeight.w600,
                                                fontSize: 16,
                                              ),
                                            ),
                                          ),
                                          Chip(
                                            label: Text(application.status),
                                            backgroundColor: _getStatusColor(
                                              application.status,
                                            ).withOpacity(0.1),
                                            labelStyle: TextStyle(
                                              color: _getStatusColor(application.status),
                                              fontSize: 10,
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 8),
                                      Text(
                                        '${application.leaveTypeName} (${application.leaveCode})',
                                        style: GoogleFonts.montserrat(fontSize: 14),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        '${DateFormat('MMM d').format(DateTime.parse(application.startDate))} - ${DateFormat('MMM d, yyyy').format(DateTime.parse(application.endDate))}',
                                        style: GoogleFonts.montserrat(fontSize: 13),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        'Days: ${application.totalDays}',
                                        style: GoogleFonts.montserrat(fontSize: 13),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        'Applied: ${DateFormat('MMM d, yyyy').format(DateTime.parse(application.appliedAt))}',
                                        style: GoogleFonts.montserrat(fontSize: 12, color: Colors.grey[600]),
                                      ),
                                      if (application.approvalDate != null) ...[
                                        const SizedBox(height: 4),
                                        Text(
                                          'Approved: ${DateFormat('MMM d, yyyy').format(DateTime.parse(application.approvalDate!))}',
                                          style: GoogleFonts.montserrat(fontSize: 12, color: Colors.grey[600]),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            if (application.status == 'Pending') ...[
                              const SizedBox(height: 12),
                              Row(
                                children: [
                                  Expanded(
                                    child: ElevatedButton.icon(
                                      onPressed: () => _approveLeave(application.id),
                                      icon: const Icon(Icons.check, size: 18),
                                      label: const Text('Approve'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.green,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: ElevatedButton.icon(
                                      onPressed: () => _rejectLeave(application.id),
                                      icon: const Icon(Icons.close, size: 18),
                                      label: const Text('Reject'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.red,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                            if (application.status == 'Approved' || application.status == 'Rejected') ...[
                              const SizedBox(height: 12),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton.icon(
                                  onPressed: () => _cancelLeave(application.id),
                                  icon: const Icon(Icons.cancel, size: 18),
                                  label: const Text('Cancel Leave'),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.grey,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 12),
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.pushNamed(context, '/hr/leave/apply'),
        backgroundColor: Colors.orange,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadApplications() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    context.read<HrProvider>().loadLeaveApplications(
      userId: user?.id,
      status: _selectedStatus,
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Approved':
        return Colors.green;
      case 'Rejected':
        return Colors.red;
      case 'Pending':
        return Colors.orange;
      case 'Cancelled':
        return Colors.grey;
      default:
        return Colors.grey;
    }
  }

  Future<void> _approveLeave(int applicationId) async {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    
    if (user == null) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Authentication Error',
          message: 'User not logged in. Please login again.',
        );
      }
      return;
    }

    // Show confirmation dialog
    SweetAlert.showConfirmation(
      context: context,
      title: 'Approve Leave',
      message: 'Are you sure you want to approve this leave application?',
      confirmText: 'Approve',
      cancelText: 'Cancel',
      confirmColor: Colors.green,
      onConfirm: () async {
        // Show loading
        if (mounted) {
          SweetAlert.showLoading(
            context: context,
            message: 'Approving leave...',
          );
        }

        try {
          final success = await context.read<HrProvider>().updateLeaveStatus(
            applicationId: applicationId,
            status: 'Approved',
            userId: user.id,
          );

          // Dismiss loading
          if (mounted) {
            Navigator.of(context, rootNavigator: true).pop();
          }

          if (mounted) {
            if (success) {
              SweetAlert.showSuccess(
                context: context,
                title: 'Success!',
                message: 'Leave application has been approved successfully.',
                onConfirm: () {
                  _loadApplications();
                },
              );
            } else {
              SweetAlert.showError(
                context: context,
                title: 'Approval Failed',
                message: context.read<HrProvider>().error ?? 'Failed to approve leave. Please try again.',
              );
            }
          }
        } catch (e) {
          // Dismiss loading
          if (mounted) {
            Navigator.of(context, rootNavigator: true).pop();
          }

          if (mounted) {
            SweetAlert.showError(
              context: context,
              title: 'Error',
              message: 'An unexpected error occurred. Please try again.',
            );
          }
        }
      },
    );
  }

  Future<void> _rejectLeave(int applicationId) async {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    
    if (user == null) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Authentication Error',
          message: 'User not logged in. Please login again.',
        );
      }
      return;
    }

    // Show confirmation dialog with input for rejection reason
    final rejectionReasonController = TextEditingController();
    
    SweetAlert.showInputDialog(
      context: context,
      title: 'Reject Leave',
      message: 'Please provide a reason for rejection:',
      controller: rejectionReasonController,
      hint: 'Enter rejection reason',
      maxLines: 3,
      dialogType: DialogType.warning,
      confirmText: 'Reject',
      cancelText: 'Cancel',
      onConfirm: () async {
        if (rejectionReasonController.text.trim().isEmpty) {
          SweetAlert.showError(
            context: context,
            title: 'Validation Error',
            message: 'Please provide a reason for rejection.',
          );
          return;
        }

        // Show loading
        if (mounted) {
          SweetAlert.showLoading(
            context: context,
            message: 'Rejecting leave...',
          );
        }

        try {
          final success = await context.read<HrProvider>().updateLeaveStatus(
            applicationId: applicationId,
            status: 'Rejected',
            rejectionReason: rejectionReasonController.text.trim(),
            userId: user.id,
          );

          // Dismiss loading
          if (mounted) {
            Navigator.of(context, rootNavigator: true).pop();
          }

          if (mounted) {
            if (success) {
              SweetAlert.showSuccess(
                context: context,
                title: 'Success!',
                message: 'Leave application has been rejected.',
                onConfirm: () {
                  _loadApplications();
                },
              );
            } else {
              SweetAlert.showError(
                context: context,
                title: 'Rejection Failed',
                message: context.read<HrProvider>().error ?? 'Failed to reject leave. Please try again.',
              );
            }
          }
        } catch (e) {
          // Dismiss loading
          if (mounted) {
            Navigator.of(context, rootNavigator: true).pop();
          }

          if (mounted) {
            SweetAlert.showError(
              context: context,
              title: 'Error',
              message: 'An unexpected error occurred. Please try again.',
            );
          }
        }
      },
    );
  }

  Future<void> _cancelLeave(int applicationId) async {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    
    if (user == null) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Authentication Error',
          message: 'User not logged in. Please login again.',
        );
      }
      return;
    }

    // Show confirmation dialog
    SweetAlert.showConfirmation(
      context: context,
      title: 'Cancel Leave',
      message: 'Are you sure you want to cancel this leave application?',
      confirmText: 'Cancel Leave',
      cancelText: 'No',
      confirmColor: Colors.grey,
      onConfirm: () async {
        // Show loading
        if (mounted) {
          SweetAlert.showLoading(
            context: context,
            message: 'Cancelling leave...',
          );
        }

        try {
          final success = await context.read<HrProvider>().updateLeaveStatus(
            applicationId: applicationId,
            status: 'Cancelled',
            userId: user.id,
          );

          // Dismiss loading
          if (mounted) {
            Navigator.of(context, rootNavigator: true).pop();
          }

          if (mounted) {
            if (success) {
              SweetAlert.showSuccess(
                context: context,
                title: 'Success!',
                message: 'Leave application has been cancelled.',
                onConfirm: () {
                  _loadApplications();
                },
              );
            } else {
              SweetAlert.showError(
                context: context,
                title: 'Cancellation Failed',
                message: context.read<HrProvider>().error ?? 'Failed to cancel leave. Please try again.',
              );
            }
          }
        } catch (e) {
          // Dismiss loading
          if (mounted) {
            Navigator.of(context, rootNavigator: true).pop();
          }

          if (mounted) {
            SweetAlert.showError(
              context: context,
              title: 'Error',
              message: 'An unexpected error occurred. Please try again.',
            );
          }
        }
      },
    );
  }
}
