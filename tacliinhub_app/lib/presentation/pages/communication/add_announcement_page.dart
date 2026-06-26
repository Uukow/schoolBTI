import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/communication_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class AddAnnouncementPage extends StatefulWidget {
  const AddAnnouncementPage({super.key});

  @override
  State<AddAnnouncementPage> createState() => _AddAnnouncementPageState();
}

class _AddAnnouncementPageState extends State<AddAnnouncementPage> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _contentController = TextEditingController();
  String? _selectedTargetAudience;
  int? _selectedClassId;
  String? _selectedStatus = 'Draft';
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      try {
        final studentProvider = context.read<StudentProvider>();
        if (studentProvider.classes.isEmpty) {
          studentProvider.loadClasses();
        }
      } catch (e) {
        if (mounted) {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: 'Failed to load classes: ${e.toString()}',
          );
        }
      }
    });
  }

  @override
  void dispose() {
    _titleController.dispose();
    _contentController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_isSubmitting) return;

    setState(() {
      _isSubmitting = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user?.id == null) {
        if (mounted) {
          SweetAlert.showError(
            context: context,
            title: 'Authentication Error',
            message: 'Please login to create announcements',
          );
        }
        return;
      }

      final provider = context.read<CommunicationProvider>();

      final success = await provider.addAnnouncement(
        title: _titleController.text.trim(),
        content: _contentController.text.trim(),
        targetAudience: _selectedTargetAudience,
        classId: _selectedClassId,
        status: _selectedStatus ?? 'Draft',
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message:
                'Announcement ${_selectedStatus == 'Published' ? 'published' : 'saved as draft'} successfully',
          );
          Navigator.pop(context, true);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message:
                provider.error ??
                'Failed to add announcement. Please try again.',
          );
        }
      }
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'An unexpected error occurred: ${e.toString()}',
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmitting = false;
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
          'Add Announcement',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
        elevation: 0,
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Title Field
              TextFormField(
                controller: _titleController,
                decoration: InputDecoration(
                  labelText: 'Title *',
                  hintText: 'Enter announcement title',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.title),
                ),
                textCapitalization: TextCapitalization.words,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter a title';
                  }
                  if (value.trim().length < 3) {
                    return 'Title must be at least 3 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // Content Field
              TextFormField(
                controller: _contentController,
                decoration: InputDecoration(
                  labelText: 'Content *',
                  hintText: 'Enter announcement content',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  alignLabelWithHint: true,
                ),
                maxLines: 8,
                textCapitalization: TextCapitalization.sentences,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter content';
                  }
                  if (value.trim().length < 10) {
                    return 'Content must be at least 10 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // Target Audience Dropdown
              DropdownButtonFormField<String>(
                decoration: InputDecoration(
                  labelText: 'Target Audience',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.people),
                ),
                initialValue: _selectedTargetAudience,
                items: const [
                  DropdownMenuItem<String>(value: null, child: Text('All')),
                  DropdownMenuItem<String>(
                    value: 'Students',
                    child: Text('Students'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Teachers',
                    child: Text('Teachers'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Parents',
                    child: Text('Parents'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Staff',
                    child: Text('Staff'),
                  ),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedTargetAudience = value;
                    if (value != 'Students' && value != 'Parents') {
                      _selectedClassId = null;
                    }
                  });
                },
              ),
              const SizedBox(height: 16),
              // Class Dropdown (conditional)
              if (_selectedTargetAudience == 'Students' ||
                  _selectedTargetAudience == 'Parents')
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    if (studentProvider.isLoading) {
                      return const LinearProgressIndicator();
                    }

                    if (studentProvider.classes.isEmpty) {
                      return Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.orange.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.info_outline, color: Colors.orange),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'No classes available',
                                style: TextStyle(color: Colors.orange[700]),
                              ),
                            ),
                          ],
                        ),
                      );
                    }

                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Class (Optional)',
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
                        });
                      },
                    );
                  },
                ),
              const SizedBox(height: 16),
              // Status Dropdown
              DropdownButtonFormField<String>(
                decoration: InputDecoration(
                  labelText: 'Status',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.flag),
                ),
                initialValue: _selectedStatus,
                items: const [
                  DropdownMenuItem<String>(
                    value: 'Draft',
                    child: Text('Draft'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Published',
                    child: Text('Published'),
                  ),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedStatus = value;
                  });
                },
              ),
              const SizedBox(height: 24),
              // Submit Button
              Consumer<CommunicationProvider>(
                builder: (context, provider, child) {
                  final isLoading = provider.isLoading || _isSubmitting;
                  return ElevatedButton(
                    onPressed: isLoading ? null : _submit,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.orange,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      elevation: 2,
                    ),
                    child: isLoading
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
                            _selectedStatus == 'Published'
                                ? 'Publish Announcement'
                                : 'Save as Draft',
                            style: GoogleFonts.montserrat(
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                              fontSize: 16,
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
