import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/examination_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class ManageExamsPage extends StatefulWidget {
  const ManageExamsPage({super.key});

  @override
  State<ManageExamsPage> createState() => _ManageExamsPageState();
}

class _ManageExamsPageState extends State<ManageExamsPage> {
  int? _selectedClassId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      context.read<ExaminationProvider>().loadExamTypes();
      if (user != null) {
        context.read<ExaminationProvider>().loadExams(userId: user.id);
      }
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Manage Exams',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.blue,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => _showAddExamDialog(context),
            tooltip: 'Add Exam',
          ),
        ],
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<StudentProvider>(
              builder: (context, studentProvider, child) {
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Filter by Class',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.class_),
                  ),
                  initialValue: _selectedClassId,
                  items: [
                    const DropdownMenuItem<int>(
                      value: null,
                      child: Text('All Classes'),
                    ),
                    ...studentProvider.classes.map((classItem) {
                      return DropdownMenuItem<int>(
                        value: classItem.id,
                        child: Text(classItem.className),
                      );
                    }),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedClassId = value;
                    });
                    final user = context.read<AuthProvider>().user;
                    if (user != null) {
                      context.read<ExaminationProvider>().loadExams(
                        userId: user.id,
                        classId: value,
                      );
                    }
                  },
                );
              },
            ),
          ),

          // Exams List
          Expanded(
            child: Consumer<ExaminationProvider>(
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
                        Text(provider.error ?? 'Error loading exams'),
                        const SizedBox(height: 16),
                        Consumer<AuthProvider>(
                          builder: (context, authProvider, child) {
                            final user = authProvider.user;
                            return ElevatedButton(
                              onPressed: () {
                                if (user != null) {
                                  provider.loadExams(
                                    userId: user.id,
                                    classId: _selectedClassId,
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

                final exams = _selectedClassId == null
                    ? provider.exams
                    : provider.exams
                          .where((e) => e.classId == _selectedClassId)
                          .toList();

                if (exams.isEmpty) {
                  return Center(
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
                          'No exams found',
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
                  itemCount: exams.length,
                  itemBuilder: (context, index) {
                    final exam = exams[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: Colors.blue.withOpacity(0.1),
                          child: const Icon(
                            Icons.assignment,
                            color: Colors.blue,
                          ),
                        ),
                        title: Text(
                          exam.examName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text('${exam.examTypeName} - ${exam.className}'),
                            Text(
                              '${DateFormat('MMM d').format(exam.startDate)} - ${DateFormat('MMM d, yyyy').format(exam.endDate)}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                          ],
                        ),
                        trailing: IconButton(
                          icon: const Icon(Icons.arrow_forward_ios, size: 16),
                          onPressed: () {
                            Navigator.pushNamed(
                              context,
                              '/examinations/exam-schedule',
                              arguments: {'exam_id': exam.id},
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
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showAddExamDialog(context),
        backgroundColor: Colors.blue,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _showAddExamDialog(BuildContext context) {
    final formKey = GlobalKey<FormState>();
    final nameController = TextEditingController();
    final descriptionController = TextEditingController();
    int? selectedExamTypeId;
    int? selectedClassId;
    DateTime? startDate;
    DateTime? endDate;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Add New Exam',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        content: StatefulBuilder(
          builder: (context, setState) => Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Consumer<ExaminationProvider>(
                    builder: (context, examProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Exam Type *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        initialValue: selectedExamTypeId,
                        items: examProvider.examTypes.map((type) {
                          return DropdownMenuItem<int>(
                            value: type.id,
                            child: Text(type.examName),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            selectedExamTypeId = value;
                          });
                        },
                        validator: (value) =>
                            value == null ? 'Please select exam type' : null,
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  Consumer<StudentProvider>(
                    builder: (context, studentProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Class *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        initialValue: selectedClassId,
                        items: studentProvider.classes.map((classItem) {
                          return DropdownMenuItem<int>(
                            value: classItem.id,
                            child: Text(classItem.className),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            selectedClassId = value;
                          });
                        },
                        validator: (value) =>
                            value == null ? 'Please select class' : null,
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: nameController,
                    decoration: InputDecoration(
                      labelText: 'Exam Name *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    validator: (value) => value?.isEmpty ?? true
                        ? 'Please enter exam name'
                        : null,
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: startDate ?? DateTime.now(),
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 365)),
                      );
                      if (picked != null) {
                        setState(() {
                          startDate = picked;
                        });
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Start Date *',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text(
                        startDate != null
                            ? DateFormat('yyyy-MM-dd').format(startDate!)
                            : 'Select start date',
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: endDate ?? startDate ?? DateTime.now(),
                        firstDate: startDate ?? DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 365)),
                      );
                      if (picked != null) {
                        setState(() {
                          endDate = picked;
                        });
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'End Date *',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text(
                        endDate != null
                            ? DateFormat('yyyy-MM-dd').format(endDate!)
                            : 'Select end date',
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: descriptionController,
                    decoration: InputDecoration(
                      labelText: 'Description',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    maxLines: 3,
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
                  selectedExamTypeId != null &&
                  selectedClassId != null &&
                  startDate != null &&
                  endDate != null) {
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
                final success = await provider.createExam(
                  userId: user.id,
                  examTypeId: selectedExamTypeId!,
                  examName: nameController.text,
                  classId: selectedClassId!,
                  startDate: startDate!,
                  endDate: endDate!,
                  description: descriptionController.text.isEmpty
                      ? null
                      : descriptionController.text,
                );

                if (mounted) {
                  Navigator.pop(context);
                  if (success) {
                    SweetAlert.showSuccess(
                      context: context,
                      title: 'Success',
                      message: 'Exam created successfully!',
                    );
                  } else {
                    SweetAlert.showError(
                      context: context,
                      title: 'Error',
                      message: provider.error ?? 'Failed to create exam',
                    );
                  }
                }
              }
            },
            child: const Text('Create'),
          ),
        ],
      ),
    );
  }
}
