import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/academic_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AddLessonPlanPage extends StatefulWidget {
  const AddLessonPlanPage({super.key});

  @override
  State<AddLessonPlanPage> createState() => _AddLessonPlanPageState();
}

class _AddLessonPlanPageState extends State<AddLessonPlanPage> {
  final _formKey = GlobalKey<FormState>();
  final _topicController = TextEditingController();
  final _objectivesController = TextEditingController();
  final _contentController = TextEditingController();
  final _teachingMethodsController = TextEditingController();
  final _assessmentController = TextEditingController();
  final _dateController = TextEditingController();

  int? _selectedClassId;
  int? _selectedSubjectId;
  bool _isLoading = false;

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

  @override
  void dispose() {
    _topicController.dispose();
    _objectivesController.dispose();
    _contentController.dispose();
    _teachingMethodsController.dispose();
    _assessmentController.dispose();
    _dateController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        _dateController.text = DateFormat('yyyy-MM-dd').format(picked);
      });
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedClassId == null || _selectedSubjectId == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select class and subject',
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final success = await context.read<AcademicProvider>().createLessonPlan(
        classId: _selectedClassId!,
        subjectId: _selectedSubjectId!,
        topic: _topicController.text.trim(),
        objectives: _objectivesController.text.trim(),
        content: _contentController.text.trim(),
        teachingMethods: _teachingMethodsController.text.trim(),
        assessment: _assessmentController.text.trim(),
        date: _dateController.text.trim(),
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Lesson plan created successfully!',
            onConfirm: () {
              Navigator.pop(context, true);
            },
          );
        } else {
          final error =
              context.read<AcademicProvider>().error ??
              'Failed to create lesson plan';
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
          message: 'Failed to create lesson plan: ${e.toString()}',
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
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
          'Add Lesson Plan',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.teal,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Class Selection
              Consumer<StudentProvider>(
                builder: (context, studentProvider, child) {
                  return DropdownButtonFormField<int>(
                    decoration: InputDecoration(
                      labelText: 'Class *',
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
                        _selectedSubjectId = null;
                      });
                    },
                    validator: (value) {
                      if (value == null) {
                        return 'Please select a class';
                      }
                      return null;
                    },
                  );
                },
              ),
              const SizedBox(height: 16),

              // Subject Selection
              Consumer<AcademicProvider>(
                builder: (context, academicProvider, child) {
                  return DropdownButtonFormField<int>(
                    decoration: InputDecoration(
                      labelText: 'Subject *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      prefixIcon: const Icon(Icons.book),
                    ),
                    initialValue: _selectedSubjectId,
                    items: academicProvider.subjects.map((subject) {
                      return DropdownMenuItem<int>(
                        value: subject.id,
                        child: Text(subject.subjectName),
                      );
                    }).toList(),
                    onChanged: _selectedClassId == null
                        ? null
                        : (value) {
                            setState(() {
                              _selectedSubjectId = value;
                            });
                          },
                    validator: (value) {
                      if (value == null) {
                        return 'Please select a subject';
                      }
                      return null;
                    },
                  );
                },
              ),
              const SizedBox(height: 16),

              // Date
              TextFormField(
                controller: _dateController,
                decoration: InputDecoration(
                  labelText: 'Plan Date *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.calendar_today),
                ),
                readOnly: true,
                onTap: _selectDate,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please select a date';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),

              // Topic
              TextFormField(
                controller: _topicController,
                decoration: InputDecoration(
                  labelText: 'Topic *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.topic),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter topic';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),

              // Objectives
              TextFormField(
                controller: _objectivesController,
                decoration: InputDecoration(
                  labelText: 'Objectives *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.checklist),
                ),
                maxLines: 3,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter objectives';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),

              // Content
              TextFormField(
                controller: _contentController,
                decoration: InputDecoration(
                  labelText: 'Content *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.article),
                ),
                maxLines: 4,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter content';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),

              // Teaching Methods
              TextFormField(
                controller: _teachingMethodsController,
                decoration: InputDecoration(
                  labelText: 'Teaching Methods',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.school),
                ),
                maxLines: 3,
              ),
              const SizedBox(height: 16),

              // Assessment
              TextFormField(
                controller: _assessmentController,
                decoration: InputDecoration(
                  labelText: 'Assessment',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.assessment),
                ),
                maxLines: 3,
              ),
              const SizedBox(height: 24),

              // Submit Button
              ElevatedButton(
                onPressed: _isLoading ? null : _submitForm,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.teal,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : Text(
                        'Create Lesson Plan',
                        style: GoogleFonts.montserrat(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                        ),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
