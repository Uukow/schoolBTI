import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/hr_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class ApplyLeavePage extends StatefulWidget {
  const ApplyLeavePage({super.key});

  @override
  State<ApplyLeavePage> createState() => _ApplyLeavePageState();
}

class _ApplyLeavePageState extends State<ApplyLeavePage> {
  final _formKey = GlobalKey<FormState>();
  final _reasonController = TextEditingController();

  int? _selectedStaffId;
  int? _selectedLeaveTypeId;
  DateTime _startDate = DateTime.now();
  DateTime _endDate = DateTime.now().add(const Duration(days: 1));
  int _totalDays = 1;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<HrProvider>();
      provider.loadStaff(userId: user?.id, status: 'Active');
      provider.loadLeaveTypes(userId: user?.id);
    });
  }

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Apply Leave',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Card(
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Leave Application',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Consumer<HrProvider>(
                        builder: (context, provider, child) {
                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select Staff *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.person),
                            ),
                            initialValue: _selectedStaffId,
                            items: provider.staff.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No staff available'),
                                    ),
                                  ]
                                : provider.staff.map((staff) {
                                    return DropdownMenuItem<int>(
                                      value: staff.id,
                                      child: Text(
                                        '${staff.fullName} (${staff.designation})',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: provider.staff.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedStaffId = value;
                                    });
                                  },
                            validator: (value) =>
                                value == null ? 'Please select staff' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      Consumer<HrProvider>(
                        builder: (context, provider, child) {
                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Leave Type *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.event_note),
                            ),
                            initialValue: _selectedLeaveTypeId,
                            items: provider.leaveTypes.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No leave types available'),
                                    ),
                                  ]
                                : provider.leaveTypes.map((type) {
                                    return DropdownMenuItem<int>(
                                      value: type.id,
                                      child: Text(
                                        '${type.leaveName} (${type.leaveCode}${type.daysAllowed != null ? ' - ${type.daysAllowed} days' : ''})',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: provider.leaveTypes.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedLeaveTypeId = value;
                                    });
                                  },
                            validator: (value) => value == null
                                ? 'Please select leave type'
                                : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: InkWell(
                              onTap: () async {
                                final picked = await showDatePicker(
                                  context: context,
                                  initialDate: _startDate,
                                  firstDate: DateTime.now(),
                                  lastDate: DateTime.now().add(
                                    const Duration(days: 365),
                                  ),
                                );
                                if (picked != null) {
                                  setState(() {
                                    _startDate = picked;
                                    if (_endDate.isBefore(_startDate)) {
                                      _endDate = _startDate.add(
                                        const Duration(days: 1),
                                      );
                                    }
                                    _calculateDays();
                                  });
                                }
                              },
                              child: InputDecorator(
                                decoration: InputDecoration(
                                  labelText: 'Start Date *',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  prefixIcon: const Icon(Icons.calendar_today),
                                ),
                                child: Text(
                                  DateFormat('yyyy-MM-dd').format(_startDate),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: InkWell(
                              onTap: () async {
                                final picked = await showDatePicker(
                                  context: context,
                                  initialDate: _endDate,
                                  firstDate: _startDate,
                                  lastDate: DateTime.now().add(
                                    const Duration(days: 365),
                                  ),
                                );
                                if (picked != null) {
                                  setState(() {
                                    _endDate = picked;
                                    _calculateDays();
                                  });
                                }
                              },
                              child: InputDecorator(
                                decoration: InputDecoration(
                                  labelText: 'End Date *',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  prefixIcon: const Icon(Icons.event),
                                ),
                                child: Text(
                                  DateFormat('yyyy-MM-dd').format(_endDate),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.blue.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Total Days:',
                              style: GoogleFonts.montserrat(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              '$_totalDays',
                              style: GoogleFonts.montserrat(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.blue,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _reasonController,
                        decoration: InputDecoration(
                          labelText: 'Reason *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        maxLines: 4,
                        validator: (value) => value?.isEmpty ?? true
                            ? 'Please enter reason'
                            : null,
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _submitForm,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.orange,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Apply Leave',
                            style: GoogleFonts.montserrat(
                              fontSize: 16,
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _calculateDays() {
    final difference = _endDate.difference(_startDate).inDays + 1;
    setState(() {
      _totalDays = difference > 0 ? difference : 1;
    });
  }

  Future<void> _submitForm() async {
    if (_formKey.currentState!.validate()) {
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

      // Show loading dialog
      if (mounted) {
        SweetAlert.showLoading(
          context: context,
          message: 'Submitting leave application...',
        );
      }

      try {
        final provider = context.read<HrProvider>();

        final success = await provider.applyLeave(
          staffId: _selectedStaffId!,
          leaveTypeId: _selectedLeaveTypeId!,
          startDate: DateFormat('yyyy-MM-dd').format(_startDate),
          endDate: DateFormat('yyyy-MM-dd').format(_endDate),
          totalDays: _totalDays,
          reason: _reasonController.text,
          userId: user.id,
        );

        // Dismiss loading dialog
        if (mounted) {
          Navigator.of(context, rootNavigator: true).pop();
        }

        if (mounted) {
          if (success) {
            SweetAlert.showSuccess(
              context: context,
              title: 'Success!',
              message: 'Leave application has been submitted successfully.',
              onConfirm: () {
                Navigator.pop(context);
              },
            );
          } else {
            SweetAlert.showError(
              context: context,
              title: 'Submission Failed',
              message: provider.error ?? 'Failed to submit leave application. Please try again.',
            );
          }
        }
      } catch (e) {
        // Dismiss loading dialog
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
    }
  }
}
