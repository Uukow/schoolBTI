import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/lms_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/academic_provider.dart';
import '../../../core/sweet_alert.dart';

class AddQuizPage extends StatefulWidget {
  const AddQuizPage({super.key});

  @override
  State<AddQuizPage> createState() => _AddQuizPageState();
}

class _AddQuizPageState extends State<AddQuizPage> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _durationController = TextEditingController();
  final _totalMarksController = TextEditingController();
  int? _selectedClassId;
  int? _selectedSubjectId;
  DateTime? _startDate;
  DateTime? _endDate;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      final studentProvider = context.read<StudentProvider>();
      studentProvider.loadClasses();

      final academicProvider = context.read<AcademicProvider>();
      if (user != null) {
        academicProvider.loadSubjects(user.id);
      }
    });
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _durationController.dispose();
    _totalMarksController.dispose();
    super.dispose();
  }

  Future<void> _selectStartDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        _startDate = picked;
        if (_endDate != null && _endDate!.isBefore(picked)) {
          _endDate = null;
        }
      });
    }
  }

  Future<void> _selectEndDate() async {
    if (_startDate == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'Please select start date first',
      );
      return;
    }

    final picked = await showDatePicker(
      context: context,
      initialDate: _startDate!.add(const Duration(days: 7)),
      firstDate: _startDate!,
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        _endDate = picked;
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedClassId == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'Please select a class',
      );
      return;
    }
    if (_startDate == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'Please select a start date',
      );
      return;
    }

    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<LmsProvider>();

    final success = await provider.addQuiz(
      title: _titleController.text.trim(),
      description: _descriptionController.text.trim(),
      classId: _selectedClassId!,
      subjectId: _selectedSubjectId,
      durationMinutes: int.tryParse(_durationController.text.trim()) ?? 30,
      totalMarks: double.tryParse(_totalMarksController.text.trim()) ?? 100.0,
      startDate: DateFormat('yyyy-MM-dd').format(_startDate!),
      endDate: _endDate != null
          ? DateFormat('yyyy-MM-dd').format(_endDate!)
          : null,
      userId: user?.id,
    );

    if (mounted) {
      if (success) {
        SweetAlert.showSuccess(
          context: context,
          title: 'Success',
          message: 'Quiz added successfully',
        );
        Navigator.pop(context);
      } else {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to add quiz',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Add Quiz',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextFormField(
                controller: _titleController,
                decoration: InputDecoration(
                  labelText: 'Title *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter a title';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // Subject dropdown
              Consumer<AcademicProvider>(
                builder: (context, academicProvider, child) {
                  return DropdownButtonFormField<int>(
                    decoration: InputDecoration(
                      labelText: 'Subject *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    initialValue: _selectedSubjectId,
                    items: [
                      const DropdownMenuItem<int>(
                        value: null,
                        child: Text('Select Subject'),
                      ),
                      ...academicProvider.subjects.map((subj) {
                        return DropdownMenuItem<int>(
                          value: subj.id,
                          child: Text(subj.subjectName),
                        );
                      }),
                    ],
                    onChanged: (value) {
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
              TextFormField(
                controller: _descriptionController,
                decoration: InputDecoration(
                  labelText: 'Description *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                maxLines: 4,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter a description';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              Consumer<StudentProvider>(
                builder: (context, studentProvider, child) {
                  return DropdownButtonFormField<int>(
                    decoration: InputDecoration(
                      labelText: 'Class *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    initialValue: _selectedClassId,
                    items: [
                      const DropdownMenuItem<int>(
                        value: null,
                        child: Text('Select Class'),
                      ),
                      ...studentProvider.classes.map((cls) {
                        return DropdownMenuItem<int>(
                          value: cls.id,
                          child: Text(cls.className),
                        );
                      }),
                    ],
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
              InkWell(
                onTap: _selectStartDate,
                child: InputDecorator(
                  decoration: InputDecoration(
                    labelText: 'Start Date *',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    suffixIcon: const Icon(Icons.calendar_today),
                  ),
                  child: Text(
                    _startDate == null
                        ? 'Select start date'
                        : DateFormat('yyyy-MM-dd').format(_startDate!),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              InkWell(
                onTap: _selectEndDate,
                child: InputDecorator(
                  decoration: InputDecoration(
                    labelText: 'End Date (Optional)',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    suffixIcon: const Icon(Icons.calendar_today),
                  ),
                  child: Text(
                    _endDate == null
                        ? 'Select end date (optional)'
                        : DateFormat('yyyy-MM-dd').format(_endDate!),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _durationController,
                decoration: InputDecoration(
                  labelText: 'Duration (minutes) *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  hintText: 'e.g., 30',
                ),
                keyboardType: TextInputType.number,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter duration';
                  }
                  if (int.tryParse(value.trim()) == null) {
                    return 'Please enter a valid number';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _totalMarksController,
                decoration: InputDecoration(
                  labelText: 'Total Marks *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  hintText: 'e.g., 100',
                ),
                keyboardType: const TextInputType.numberWithOptions(
                  decimal: true,
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter total marks';
                  }
                  if (double.tryParse(value.trim()) == null) {
                    return 'Please enter a valid number';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 24),
              Consumer<LmsProvider>(
                builder: (context, provider, child) {
                  return ElevatedButton(
                    onPressed: provider.isLoading ? null : _submit,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.purple,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: provider.isLoading
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
                            'Add Quiz',
                            style: GoogleFonts.montserrat(
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                  );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
