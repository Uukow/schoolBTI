import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/attendance_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/academic_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/constants.dart';
import '../../../core/sweet_alert.dart';

class StudentAttendancePage extends StatefulWidget {
  const StudentAttendancePage({super.key});

  @override
  State<StudentAttendancePage> createState() => _StudentAttendancePageState();
}

class _StudentAttendancePageState extends State<StudentAttendancePage> {
  int? _selectedClassId;
  int? _selectedSectionId;
  int? _selectedSubjectId;
  DateTime _selectedDate = DateTime.now();
  final Map<int, String> _attendanceStatus = {}; // student_id => status
  final Map<int, String> _remarks = {}; // student_id => remarks
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      context.read<StudentProvider>().loadClasses();
      if (user != null) {
        context.read<AcademicProvider>().loadSubjects(user.id);
      }
    });
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
      });
      _loadAttendance();
    }
  }

  void _loadAttendance() {
    if (_selectedClassId != null && _selectedSectionId != null) {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      context.read<AttendanceProvider>().loadStudentAttendance(
        classId: _selectedClassId!,
        sectionId: _selectedSectionId!,
        date: dateStr,
        subjectId: _selectedSubjectId,
      );
    }
  }

  Future<void> _saveAttendance() async {
    if (_selectedClassId == null || _selectedSectionId == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select class and section',
      );
      return;
    }

    if (_attendanceStatus.isEmpty) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please mark attendance for at least one student',
      );
      return;
    }

    setState(() {
      _isSaving = true;
    });

    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      final students = _attendanceStatus.entries.map((entry) {
        return {
          'id': entry.key,
          'student_id': entry.key,
          'status': entry.value,
          'remarks': _remarks[entry.key] ?? '',
        };
      }).toList();

      final success = await context
          .read<AttendanceProvider>()
          .saveStudentAttendance(
            classId: _selectedClassId!,
            sectionId: _selectedSectionId!,
            date: dateStr,
            students: students,
            subjectId: _selectedSubjectId,
          );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Attendance saved successfully!',
          );
        } else {
          final error =
              context.read<AttendanceProvider>().error ??
              'Failed to save attendance';
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
          'Mark Student Attendance',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: AppConstants.primaryColor,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                // Date Picker
                InkWell(
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
                          DateFormat(
                            'EEEE, MMMM d, yyyy',
                          ).format(_selectedDate),
                          style: GoogleFonts.montserrat(
                            fontSize: 16,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Class Selection
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Select Class',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.class_),
                      ),
                      initialValue: _selectedClassId,
                      items: studentProvider.classes.map((classItem) {
                        return DropdownMenuItem<int>(
                          value: classItem.id,
                          child: Text(classItem.className),
                        );
                      }).toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedClassId = value;
                          _selectedSectionId = null;
                          _attendanceStatus.clear();
                          _remarks.clear();
                        });
                        if (value != null) {
                          context.read<StudentProvider>().loadSectionsByClass(
                            value,
                          );
                        }
                      },
                    );
                  },
                ),
                if (_selectedClassId != null) ...[
                  const SizedBox(height: 16),
                  Consumer<StudentProvider>(
                    builder: (context, studentProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Select Section',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.group),
                        ),
                        initialValue: _selectedSectionId,
                        items: studentProvider.sections.map((section) {
                          return DropdownMenuItem<int>(
                            value: section.id,
                            child: Text(section.sectionName),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedSectionId = value;
                            _attendanceStatus.clear();
                            _remarks.clear();
                          });
                          if (value != null) {
                            _loadAttendance();
                          }
                        },
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  Consumer<AcademicProvider>(
                    builder: (context, academicProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Select Subject (Optional)',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.book),
                        ),
                        initialValue: _selectedSubjectId,
                        items: [
                          const DropdownMenuItem<int>(
                            value: null,
                            child: Text('All Subjects'),
                          ),
                          ...academicProvider.subjects.map((subject) {
                            return DropdownMenuItem<int>(
                              value: subject.id,
                              child: Text(subject.subjectName),
                            );
                          }),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedSubjectId = value;
                            _attendanceStatus.clear();
                            _remarks.clear();
                          });
                          _loadAttendance();
                        },
                      );
                    },
                  ),
                ],
              ],
            ),
          ),

          // Students List with Attendance
          Expanded(
            child: Consumer<AttendanceProvider>(
              builder: (context, provider, child) {
                if (_selectedClassId == null || _selectedSectionId == null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.class_, size: 64, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        Text(
                          'Please select class and section',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

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

                if (provider.studentAttendance.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.person_outline,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No students found',
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
                for (var attendance in provider.studentAttendance) {
                  if (!_attendanceStatus.containsKey(attendance.studentId)) {
                    _attendanceStatus[attendance.studentId] = attendance.status;
                    if (attendance.remarks != null) {
                      _remarks[attendance.studentId] = attendance.remarks!;
                    }
                  }
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: provider.studentAttendance.length,
                  itemBuilder: (context, index) {
                    final attendance = provider.studentAttendance[index];
                    final currentStatus =
                        _attendanceStatus[attendance.studentId] ?? 'Present';

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
                                  backgroundColor: AppConstants.primaryColor
                                      .withOpacity(0.1),
                                  child: Text(
                                    attendance.studentName[0].toUpperCase(),
                                    style: TextStyle(
                                      color: AppConstants.primaryColor,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        attendance.studentName,
                                        style: GoogleFonts.montserrat(
                                          fontWeight: FontWeight.w600,
                                          fontSize: 16,
                                        ),
                                      ),
                                      Text(
                                        'ID: ${attendance.studentIdNumber ?? attendance.studentId}',
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
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                  ),
                                  decoration: BoxDecoration(
                                    border: Border.all(
                                      color: Colors.grey[300]!,
                                    ),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: DropdownButton<String>(
                                    value: currentStatus,
                                    underline: const SizedBox(),
                                    items:
                                        [
                                          'Present',
                                          'Absent',
                                          'Late',
                                          'Half Day',
                                          'Leave',
                                        ].map((status) {
                                          return DropdownMenuItem<String>(
                                            value: status,
                                            child: Text(
                                              status,
                                              style: GoogleFonts.montserrat(
                                                fontSize: 12,
                                              ),
                                            ),
                                          );
                                        }).toList(),
                                    onChanged: (value) {
                                      setState(() {
                                        _attendanceStatus[attendance
                                                .studentId] =
                                            value!;
                                      });
                                    },
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
                                _remarks[attendance.studentId] = value;
                              },
                              controller: TextEditingController(
                                text: _remarks[attendance.studentId] ?? '',
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
                  backgroundColor: AppConstants.primaryColor,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: _isSaving
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
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
