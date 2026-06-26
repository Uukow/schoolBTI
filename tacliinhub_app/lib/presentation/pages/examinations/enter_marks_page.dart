import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/examination_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class EnterMarksPage extends StatefulWidget {
  const EnterMarksPage({super.key});

  @override
  State<EnterMarksPage> createState() => _EnterMarksPageState();
}

class _EnterMarksPageState extends State<EnterMarksPage> {
  int? _examScheduleId;
  final Map<int, TextEditingController> _marksControllers = {};
  final Map<int, bool> _absentFlags = {};
  final Map<int, TextEditingController> _remarksControllers = {};
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final args =
          ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
      if (args != null && args['exam_schedule_id'] != null) {
        _examScheduleId = args['exam_schedule_id'];
        _loadMarks();
      }
    });
  }

  void _loadMarks() {
    if (_examScheduleId != null) {
      final user = context.read<AuthProvider>().user;
      if (user != null) {
        context.read<ExaminationProvider>().loadStudentMarks(
          userId: user.id,
          examScheduleId: _examScheduleId!,
        );
      }
    }
  }

  Future<void> _saveMarks() async {
    if (_examScheduleId == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'Exam schedule not selected',
      );
      return;
    }

    setState(() {
      _isSaving = true;
    });

    try {
      final provider = context.read<ExaminationProvider>();
      final students = provider.studentMarks.map((mark) {
        final marksText = _marksControllers[mark.studentId]?.text ?? '';
        final marks = marksText.isEmpty ? null : double.tryParse(marksText);
        final isAbsent = _absentFlags[mark.studentId] ?? false;
        final remarks = _remarksControllers[mark.studentId]?.text ?? '';

        return {
          'id': mark.studentId,
          'marks': marks,
          'is_absent': isAbsent,
          'remarks': remarks,
        };
      }).toList();

      final user = context.read<AuthProvider>().user;
      if (user == null) {
        if (mounted) {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: 'User not logged in',
          );
        }
        return;
      }

      final success = await provider.saveMarks(
        userId: user.id,
        examScheduleId: _examScheduleId!,
        students: students,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Marks saved successfully!',
          );
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to save marks',
          );
        }
      }
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to save marks: ${e.toString()}',
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
          'Enter Marks',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
        elevation: 0,
      ),
      body: Consumer<ExaminationProvider>(
        builder: (context, provider, child) {
          if (_examScheduleId == null) {
            return _buildScheduleSelector(context, provider);
          }

          if (provider.isLoading && provider.studentMarks.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.studentMarks.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading marks'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadMarks,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.studentMarks.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.person_outline, size: 64, color: Colors.grey[400]),
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

          // Initialize controllers
          for (var mark in provider.studentMarks) {
            if (!_marksControllers.containsKey(mark.studentId)) {
              _marksControllers[mark.studentId] = TextEditingController(
                text: mark.marksObtained?.toString() ?? '',
              );
              _absentFlags[mark.studentId] = mark.isAbsent;
              _remarksControllers[mark.studentId] = TextEditingController(
                text: mark.remarks ?? '',
              );
            }
          }

          return Column(
            children: [
              Expanded(
                child: ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: provider.studentMarks.length,
                  itemBuilder: (context, index) {
                    final mark = provider.studentMarks[index];
                    final isAbsent = _absentFlags[mark.studentId] ?? false;

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
                                  backgroundColor: Colors.orange.withOpacity(
                                    0.1,
                                  ),
                                  child: Text(
                                    mark.studentName[0].toUpperCase(),
                                    style: TextStyle(
                                      color: Colors.orange,
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
                                        mark.studentName,
                                        style: GoogleFonts.montserrat(
                                          fontWeight: FontWeight.w600,
                                          fontSize: 16,
                                        ),
                                      ),
                                      Text(
                                        'ID: ${mark.studentIdNumber ?? mark.studentId}',
                                        style: GoogleFonts.montserrat(
                                          fontSize: 12,
                                          color: Colors.grey[600],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Checkbox(
                                  value: isAbsent,
                                  onChanged: (value) {
                                    setState(() {
                                      _absentFlags[mark.studentId] =
                                          value ?? false;
                                      if (value == true) {
                                        _marksControllers[mark.studentId]
                                            ?.clear();
                                      }
                                    });
                                  },
                                ),
                                Text(
                                  'Absent',
                                  style: GoogleFonts.montserrat(fontSize: 12),
                                ),
                              ],
                            ),
                            if (!isAbsent) ...[
                              const SizedBox(height: 12),
                              TextField(
                                controller: _marksControllers[mark.studentId],
                                decoration: InputDecoration(
                                  labelText: 'Marks Obtained',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                ),
                                keyboardType:
                                    const TextInputType.numberWithOptions(
                                      decimal: true,
                                    ),
                              ),
                            ],
                            const SizedBox(height: 8),
                            TextField(
                              controller: _remarksControllers[mark.studentId],
                              decoration: InputDecoration(
                                labelText: 'Remarks',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                              maxLines: 2,
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
              Container(
                padding: const EdgeInsets.all(16),
                color: Colors.white,
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isSaving ? null : _saveMarks,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.orange,
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
                            'Save Marks',
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
          );
        },
      ),
    );
  }

  Widget _buildScheduleSelector(
    BuildContext context,
    ExaminationProvider provider,
  ) {
    final user = context.read<AuthProvider>().user;

    // Load exams and schedules if not loaded
    if (user != null) {
      if (provider.exams.isEmpty) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          provider.loadExams(userId: user.id);
        });
      }
      if (provider.examSchedules.isEmpty) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          provider.loadExamSchedules(userId: user.id);
        });
      }
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.info_outline, color: Colors.orange),
                      const SizedBox(width: 8),
                      Text(
                        'Select Exam Schedule',
                        style: GoogleFonts.montserrat(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Choose an exam schedule to enter marks for students.',
                    style: GoogleFonts.montserrat(
                      fontSize: 14,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (provider.isLoading && provider.examSchedules.isEmpty)
            const Center(child: CircularProgressIndicator())
          else if (provider.examSchedules.isEmpty)
            Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.event_busy, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No exam schedules available',
                    style: GoogleFonts.montserrat(
                      fontSize: 16,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            )
          else
            ...provider.examSchedules.map((schedule) {
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: Colors.orange.withOpacity(0.1),
                    child: const Icon(Icons.event, color: Colors.orange),
                  ),
                  title: Text(
                    schedule.subjectName,
                    style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text(
                        'Exam: ${schedule.examName}',
                        style: GoogleFonts.montserrat(fontSize: 12),
                      ),
                      Text(
                        DateFormat(
                          'EEEE, MMM d, yyyy',
                        ).format(schedule.examDate),
                        style: GoogleFonts.montserrat(fontSize: 12),
                      ),
                      Text(
                        '${schedule.startTime.substring(0, 5)} - ${schedule.endTime.substring(0, 5)}',
                        style: GoogleFonts.montserrat(fontSize: 12),
                      ),
                      if (schedule.roomNo != null)
                        Text(
                          'Room: ${schedule.roomNo}',
                          style: GoogleFonts.montserrat(fontSize: 12),
                        ),
                      Text(
                        'Marks: ${schedule.totalMarks.toStringAsFixed(0)} (Pass: ${schedule.passingMarks.toStringAsFixed(0)})',
                        style: GoogleFonts.montserrat(fontSize: 12),
                      ),
                    ],
                  ),
                  trailing: ElevatedButton(
                    onPressed: () {
                      setState(() {
                        _examScheduleId = schedule.id;
                      });
                      _loadMarks();
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.orange,
                    ),
                    child: const Text('Select'),
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }
}
