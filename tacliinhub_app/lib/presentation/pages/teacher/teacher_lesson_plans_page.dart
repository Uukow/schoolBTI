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

class TeacherLessonPlansPage extends StatefulWidget {
  const TeacherLessonPlansPage({super.key});

  @override
  State<TeacherLessonPlansPage> createState() => _TeacherLessonPlansPageState();
}

class _TeacherLessonPlansPageState extends State<TeacherLessonPlansPage> {
  TeacherClass? _selectedClass;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        final provider = Provider.of<TeacherProvider>(context, listen: false);
        provider.loadClasses(user.id);
        provider.loadLessonPlans(user.id);
      }
    });
  }

  Future<void> _refreshData() async {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user == null) return;

    final provider = Provider.of<TeacherProvider>(context, listen: false);
    await provider.loadLessonPlans(
      user.id,
      classId: _selectedClass?.classId,
      subjectId: _selectedClass?.subjectId,
    );
  }

  void _showCreateDialog() {
    final pageContext = context; // Store parent context
    showDialog(
      context: context,
      builder: (dialogContext) => _CreateLessonPlanDialog(
        classes: Provider.of<TeacherProvider>(context, listen: false).classes,
        parentContext: pageContext, // Pass parent context
        onSaved: () {
          _refreshData();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Lesson Plans',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: _showCreateDialog,
            tooltip: 'Create Lesson Plan',
          ),
        ],
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer<TeacherProvider>(
        builder: (context, provider, child) {
          return Column(
            children: [
              // Filter Section
              Container(
                padding: const EdgeInsets.all(16),
                color: Colors.white,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    DropdownButtonFormField<TeacherClass>(
                      decoration: InputDecoration(
                        labelText: 'Filter by Class/Subject',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.filter_list),
                      ),
                      initialValue: _selectedClass,
                      items: [
                        const DropdownMenuItem<TeacherClass>(
                          value: null,
                          child: Text('All Classes'),
                        ),
                        ...provider.classes.map((cls) {
                          return DropdownMenuItem<TeacherClass>(
                            value: cls,
                            child: Text(
                              '${cls.className} - ${cls.subjectName}',
                              style: GoogleFonts.montserrat(),
                            ),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedClass = value;
                        });
                        final user = Provider.of<AuthProvider>(
                          context,
                          listen: false,
                        ).user;
                        if (user != null) {
                          provider.loadLessonPlans(
                            user.id,
                            classId: value?.classId,
                            subjectId: value?.subjectId,
                          );
                        }
                      },
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
              // Lesson Plans List
              Expanded(
                child: provider.isLoading && provider.lessonPlans.isEmpty
                    ? const Center(child: CircularProgressIndicator())
                    : provider.error != null && provider.lessonPlans.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(
                              Icons.error_outline,
                              size: 64,
                              color: Colors.red,
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Error: ${provider.error}',
                              style: const TextStyle(color: Colors.red),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 16),
                            ElevatedButton(
                              onPressed: _refreshData,
                              child: const Text('Retry'),
                            ),
                          ],
                        ),
                      )
                    : provider.lessonPlans.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.book_outlined,
                              size: 64,
                              color: Colors.grey[400],
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'No lesson plans found',
                              style: GoogleFonts.montserrat(
                                fontSize: 18,
                                color: Colors.grey[600],
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Tap + to create a new lesson plan',
                              style: GoogleFonts.montserrat(
                                fontSize: 14,
                                color: Colors.grey[500],
                              ),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _refreshData,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: provider.lessonPlans.length,
                          itemBuilder: (context, index) {
                            final plan = provider.lessonPlans[index];
                            return _buildLessonPlanCard(plan);
                          },
                        ),
                      ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildLessonPlanCard(TeacherLessonPlan plan) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ExpansionTile(
        leading: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppConstants.primaryColor.withOpacity(0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(Icons.book, color: AppConstants.primaryColor),
        ),
        title: Text(
          plan.title,
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.bold,
            fontSize: 16,
          ),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(
              '${plan.className} - ${plan.subjectName}',
              style: GoogleFonts.montserrat(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 4),
            Text(
              DateFormat(
                'MMM d, yyyy',
              ).format(DateTime.tryParse(plan.date) ?? DateTime.now()),
              style: GoogleFonts.montserrat(
                fontSize: 12,
                color: Colors.grey[500],
              ),
            ),
          ],
        ),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (plan.objectives != null && plan.objectives!.isNotEmpty) ...[
                  _buildSection('Objectives', plan.objectives!),
                  const SizedBox(height: 16),
                ],
                if (plan.activities != null && plan.activities!.isNotEmpty) ...[
                  _buildSection('Activities', plan.activities!),
                  const SizedBox(height: 16),
                ],
                if (plan.materials != null && plan.materials!.isNotEmpty) ...[
                  _buildSection('Materials', plan.materials!),
                  const SizedBox(height: 16),
                ],
                if (plan.homework != null && plan.homework!.isNotEmpty) ...[
                  _buildSection('Homework', plan.homework!),
                  const SizedBox(height: 16),
                ],
                if (plan.notes != null && plan.notes!.isNotEmpty) ...[
                  _buildSection('Notes', plan.notes!),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSection(String title, String content) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.bold,
            fontSize: 14,
            color: AppConstants.primaryColor,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          content,
          style: GoogleFonts.montserrat(fontSize: 14, color: Colors.grey[700]),
        ),
      ],
    );
  }
}

class _CreateLessonPlanDialog extends StatefulWidget {
  final List<TeacherClass> classes;
  final BuildContext parentContext; // Parent page context
  final VoidCallback onSaved;

  const _CreateLessonPlanDialog({
    required this.classes,
    required this.parentContext,
    required this.onSaved,
  });

  @override
  State<_CreateLessonPlanDialog> createState() =>
      _CreateLessonPlanDialogState();
}

class _CreateLessonPlanDialogState extends State<_CreateLessonPlanDialog> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _objectivesController = TextEditingController();
  final _activitiesController = TextEditingController();
  final _materialsController = TextEditingController();
  final _homeworkController = TextEditingController();
  final _notesController = TextEditingController();
  final _dateController = TextEditingController();

  TeacherClass? _selectedClass;
  DateTime _selectedDate = DateTime.now();
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _dateController.text = DateFormat('yyyy-MM-dd').format(_selectedDate);
  }

  @override
  void dispose() {
    _titleController.dispose();
    _objectivesController.dispose();
    _activitiesController.dispose();
    _materialsController.dispose();
    _homeworkController.dispose();
    _notesController.dispose();
    _dateController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        _selectedDate = picked;
        _dateController.text = DateFormat('yyyy-MM-dd').format(picked);
      });
    }
  }

  Future<void> _saveLessonPlan() async {
    if (!_formKey.currentState!.validate()) return;

    if (_selectedClass == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select a class/subject',
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

      final success = await provider.saveLessonPlan(
        userId: user.id,
        title: _titleController.text.trim(),
        date: _dateController.text,
        classId: _selectedClass!.classId,
        subjectId: _selectedClass!.subjectId,
        objectives: _objectivesController.text.trim().isEmpty
            ? null
            : _objectivesController.text.trim(),
        activities: _activitiesController.text.trim().isEmpty
            ? null
            : _activitiesController.text.trim(),
        materials: _materialsController.text.trim().isEmpty
            ? null
            : _materialsController.text.trim(),
        homework: _homeworkController.text.trim().isEmpty
            ? null
            : _homeworkController.text.trim(),
        notes: _notesController.text.trim().isEmpty
            ? null
            : _notesController.text.trim(),
      );

      setState(() {
        _isSaving = false;
      });

      if (success && mounted) {
        // Close dialog first
        Navigator.pop(context);
        // Refresh data
        widget.onSaved();
        // Show success alert using parent context (page context, not dialog context)
        Future.delayed(const Duration(milliseconds: 300), () {
          if (widget.parentContext.mounted) {
            SweetAlert.showSuccess(
              context: widget.parentContext,
              title: 'Success',
              message: 'Lesson plan created successfully!',
            );
          }
        });
      } else if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to create lesson plan',
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
          message: 'Failed to create lesson plan: $e',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(24),
      ),
      elevation: 8,
      child: Container(
        constraints: const BoxConstraints(maxWidth: 600, maxHeight: 750),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(24),
          color: Colors.white,
        ),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Enhanced Header with Gradient
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      AppConstants.primaryColor,
                      AppConstants.primaryColor.withOpacity(0.8),
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(24),
                    topRight: Radius.circular(24),
                  ),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.book_rounded,
                        color: Colors.white,
                        size: 28,
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Create Lesson Plan',
                            style: GoogleFonts.montserrat(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                              letterSpacing: 0.5,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Fill in the details below',
                            style: GoogleFonts.montserrat(
                              fontSize: 13,
                              color: Colors.white.withOpacity(0.9),
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(
                          Icons.close_rounded,
                          color: Colors.white,
                          size: 20,
                        ),
                      ),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
              ),
              // Enhanced Form Content
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Class/Subject Dropdown
                      Container(
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.05),
                              blurRadius: 10,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: DropdownButtonFormField<TeacherClass>(
                          decoration: InputDecoration(
                            labelText: 'Class/Subject *',
                            labelStyle: GoogleFonts.montserrat(
                              fontWeight: FontWeight.w500,
                            ),
                            filled: true,
                            fillColor: Colors.grey[50],
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(16),
                              borderSide: BorderSide(
                                color: Colors.grey[300]!,
                              ),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(16),
                              borderSide: BorderSide(
                                color: Colors.grey[300]!,
                              ),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(16),
                              borderSide: BorderSide(
                                color: AppConstants.primaryColor,
                                width: 2,
                              ),
                            ),
                            prefixIcon: Icon(
                              Icons.class_rounded,
                              color: AppConstants.primaryColor,
                            ),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 20,
                              vertical: 16,
                            ),
                          ),
                          style: GoogleFonts.montserrat(),
                          initialValue: _selectedClass,
                          items: widget.classes.map((cls) {
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
                            });
                          },
                          validator: (value) {
                            if (value == null) {
                              return 'Please select a class/subject';
                            }
                            return null;
                          },
                        ),
                      ),
                      const SizedBox(height: 20),
                      // Title Field
                      _buildEnhancedTextField(
                        controller: _titleController,
                        label: 'Title *',
                        icon: Icons.title_rounded,
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Please enter a title';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 20),
                      // Date Field
                      InkWell(
                        onTap: _selectDate,
                        child: Container(
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.05),
                                blurRadius: 10,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: TextFormField(
                            controller: _dateController,
                            enabled: false,
                            style: GoogleFonts.montserrat(),
                            decoration: InputDecoration(
                              labelText: 'Date *',
                              labelStyle: GoogleFonts.montserrat(
                                fontWeight: FontWeight.w500,
                              ),
                              filled: true,
                              fillColor: Colors.grey[50],
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: Colors.grey[300]!,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: Colors.grey[300]!,
                                ),
                              ),
                              prefixIcon: Icon(
                                Icons.calendar_today_rounded,
                                color: AppConstants.primaryColor,
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 20,
                                vertical: 16,
                              ),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),
                      // Objectives Field
                      _buildEnhancedTextField(
                        controller: _objectivesController,
                        label: 'Objectives',
                        icon: Icons.flag_rounded,
                        maxLines: 3,
                      ),
                      const SizedBox(height: 20),
                      // Activities Field
                      _buildEnhancedTextField(
                        controller: _activitiesController,
                        label: 'Activities',
                        icon: Icons.assignment_rounded,
                        maxLines: 3,
                      ),
                      const SizedBox(height: 20),
                      // Materials Field
                      _buildEnhancedTextField(
                        controller: _materialsController,
                        label: 'Materials',
                        icon: Icons.inventory_2_rounded,
                        maxLines: 2,
                      ),
                      const SizedBox(height: 20),
                      // Homework Field
                      _buildEnhancedTextField(
                        controller: _homeworkController,
                        label: 'Homework',
                        icon: Icons.home_rounded,
                        maxLines: 2,
                      ),
                      const SizedBox(height: 20),
                      // Notes Field
                      _buildEnhancedTextField(
                        controller: _notesController,
                        label: 'Notes',
                        icon: Icons.note_rounded,
                        maxLines: 3,
                      ),
                    ],
                  ),
                ),
              ),
              // Enhanced Footer Buttons
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.grey[50],
                  borderRadius: const BorderRadius.only(
                    bottomLeft: Radius.circular(24),
                    bottomRight: Radius.circular(24),
                  ),
                  border: Border(
                    top: BorderSide(
                      color: Colors.grey[200]!,
                      width: 1,
                    ),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton(
                      onPressed: _isSaving ? null : () => Navigator.pop(context),
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 24,
                          vertical: 12,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: Text(
                        'Cancel',
                        style: GoogleFonts.montserrat(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          color: Colors.grey[700],
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton(
                      onPressed: _isSaving ? null : _saveLessonPlan,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppConstants.primaryColor,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 32,
                          vertical: 14,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 2,
                      ),
                      child: _isSaving
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.5,
                                valueColor: AlwaysStoppedAnimation<Color>(
                                  Colors.white,
                                ),
                              ),
                            )
                          : Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.check_circle_rounded, size: 20),
                                const SizedBox(width: 8),
                                Text(
                                  'Save Lesson Plan',
                                  style: GoogleFonts.montserrat(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEnhancedTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    int maxLines = 1,
    String? Function(String?)? validator,
  }) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: TextFormField(
        controller: controller,
        style: GoogleFonts.montserrat(),
        maxLines: maxLines,
        validator: validator,
        decoration: InputDecoration(
          labelText: label,
          labelStyle: GoogleFonts.montserrat(
            fontWeight: FontWeight.w500,
          ),
          filled: true,
          fillColor: Colors.grey[50],
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide(
              color: Colors.grey[300]!,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide(
              color: Colors.grey[300]!,
            ),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide(
              color: AppConstants.primaryColor,
              width: 2,
            ),
          ),
          prefixIcon: Icon(
            icon,
            color: AppConstants.primaryColor,
          ),
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 20,
            vertical: 16,
          ),
        ),
      ),
    );
  }
}
