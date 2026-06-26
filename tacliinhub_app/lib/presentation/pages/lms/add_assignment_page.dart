import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/lms_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class AddAssignmentPage extends StatefulWidget {
  const AddAssignmentPage({super.key});

  @override
  State<AddAssignmentPage> createState() => _AddAssignmentPageState();
}

class _AddAssignmentPageState extends State<AddAssignmentPage> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _maxMarksController = TextEditingController();
  int? _selectedClassId;
  int? _selectedSubjectId;
  DateTime? _dueDate;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final studentProvider = context.read<StudentProvider>();
      studentProvider.loadClasses();
    });
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _maxMarksController.dispose();
    super.dispose();
  }

  Future<void> _selectDueDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 7)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        _dueDate = picked;
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
    if (_dueDate == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'Please select a due date',
      );
      return;
    }

    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<LmsProvider>();

    final success = await provider.addAssignment(
      title: _titleController.text.trim(),
      description: _descriptionController.text.trim(),
      classId: _selectedClassId!,
      subjectId: _selectedSubjectId,
      dueDate: DateFormat('yyyy-MM-dd').format(_dueDate!),
      maxMarks: _maxMarksController.text.trim().isEmpty
          ? null
          : double.tryParse(_maxMarksController.text.trim()),
      userId: user?.id,
    );

    if (mounted) {
      if (success) {
        SweetAlert.showSuccess(
          context: context,
          title: 'Success',
          message: 'Assignment added successfully',
        );
        Navigator.pop(context);
      } else {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to add assignment',
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
          'Add Assignment',
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
                onTap: _selectDueDate,
                child: InputDecorator(
                  decoration: InputDecoration(
                    labelText: 'Due Date *',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    suffixIcon: const Icon(Icons.calendar_today),
                  ),
                  child: Text(
                    _dueDate == null
                        ? 'Select due date'
                        : DateFormat('yyyy-MM-dd').format(_dueDate!),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _maxMarksController,
                decoration: InputDecoration(
                  labelText: 'Max Marks (Optional)',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                keyboardType: const TextInputType.numberWithOptions(
                  decimal: true,
                ),
                validator: (value) {
                  if (value != null &&
                      value.trim().isNotEmpty &&
                      double.tryParse(value.trim()) == null) {
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
                      backgroundColor: Colors.orange,
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
                            'Add Assignment',
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
