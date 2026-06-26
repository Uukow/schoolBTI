import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/attendance_provider.dart';
import '../../../core/sweet_alert.dart';

class StaffAttendancePage extends StatefulWidget {
  const StaffAttendancePage({super.key});

  @override
  State<StaffAttendancePage> createState() => _StaffAttendancePageState();
}

class _StaffAttendancePageState extends State<StaffAttendancePage> {
  DateTime _selectedDate = DateTime.now();
  final Map<int, String> _attendanceStatus = {}; // staff_id => status
  final Map<int, String> _checkIn = {}; // staff_id => check_in time
  final Map<int, String> _checkOut = {}; // staff_id => check_out time
  final Map<int, String> _remarks = {}; // staff_id => remarks
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _loadAttendance();
  }

  void _loadAttendance() {
    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
    context.read<AttendanceProvider>().loadStaffAttendance(date: dateStr);
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (picked != null) {
      setState(() {
        _selectedDate = picked;
        _attendanceStatus.clear();
        _checkIn.clear();
        _checkOut.clear();
        _remarks.clear();
      });
      _loadAttendance();
    }
  }

  Future<void> _selectTime(BuildContext context, int staffId, bool isCheckIn) async {
    final picked = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.now(),
    );
    if (picked != null) {
      setState(() {
        final timeStr = '${picked.hour.toString().padLeft(2, '0')}:${picked.minute.toString().padLeft(2, '0')}:00';
        if (isCheckIn) {
          _checkIn[staffId] = timeStr;
        } else {
          _checkOut[staffId] = timeStr;
        }
      });
    }
  }

  Future<void> _saveAttendance() async {
    if (_attendanceStatus.isEmpty) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please mark attendance for at least one staff member',
      );
      return;
    }

    setState(() {
      _isSaving = true;
    });

    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      final staff = _attendanceStatus.entries.map((entry) {
        return {
          'id': entry.key,
          'staff_id': entry.key,
          'status': entry.value,
          'check_in': _checkIn[entry.key],
          'check_out': _checkOut[entry.key],
          'remarks': _remarks[entry.key] ?? '',
        };
      }).toList();

      final success = await context.read<AttendanceProvider>().saveStaffAttendance(
            date: dateStr,
            staff: staff,
          );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Staff attendance saved successfully!',
          );
        } else {
          final error = context.read<AttendanceProvider>().error ?? 'Failed to save attendance';
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: error,
          );
        }
      }
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to save attendance: ${e.toString()}',
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSaving = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Mark Staff Attendance',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Date Picker
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: InkWell(
              onTap: _selectDate,
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey[300]!),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today),
                    const SizedBox(width: 12),
                    Text(
                      DateFormat('EEEE, MMMM d, yyyy').format(_selectedDate),
                      style: GoogleFonts.montserrat(
                        fontSize: 16,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Staff List with Attendance
          Expanded(
            child: Consumer<AttendanceProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: Colors.red),
                        const SizedBox(height: 16),
                        Text(provider.error ?? 'Error loading attendance'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadAttendance,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.staffAttendance.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.people_outline, size: 64, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        Text(
                          'No staff found',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                // Initialize attendance status from loaded data
                for (var attendance in provider.staffAttendance) {
                  if (!_attendanceStatus.containsKey(attendance.staffId)) {
                    _attendanceStatus[attendance.staffId] = attendance.status;
                    if (attendance.checkIn != null) {
                      _checkIn[attendance.staffId] = attendance.checkIn!;
                    }
                    if (attendance.checkOut != null) {
                      _checkOut[attendance.staffId] = attendance.checkOut!;
                    }
                    if (attendance.remarks != null) {
                      _remarks[attendance.staffId] = attendance.remarks!;
                    }
                  }
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: provider.staffAttendance.length,
                  itemBuilder: (context, index) {
                    final attendance = provider.staffAttendance[index];
                    final currentStatus = _attendanceStatus[attendance.staffId] ?? 'Present';

                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                CircleAvatar(
                                  backgroundColor: Colors.purple.withOpacity(0.1),
                                  child: Text(
                                    attendance.staffName[0].toUpperCase(),
                                    style: TextStyle(
                                      color: Colors.purple,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        attendance.staffName,
                                        style: GoogleFonts.montserrat(
                                          fontWeight: FontWeight.w600,
                                          fontSize: 16,
                                        ),
                                      ),
                                      Text(
                                        'ID: ${attendance.staffIdNumber ?? attendance.staffId}',
                                        style: GoogleFonts.montserrat(
                                          fontSize: 12,
                                          color: Colors.grey[600],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                // Status Dropdown
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8),
                                  decoration: BoxDecoration(
                                    border: Border.all(color: Colors.grey[300]!),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: DropdownButton<String>(
                                    value: currentStatus,
                                    underline: const SizedBox(),
                                    items: ['Present', 'Absent', 'Late', 'Half Day', 'Leave']
                                        .map((status) {
                                      return DropdownMenuItem<String>(
                                        value: status,
                                        child: Text(
                                          status,
                                          style: GoogleFonts.montserrat(fontSize: 12),
                                        ),
                                      );
                                    }).toList(),
                                    onChanged: (value) {
                                      setState(() {
                                        _attendanceStatus[attendance.staffId] = value!;
                                      });
                                    },
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            // Check In/Out Times
                            Row(
                              children: [
                                Expanded(
                                  child: InkWell(
                                    onTap: () => _selectTime(context, attendance.staffId, true),
                                    child: Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        border: Border.all(color: Colors.grey[300]!),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          const Icon(Icons.login, size: 16),
                                          const SizedBox(width: 8),
                                          Text(
                                            _checkIn[attendance.staffId] ?? 'Check In',
                                            style: GoogleFonts.montserrat(fontSize: 12),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: InkWell(
                                    onTap: () => _selectTime(context, attendance.staffId, false),
                                    child: Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        border: Border.all(color: Colors.grey[300]!),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          const Icon(Icons.logout, size: 16),
                                          const SizedBox(width: 8),
                                          Text(
                                            _checkOut[attendance.staffId] ?? 'Check Out',
                                            style: GoogleFonts.montserrat(fontSize: 12),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            // Remarks field
                            TextField(
                              decoration: InputDecoration(
                                hintText: 'Remarks (optional)',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 8,
                                ),
                                isDense: true,
                              ),
                              style: GoogleFonts.montserrat(fontSize: 12),
                              onChanged: (value) {
                                _remarks[attendance.staffId] = value;
                              },
                              controller: TextEditingController(
                                text: _remarks[attendance.staffId] ?? '',
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),

          // Save Button
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isSaving ? null : _saveAttendance,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.purple,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: _isSaving
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : Text(
                        'Save Attendance',
                        style: GoogleFonts.montserrat(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                        ),
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

