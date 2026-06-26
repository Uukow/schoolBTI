import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../core/sweet_alert.dart';
import '../../providers/teacher_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';
import '../../../data/models/teacher_models.dart';

class TeacherAttendancePage extends StatefulWidget {
  const TeacherAttendancePage({super.key});

  @override
  State<TeacherAttendancePage> createState() => _TeacherAttendancePageState();
}

class _TeacherAttendancePageState extends State<TeacherAttendancePage> {
  TeacherClass? _selectedClass;
  DateTime _selectedDate = DateTime.now();
  List<TeacherStudent> _students = [];
  final Map<int, String> _attendanceStatus = {}; // student_id => status
  bool _isLoading = false;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        Provider.of<TeacherProvider>(
          context,
          listen: false,
        ).loadClasses(user.id);
      }
    });
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 30)),
      lastDate: DateTime.now().add(const Duration(days: 1)),
    );
    if (picked != null) {
      setState(() {
        _selectedDate = picked;
      });
      _loadStudents();
    }
  }

  Future<void> _loadStudents() async {
    if (_selectedClass == null) return;

    setState(() {
      _isLoading = true;
      _attendanceStatus.clear();
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user == null) return;

      final provider = Provider.of<TeacherProvider>(context, listen: false);
      await provider.loadStudents(
        user.id,
        classId: _selectedClass!.classId,
        subjectId: _selectedClass!.subjectId,
      );

      setState(() {
        _students = provider.students;
        // Initialize all as Present by default
        for (var student in _students) {
          _attendanceStatus[student.id] = 'Present';
        }
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to load students: $e',
        );
      }
    }
  }

  Future<void> _saveAttendance() async {
    if (_selectedClass == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select a class/subject',
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
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user == null) return;

      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      final provider = Provider.of<TeacherProvider>(context, listen: false);

      final success = await provider.saveAttendance(
        userId: user.id,
        date: dateStr,
        classId: _selectedClass!.classId,
        subjectId: _selectedClass!.subjectId,
        attendance: _attendanceStatus,
      );

      setState(() {
        _isSaving = false;
      });

      if (success && mounted) {
        SweetAlert.showSuccess(
          context: context,
          title: 'Success',
          message: 'Attendance saved successfully',
        );
      } else if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to save attendance',
        );
      }
    } catch (e) {
      setState(() {
        _isSaving = false;
      });
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to save attendance: $e',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Mark Attendance',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer<TeacherProvider>(
        builder: (context, provider, child) {
          return Column(
            children: [
              // Selection Section
              Container(
                padding: const EdgeInsets.all(16),
                color: Colors.white,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Class/Subject Dropdown
                    DropdownButtonFormField<TeacherClass>(
                      decoration: InputDecoration(
                        labelText: 'Select Class/Subject',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.class_),
                      ),
                      initialValue: _selectedClass,
                      items: provider.classes.map((cls) {
                        return DropdownMenuItem<TeacherClass>(
                          value: cls,
                          child: Text(
                            '${cls.className} - ${cls.subjectName}',
                            style: GoogleFonts.montserrat(),
                          ),
                        );
                      }).toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedClass = value;
                          _students = [];
                          _attendanceStatus.clear();
                        });
                        if (value != null) {
                          _loadStudents();
                        }
                      },
                    ),
                    const SizedBox(height: 16),
                    // Date Selection
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
                            const Icon(
                              Icons.calendar_today,
                              color: Colors.grey,
                            ),
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
                  ],
                ),
              ),
              const Divider(height: 1),
              // Students List
              Expanded(
                child: _isLoading
                    ? const Center(child: CircularProgressIndicator())
                    : _selectedClass == null
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.class_outlined,
                              size: 64,
                              color: Colors.grey[400],
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Select a class/subject to mark attendance',
                              style: GoogleFonts.montserrat(
                                fontSize: 16,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                      )
                    : _students.isEmpty
                    ? Center(
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
                              'No students found',
                              style: GoogleFonts.montserrat(
                                fontSize: 16,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _students.length,
                        itemBuilder: (context, index) {
                          final student = _students[index];
                          final status =
                              _attendanceStatus[student.id] ?? 'Present';
                          return _buildStudentCard(student, status);
                        },
                      ),
              ),
              // Save Button
              if (_students.isNotEmpty)
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 10,
                        offset: const Offset(0, -2),
                      ),
                    ],
                  ),
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
                                valueColor: AlwaysStoppedAnimation<Color>(
                                  Colors.white,
                                ),
                              ),
                            )
                          : Text(
                              'Save Attendance',
                              style: GoogleFonts.montserrat(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                    ),
                  ),
                ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildStudentCard(TeacherStudent student, String status) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: AppConstants.primaryColor.withOpacity(0.1),
          child: Text(
            student.fullName.isNotEmpty
                ? student.fullName[0].toUpperCase()
                : 'S',
            style: TextStyle(
              color: AppConstants.primaryColor,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        title: Text(
          student.fullName,
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            fontSize: 16,
          ),
        ),
        subtitle: Text(
          student.studentId,
          style: GoogleFonts.montserrat(fontSize: 12),
        ),
        trailing: DropdownButton<String>(
          value: status,
          underline: const SizedBox(),
          items: ['Present', 'Absent', 'Late'].map((s) {
            Color color;
            IconData icon;
            switch (s) {
              case 'Present':
                color = Colors.green;
                icon = Icons.check_circle;
                break;
              case 'Absent':
                color = Colors.red;
                icon = Icons.cancel;
                break;
              case 'Late':
                color = Colors.orange;
                icon = Icons.schedule;
                break;
              default:
                color = Colors.grey;
                icon = Icons.help;
            }
            return DropdownMenuItem<String>(
              value: s,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(icon, color: color, size: 18),
                  const SizedBox(width: 8),
                  Text(
                    s,
                    style: GoogleFonts.montserrat(
                      color: color,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            );
          }).toList(),
          onChanged: (value) {
            if (value != null) {
              setState(() {
                _attendanceStatus[student.id] = value;
              });
            }
          },
        ),
      ),
    );
  }
}
