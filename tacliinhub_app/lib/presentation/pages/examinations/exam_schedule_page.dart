import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/examination_provider.dart';
import '../../providers/academic_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class ExamSchedulePage extends StatefulWidget {
  const ExamSchedulePage({super.key});

  @override
  State<ExamSchedulePage> createState() => _ExamSchedulePageState();
}

class _ExamSchedulePageState extends State<ExamSchedulePage> {
  int? _selectedExamId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      if (user != null) {
        context.read<ExaminationProvider>().loadExams(userId: user.id);
        context.read<ExaminationProvider>().loadExamSchedules(userId: user.id);
        context.read<AcademicProvider>().loadSubjects(user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final args =
        ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    if (args != null && args['exam_id'] != null && _selectedExamId == null) {
      _selectedExamId = args['exam_id'];
      WidgetsBinding.instance.addPostFrameCallback((_) {
        final user = context.read<AuthProvider>().user;
        if (user != null) {
          context.read<ExaminationProvider>().loadExamSchedules(
            userId: user.id,
            examId: _selectedExamId,
          );
        }
      });
    }

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Exam Schedule',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => _showAddScheduleDialog(context),
            tooltip: 'Add Schedule',
          ),
        ],
      ),
      body: Column(
        children: [
          // Exam Filter
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<ExaminationProvider>(
              builder: (context, provider, child) {
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Select Exam',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.assignment),
                  ),
                  initialValue: _selectedExamId,
                  items: [
                    const DropdownMenuItem<int>(
                      value: null,
                      child: Text('All Exams'),
                    ),
                    ...provider.exams.map((exam) {
                      return DropdownMenuItem<int>(
                        value: exam.id,
                        child: Text('${exam.examName} - ${exam.className}'),
                      );
                    }),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedExamId = value;
                    });
                    final user = context.read<AuthProvider>().user;
                    if (user != null) {
                      context.read<ExaminationProvider>().loadExamSchedules(
                        userId: user.id,
                        examId: value,
                      );
                    }
                  },
                );
              },
            ),
          ),

          // Schedule List
          Expanded(
            child: Consumer<ExaminationProvider>(
              builder: (context, provider, child) {
                if (_selectedExamId == null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.calendar_today,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Please select an exam',
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
                        Text(provider.error ?? 'Error loading schedule'),
                        const SizedBox(height: 16),
                        Consumer<AuthProvider>(
                          builder: (context, authProvider, child) {
                            final user = authProvider.user;
                            return ElevatedButton(
                              onPressed: () {
                                if (user != null) {
                                  provider.loadExamSchedules(
                                    userId: user.id,
                                    examId: _selectedExamId,
                                  );
                                }
                              },
                              child: const Text('Retry'),
                            );
                          },
                        ),
                      ],
                    ),
                  );
                }

                final schedules = provider.examSchedules
                    .where((s) => s.examId == _selectedExamId)
                    .toList();

                if (schedules.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.event_busy,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No schedule found',
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
                  itemCount: schedules.length,
                  itemBuilder: (context, index) {
                    final schedule = schedules[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: Colors.purple.withOpacity(0.1),
                          child: const Icon(Icons.event, color: Colors.purple),
                        ),
                        title: Text(
                          schedule.subjectName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
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
                        trailing: IconButton(
                          icon: const Icon(Icons.arrow_forward_ios, size: 16),
                          onPressed: () {
                            Navigator.pushNamed(
                              context,
                              '/examinations/enter-marks',
                              arguments: {'exam_schedule_id': schedule.id},
                            );
                          },
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
      floatingActionButton: _selectedExamId != null
          ? FloatingActionButton(
              onPressed: () => _showAddScheduleDialog(context),
              backgroundColor: Colors.purple,
              child: const Icon(Icons.add),
            )
          : null,
    );
  }

  void _showAddScheduleDialog(BuildContext context) {
    if (_selectedExamId == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'Please select an exam first',
      );
      return;
    }

    final formKey = GlobalKey<FormState>();
    final roomController = TextEditingController();
    final totalMarksController = TextEditingController(text: '100');
    final passingMarksController = TextEditingController(text: '40');
    int? selectedSubjectId;
    DateTime? examDate;
    TimeOfDay? startTime;
    TimeOfDay? endTime;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Add Schedule',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        content: StatefulBuilder(
          builder: (context, setState) => Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Consumer<AcademicProvider>(
                    builder: (context, academicProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Subject *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        initialValue: selectedSubjectId,
                        items: academicProvider.subjects.map((subject) {
                          return DropdownMenuItem<int>(
                            value: subject.id,
                            child: Text(subject.subjectName),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            selectedSubjectId = value;
                          });
                        },
                        validator: (value) =>
                            value == null ? 'Please select subject' : null,
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: examDate ?? DateTime.now(),
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 365)),
                      );
                      if (picked != null) {
                        setState(() {
                          examDate = picked;
                        });
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Exam Date *',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text(
                        examDate != null
                            ? DateFormat('yyyy-MM-dd').format(examDate!)
                            : 'Select date',
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final picked = await showTimePicker(
                        context: context,
                        initialTime: startTime ?? TimeOfDay.now(),
                      );
                      if (picked != null) {
                        setState(() {
                          startTime = picked;
                        });
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Start Time *',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text(
                        startTime != null
                            ? '${startTime!.hour.toString().padLeft(2, '0')}:${startTime!.minute.toString().padLeft(2, '0')}'
                            : 'Select time',
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final picked = await showTimePicker(
                        context: context,
                        initialTime: endTime ?? TimeOfDay.now(),
                      );
                      if (picked != null) {
                        setState(() {
                          endTime = picked;
                        });
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'End Time *',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text(
                        endTime != null
                            ? '${endTime!.hour.toString().padLeft(2, '0')}:${endTime!.minute.toString().padLeft(2, '0')}'
                            : 'Select time',
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: roomController,
                    decoration: InputDecoration(
                      labelText: 'Room No',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: totalMarksController,
                    decoration: InputDecoration(
                      labelText: 'Total Marks *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    keyboardType: TextInputType.number,
                    validator: (value) => value?.isEmpty ?? true
                        ? 'Please enter total marks'
                        : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: passingMarksController,
                    decoration: InputDecoration(
                      labelText: 'Passing Marks *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    keyboardType: TextInputType.number,
                    validator: (value) => value?.isEmpty ?? true
                        ? 'Please enter passing marks'
                        : null,
                  ),
                ],
              ),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (formKey.currentState!.validate() &&
                  selectedSubjectId != null &&
                  examDate != null &&
                  startTime != null &&
                  endTime != null) {
                final user = context.read<AuthProvider>().user;
                if (user == null) {
                  SweetAlert.showError(
                    context: context,
                    title: 'Error',
                    message: 'User not logged in',
                  );
                  return;
                }

                final provider = context.read<ExaminationProvider>();
                final startTimeStr =
                    '${startTime!.hour.toString().padLeft(2, '0')}:${startTime!.minute.toString().padLeft(2, '0')}:00';
                final endTimeStr =
                    '${endTime!.hour.toString().padLeft(2, '0')}:${endTime!.minute.toString().padLeft(2, '0')}:00';

                final success = await provider.createExamSchedule(
                  userId: user.id,
                  examId: _selectedExamId!,
                  subjectId: selectedSubjectId!,
                  examDate: examDate!,
                  startTime: startTimeStr,
                  endTime: endTimeStr,
                  roomNo: roomController.text.isEmpty
                      ? null
                      : roomController.text,
                  totalMarks: double.parse(totalMarksController.text),
                  passingMarks: double.parse(passingMarksController.text),
                );

                if (mounted) {
                  Navigator.pop(context);
                  if (success) {
                    SweetAlert.showSuccess(
                      context: context,
                      title: 'Success',
                      message: 'Schedule added successfully!',
                    );
                  } else {
                    SweetAlert.showError(
                      context: context,
                      title: 'Error',
                      message: provider.error ?? 'Failed to add schedule',
                    );
                  }
                }
              }
            },
            child: const Text('Add'),
          ),
        ],
      ),
    );
  }
}
