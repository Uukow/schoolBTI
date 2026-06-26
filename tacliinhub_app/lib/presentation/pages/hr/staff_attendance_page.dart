import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/hr_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class StaffAttendancePage extends StatefulWidget {
  const StaffAttendancePage({super.key});

  @override
  State<StaffAttendancePage> createState() => _StaffAttendancePageState();
}

class _StaffAttendancePageState extends State<StaffAttendancePage> {
  DateTime _selectedDate = DateTime.now();
  final Map<int, String> _attendanceStatus = {};
  final Map<int, String?> _checkInTimes = {};
  final Map<int, String?> _checkOutTimes = {};
  final Map<int, String?> _remarks = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadAttendance();
    });
  }

  void _loadAttendance() {
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
    
    final provider = context.read<HrProvider>();
    provider.loadStaff(userId: user.id, status: 'Active');
    provider.loadStaffAttendance(
      userId: user.id,
      date: DateFormat('yyyy-MM-dd').format(_selectedDate),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Staff Attendance',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.calendar_today),
            onPressed: () async {
              final picked = await showDatePicker(
                context: context,
                initialDate: _selectedDate,
                firstDate: DateTime.now().subtract(const Duration(days: 30)),
                lastDate: DateTime.now(),
              );
              if (picked != null) {
                setState(() {
                  _selectedDate = picked;
                });
                _loadAttendance();
              }
            },
            tooltip: 'Select Date',
          ),
        ],
      ),
      body: Column(
        children: [
          // Date Display
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Date: ${DateFormat('EEEE, MMM d, yyyy').format(_selectedDate)}',
                  style: GoogleFonts.montserrat(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                ElevatedButton.icon(
                  onPressed: _saveAllAttendance,
                  icon: const Icon(Icons.save),
                  label: const Text('Save All'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.purple,
                  ),
                ),
              ],
            ),
          ),

          // Staff List
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
                        Text(provider.error ?? 'Error loading staff'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadAttendance,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                // Initialize attendance status from existing records
                for (var attendance in provider.staffAttendance) {
                  _attendanceStatus[attendance.staffId] = attendance.status;
                  _checkInTimes[attendance.staffId] = attendance.checkIn;
                  _checkOutTimes[attendance.staffId] = attendance.checkOut;
                  _remarks[attendance.staffId] = attendance.remarks;
                }

                // Initialize missing staff
                for (var staff in provider.staff) {
                  if (!_attendanceStatus.containsKey(staff.id)) {
                    _attendanceStatus[staff.id] = 'Present';
                  }
                }

                if (provider.staff.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.people_outline,
                          size: 64,
                          color: Colors.grey[400],
                        ),
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

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: provider.staff.length,
                  itemBuilder: (context, index) {
                    final staff = provider.staff[index];
                    final status = _attendanceStatus[staff.id] ?? 'Present';
                    // Filter out "00:00" or "00:00:00" times - treat as null
                    final checkInRaw = _checkInTimes[staff.id];
                    final checkIn = (checkInRaw != null && 
                        checkInRaw.isNotEmpty && 
                        checkInRaw != '00:00' && 
                        checkInRaw != '00:00:00') ? checkInRaw : null;
                    final checkOutRaw = _checkOutTimes[staff.id];
                    final checkOut = (checkOutRaw != null && 
                        checkOutRaw.isNotEmpty && 
                        checkOutRaw != '00:00' && 
                        checkOutRaw != '00:00:00') ? checkOutRaw : null;

                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ExpansionTile(
                        leading: CircleAvatar(
                          backgroundColor: _getStatusColor(
                            status,
                          ).withOpacity(0.1),
                          child: Icon(
                            _getStatusIcon(status),
                            color: _getStatusColor(status),
                          ),
                        ),
                        title: Text(
                          staff.fullName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Text(
                          '${staff.designation}${staff.department != null ? ' - ${staff.department}' : ''}',
                        ),
                        trailing: Chip(
                          label: Text(status),
                          backgroundColor: _getStatusColor(
                            status,
                          ).withOpacity(0.1),
                          labelStyle: TextStyle(
                            color: _getStatusColor(status),
                            fontSize: 10,
                          ),
                        ),
                        children: [
                          Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              children: [
                                DropdownButtonFormField<String>(
                                  decoration: InputDecoration(
                                    labelText: 'Status *',
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
                                  initialValue: status,
                                  items: const [
                                    DropdownMenuItem(
                                      value: 'Present',
                                      child: Text('Present'),
                                    ),
                                    DropdownMenuItem(
                                      value: 'Absent',
                                      child: Text('Absent'),
                                    ),
                                    DropdownMenuItem(
                                      value: 'Late',
                                      child: Text('Late'),
                                    ),
                                    DropdownMenuItem(
                                      value: 'Half Day',
                                      child: Text('Half Day'),
                                    ),
                                    DropdownMenuItem(
                                      value: 'Leave',
                                      child: Text('Leave'),
                                    ),
                                  ],
                                  onChanged: (value) {
                                    setState(() {
                                      _attendanceStatus[staff.id] = value!;
                                    });
                                  },
                                ),
                                const SizedBox(height: 16),
                                Row(
                                  children: [
                                    Expanded(
                                      child: InkWell(
                                        onTap: () async {
                                          TimeOfDay initialTime = TimeOfDay.now();
                                          if (checkIn != null && checkIn.isNotEmpty) {
                                            try {
                                              final parts = checkIn.split(':');
                                              if (parts.length >= 2) {
                                                initialTime = TimeOfDay(
                                                  hour: int.parse(parts[0]),
                                                  minute: int.parse(parts[1]),
                                                );
                                              }
                                            } catch (e) {
                                              // If parsing fails, use current time
                                              initialTime = TimeOfDay.now();
                                            }
                                          }

                                          final picked = await showTimePicker(
                                            context: context,
                                            initialTime: initialTime,
                                            builder: (context, child) {
                                              return Theme(
                                                data: Theme.of(context).copyWith(
                                                  colorScheme: ColorScheme.light(
                                                    primary: Colors.purple,
                                                    onPrimary: Colors.white,
                                                    onSurface: Colors.black,
                                                  ),
                                                ),
                                                child: child!,
                                              );
                                            },
                                          );

                                          if (picked != null) {
                                            setState(() {
                                              _checkInTimes[staff.id] =
                                                  '${picked.hour.toString().padLeft(2, '0')}:${picked.minute.toString().padLeft(2, '0')}';
                                            });
                                          }
                                        },
                                        child: InputDecorator(
                                          decoration: InputDecoration(
                                            labelText: 'Check In',
                                            border: OutlineInputBorder(
                                              borderRadius: BorderRadius.circular(12),
                                            ),
                                            prefixIcon: const Icon(Icons.login),
                                            suffixIcon: const Icon(Icons.access_time),
                                          ),
                                          child: Text(
                                            checkIn ?? 'Select time',
                                            style: TextStyle(
                                              color: checkIn != null
                                                  ? Colors.black
                                                  : Colors.grey[600],
                                            ),
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 16),
                                    Expanded(
                                      child: InkWell(
                                        onTap: () async {
                                          TimeOfDay initialTime = TimeOfDay.now();
                                          if (checkOut != null && checkOut.isNotEmpty) {
                                            try {
                                              final parts = checkOut.split(':');
                                              if (parts.length >= 2) {
                                                initialTime = TimeOfDay(
                                                  hour: int.parse(parts[0]),
                                                  minute: int.parse(parts[1]),
                                                );
                                              }
                                            } catch (e) {
                                              // If parsing fails, use current time
                                              initialTime = TimeOfDay.now();
                                            }
                                          }

                                          final picked = await showTimePicker(
                                            context: context,
                                            initialTime: initialTime,
                                            builder: (context, child) {
                                              return Theme(
                                                data: Theme.of(context).copyWith(
                                                  colorScheme: ColorScheme.light(
                                                    primary: Colors.purple,
                                                    onPrimary: Colors.white,
                                                    onSurface: Colors.black,
                                                  ),
                                                ),
                                                child: child!,
                                              );
                                            },
                                          );

                                          if (picked != null) {
                                            setState(() {
                                              _checkOutTimes[staff.id] =
                                                  '${picked.hour.toString().padLeft(2, '0')}:${picked.minute.toString().padLeft(2, '0')}';
                                            });
                                          }
                                        },
                                        child: InputDecorator(
                                          decoration: InputDecoration(
                                            labelText: 'Check Out',
                                            border: OutlineInputBorder(
                                              borderRadius: BorderRadius.circular(12),
                                            ),
                                            prefixIcon: const Icon(Icons.logout),
                                            suffixIcon: const Icon(Icons.access_time),
                                          ),
                                          child: Text(
                                            checkOut ?? 'Select time',
                                            style: TextStyle(
                                              color: checkOut != null
                                                  ? Colors.black
                                                  : Colors.grey[600],
                                            ),
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 16),
                                TextFormField(
                                  decoration: InputDecoration(
                                    labelText: 'Remarks',
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
                                  initialValue: _remarks[staff.id],
                                  onChanged: (value) {
                                    _remarks[staff.id] = value.isEmpty
                                        ? null
                                        : value;
                                  },
                                  maxLines: 2,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Present':
        return Colors.green;
      case 'Absent':
        return Colors.red;
      case 'Late':
        return Colors.orange;
      case 'Half Day':
        return Colors.blue;
      case 'Leave':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'Present':
        return Icons.check_circle;
      case 'Absent':
        return Icons.cancel;
      case 'Late':
        return Icons.access_time;
      case 'Half Day':
        return Icons.hourglass_empty;
      case 'Leave':
        return Icons.event_busy;
      default:
        return Icons.help;
    }
  }

  Future<void> _saveAllAttendance() async {
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
    
    final provider = context.read<HrProvider>();
    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);

    // Show loading
    if (mounted) {
      SweetAlert.showLoading(
        context: context,
        message: 'Saving attendance...',
      );
    }

    int successCount = 0;
    int failCount = 0;

    try {
      for (var staff in provider.staff) {
        final status = _attendanceStatus[staff.id] ?? 'Present';
        final checkIn = _checkInTimes[staff.id];
        final checkOut = _checkOutTimes[staff.id];
        final remarks = _remarks[staff.id];

        final success = await provider.saveStaffAttendance(
          staffId: staff.id,
          attendanceDate: dateStr,
          checkIn: checkIn,
          checkOut: checkOut,
          status: status,
          remarks: remarks,
          userId: user.id,
        );

        if (success) {
          successCount++;
        } else {
          failCount++;
        }
      }

      // Dismiss loading
      if (mounted) {
        Navigator.of(context, rootNavigator: true).pop();
      }

      if (mounted) {
        if (failCount == 0) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'All attendance saved successfully!',
            onConfirm: () {
              _loadAttendance();
            },
          );
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Partial Success',
            message: 'Saved: $successCount, Failed: $failCount',
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
          message: 'Failed to save attendance: ${e.toString()}',
        );
      }
    }
  }
}
