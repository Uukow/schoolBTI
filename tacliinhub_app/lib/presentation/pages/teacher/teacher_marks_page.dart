import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../../core/sweet_alert.dart';
import '../../providers/teacher_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;

class ExamSchedule {
  final int id;
  final String examName;
  final String subjectName;
  final String className;
  final String examDate;
  final String startTime;
  final double maxMarks;

  ExamSchedule({
    required this.id,
    required this.examName,
    required this.subjectName,
    required this.className,
    required this.examDate,
    required this.startTime,
    required this.maxMarks,
  });

  factory ExamSchedule.fromJson(Map<String, dynamic> json) {
    return ExamSchedule(
      id: json['id'] ?? 0,
      examName: json['exam_name'] ?? '',
      subjectName: json['subject_name'] ?? '',
      className: json['class_name'] ?? '',
      examDate: json['exam_date'] ?? '',
      startTime: json['start_time'] ?? '',
      maxMarks: (json['max_marks'] ?? json['total_marks'] ?? 100).toDouble(),
    );
  }
}

class StudentMark {
  final int studentId;
  final String studentName;
  final String admissionNumber;
  final double? marksObtained;
  final bool isAbsent;

  StudentMark({
    required this.studentId,
    required this.studentName,
    required this.admissionNumber,
    this.marksObtained,
    this.isAbsent = false,
  });
}

class TeacherMarksPage extends StatefulWidget {
  const TeacherMarksPage({super.key});

  @override
  State<TeacherMarksPage> createState() => _TeacherMarksPageState();
}

class _TeacherMarksPageState extends State<TeacherMarksPage> {
  List<ExamSchedule> _examSchedules = [];
  ExamSchedule? _selectedSchedule;
  List<StudentMark> _students = [];
  final Map<int, TextEditingController> _marksControllers = {};
  final Map<int, bool> _absentStatus = {};
  bool _isLoadingSchedules = false;
  bool _isLoadingStudents = false;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadExamSchedules();
    });
  }

  @override
  void dispose() {
    for (var controller in _marksControllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _loadExamSchedules() async {
    setState(() {
      _isLoadingSchedules = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user == null) return;

      final url = Uri.parse(
        '${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/get-exam-schedules.php?user_id=${user.id}',
      );

      final response = await http
          .get(url, headers: {'Content-Type': 'application/json'})
          .timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> schedulesJson =
              data['schedules'] ?? data['data']?['schedules'] ?? [];
          setState(() {
            _examSchedules = schedulesJson
                .map((json) => ExamSchedule.fromJson(json))
                .toList();
            _isLoadingSchedules = false;
          });
        } else {
          throw Exception(data['message'] ?? 'Failed to load exam schedules');
        }
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      setState(() {
        _isLoadingSchedules = false;
      });
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to load exam schedules: $e',
        );
      }
    }
  }

  Future<void> _loadStudents() async {
    if (_selectedSchedule == null) return;

    setState(() {
      _isLoadingStudents = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user == null) return;

      final provider = Provider.of<TeacherProvider>(context, listen: false);

      // Get students for the selected schedule's class
      await provider.loadStudents(user.id);

      // Filter students by class (we'll need to match by class name or get class_id from schedule)
      // For now, we'll use all students from teacher's classes
      final students = provider.students;

      setState(() {
        _students = students.map((s) {
          final existingController = _marksControllers[s.id];
          if (existingController != null) {
            existingController.dispose();
          }

          final controller = TextEditingController();
          _marksControllers[s.id] = controller;

          return StudentMark(
            studentId: s.id,
            studentName: s.fullName,
            admissionNumber: s.studentId,
          );
        }).toList();

        _isLoadingStudents = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingStudents = false;
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

  Future<void> _saveMarks() async {
    if (_selectedSchedule == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select an exam schedule',
      );
      return;
    }

    final marks = <int, double>{};
    bool hasMarks = false;

    for (var student in _students) {
      if (_absentStatus[student.studentId] == true) {
        continue; // Skip absent students
      }

      final controller = _marksControllers[student.studentId];
      if (controller != null && controller.text.isNotEmpty) {
        final marksValue = double.tryParse(controller.text);
        if (marksValue != null) {
          marks[student.studentId] = marksValue;
          hasMarks = true;
        }
      }
    }

    if (!hasMarks && _absentStatus.values.every((absent) => !absent)) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please enter marks for at least one student',
      );
      return;
    }

    setState(() {
      _isSaving = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user == null) return;

      final provider = Provider.of<TeacherProvider>(context, listen: false);

      final success = await provider.saveMarks(
        userId: user.id,
        examScheduleId: _selectedSchedule!.id,
        marks: marks,
      );

      setState(() {
        _isSaving = false;
      });

      if (success && mounted) {
        SweetAlert.showSuccess(
          context: context,
          title: 'Success',
          message: 'Marks saved successfully',
        );
      } else if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to save marks',
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
          message: 'Failed to save marks: $e',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Enter Marks',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      body: Column(
        children: [
          // Selection Section
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Exam Schedule Dropdown
                _isLoadingSchedules
                    ? const Center(child: CircularProgressIndicator())
                    : DropdownButtonFormField<ExamSchedule>(
                        decoration: InputDecoration(
                          labelText: 'Select Exam Schedule',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.assignment),
                        ),
                        initialValue: _selectedSchedule,
                        items: _examSchedules.map((schedule) {
                          return DropdownMenuItem<ExamSchedule>(
                            value: schedule,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  '${schedule.examName} - ${schedule.subjectName}',
                                  style: GoogleFonts.montserrat(
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Text(
                                  '${schedule.className} • ${DateFormat('MMM d, yyyy').format(DateTime.tryParse(schedule.examDate) ?? DateTime.now())}',
                                  style: GoogleFonts.montserrat(
                                    fontSize: 12,
                                    color: Colors.grey[600],
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedSchedule = value;
                            _students = [];
                            for (var controller in _marksControllers.values) {
                              controller.dispose();
                            }
                            _marksControllers.clear();
                            _absentStatus.clear();
                          });
                          if (value != null) {
                            _loadStudents();
                          }
                        },
                      ),
                if (_selectedSchedule != null) ...[
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppConstants.primaryColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.info_outline,
                          color: AppConstants.primaryColor,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Max Marks: ${_selectedSchedule!.maxMarks}',
                            style: GoogleFonts.montserrat(
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
          const Divider(height: 1),
          // Students List
          Expanded(
            child: _isLoadingStudents
                ? const Center(child: CircularProgressIndicator())
                : _selectedSchedule == null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.assignment_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Select an exam schedule to enter marks',
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
                      return _buildStudentMarkCard(student);
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
                  onPressed: _isSaving ? null : _saveMarks,
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
                          'Save Marks',
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
      ),
    );
  }

  Widget _buildStudentMarkCard(StudentMark student) {
    final controller =
        _marksControllers[student.studentId] ?? TextEditingController();
    final isAbsent = _absentStatus[student.studentId] ?? false;

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
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    student.studentName,
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.w600,
                      fontSize: 16,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    student.admissionNumber,
                    style: GoogleFonts.montserrat(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 16),
            Checkbox(
              value: isAbsent,
              onChanged: (value) {
                setState(() {
                  _absentStatus[student.studentId] = value ?? false;
                  if (value == true) {
                    controller.clear();
                  }
                });
              },
            ),
            const SizedBox(width: 8),
            Text('Absent', style: GoogleFonts.montserrat(fontSize: 12)),
            const SizedBox(width: 16),
            SizedBox(
              width: 100,
              child: TextField(
                controller: controller,
                enabled: !isAbsent,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  labelText: 'Marks',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 8,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
