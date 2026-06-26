import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/communication_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class SendSmsPage extends StatefulWidget {
  const SendSmsPage({super.key});

  @override
  State<SendSmsPage> createState() => _SendSmsPageState();
}

class _SendSmsPageState extends State<SendSmsPage> {
  final _formKey = GlobalKey<FormState>();
  final _messageController = TextEditingController();
  String? _selectedRecipientType;
  int? _selectedClassId;
  bool _isSubmitting = false;
  static const int _maxSmsLength = 160;

  @override
  void initState() {
    super.initState();
    _messageController.addListener(() {
      setState(() {});
    });
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
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedRecipientType == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select recipient type',
      );
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
            message: 'Please login to send SMS',
          );
        }
        return;
      }

      final provider = context.read<CommunicationProvider>();

      final success = await provider.sendSms(
        recipientType: _selectedRecipientType!,
        recipientId: null,
        classId: _selectedClassId,
        message: _messageController.text.trim(),
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'SMS sent successfully',
          );
          // Clear form
          _messageController.clear();
          setState(() {
            _selectedRecipientType = null;
            _selectedClassId = null;
          });
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message:
                provider.error ??
                'Failed to send SMS. Please check your connection and try again.',
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

  int get _characterCount => _messageController.text.length;
  bool get _isOverLimit => _characterCount > _maxSmsLength;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Send SMS',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
        elevation: 0,
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Recipient Type Dropdown
              DropdownButtonFormField<String>(
                decoration: InputDecoration(
                  labelText: 'Recipient Type *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.people),
                ),
                initialValue: _selectedRecipientType,
                items: const [
                  DropdownMenuItem<String>(value: 'All', child: Text('All')),
                  DropdownMenuItem<String>(
                    value: 'Students',
                    child: Text('Students'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Parents',
                    child: Text('Parents'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Teachers',
                    child: Text('Teachers'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Staff',
                    child: Text('Staff'),
                  ),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedRecipientType = value;
                    _selectedClassId = null;
                  });
                },
                validator: (value) {
                  if (value == null) {
                    return 'Please select recipient type';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // Class Dropdown (conditional)
              if (_selectedRecipientType == 'Students' ||
                  _selectedRecipientType == 'Parents')
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    if (studentProvider.isLoading) {
                      return const LinearProgressIndicator();
                    }

                    if (studentProvider.classes.isEmpty) {
                      return Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.green.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.info_outline, color: Colors.green),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'No classes available',
                                style: TextStyle(color: Colors.green[700]),
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
              // Message Field with Character Counter
              TextFormField(
                controller: _messageController,
                decoration: InputDecoration(
                  labelText: 'Message *',
                  hintText: 'Enter your SMS message (max 160 characters)',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.message),
                  suffixIcon: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Text(
                      '$_characterCount/$_maxSmsLength',
                      style: TextStyle(
                        color: _isOverLimit ? Colors.red : Colors.grey[600],
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
                maxLines: 6,
                maxLength: _maxSmsLength,
                buildCounter:
                    (
                      context, {
                      required currentLength,
                      required isFocused,
                      maxLength,
                    }) => const SizedBox.shrink(),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter a message';
                  }
                  if (value.length > _maxSmsLength) {
                    return 'Message cannot exceed $_maxSmsLength characters';
                  }
                  return null;
                },
              ),
              // Character limit warning
              if (_isOverLimit)
                Container(
                  margin: const EdgeInsets.only(top: 8),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.warning, color: Colors.red[700], size: 20),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Message exceeds SMS limit. It will be split into multiple messages.',
                          style: TextStyle(
                            color: Colors.red[700],
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              const SizedBox(height: 24),
              // Submit Button
              Consumer<CommunicationProvider>(
                builder: (context, provider, child) {
                  final isLoading = provider.isLoading || _isSubmitting;
                  return ElevatedButton(
                    onPressed: isLoading ? null : _submit,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
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
                        : Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.send, color: Colors.white),
                              const SizedBox(width: 8),
                              Text(
                                'Send SMS',
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.w600,
                                  color: Colors.white,
                                  fontSize: 16,
                                ),
                              ),
                            ],
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
