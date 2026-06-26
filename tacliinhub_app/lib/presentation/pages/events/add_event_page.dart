import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/events_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class AddEventPage extends StatefulWidget {
  final DateTime? initialDate;

  const AddEventPage({super.key, this.initialDate});

  @override
  State<AddEventPage> createState() => _AddEventPageState();
}

class _AddEventPageState extends State<AddEventPage> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _locationController = TextEditingController();

  DateTime? _startDate;
  DateTime? _endDate;
  TimeOfDay? _startTime;
  TimeOfDay? _endTime;
  String? _selectedEventType = 'Other';
  String? _selectedTargetAudience;
  int? _selectedClassId;
  String? _selectedStatus = 'Scheduled';
  bool _isAllDay = false;
  bool _isRecurring = false;
  String? _selectedRecurrencePattern;
  int? _recurrenceInterval;
  DateTime? _recurrenceEndDate;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    if (widget.initialDate != null) {
      _startDate = widget.initialDate;
      _endDate = widget.initialDate;
    } else {
      _startDate = DateTime.now();
      _endDate = DateTime.now();
    }
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
    _descriptionController.dispose();
    _locationController.dispose();
    super.dispose();
  }

  Future<void> _selectStartDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _startDate ?? DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365 * 2)),
    );
    if (picked != null) {
      setState(() {
        _startDate = picked;
        if (_endDate == null || _endDate!.isBefore(picked)) {
          _endDate = picked;
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
      initialDate: _endDate ?? _startDate!,
      firstDate: _startDate!,
      lastDate: DateTime.now().add(const Duration(days: 365 * 2)),
    );
    if (picked != null) {
      setState(() {
        _endDate = picked;
      });
    }
  }

  Future<void> _selectStartTime() async {
    final picked = await showTimePicker(
      context: context,
      initialTime: _startTime ?? TimeOfDay.now(),
    );
    if (picked != null) {
      setState(() {
        _startTime = picked;
      });
    }
  }

  Future<void> _selectEndTime() async {
    final picked = await showTimePicker(
      context: context,
      initialTime: _endTime ?? TimeOfDay.now(),
    );
    if (picked != null) {
      setState(() {
        _endTime = picked;
      });
    }
  }

  Future<void> _selectRecurrenceEndDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate:
          _recurrenceEndDate ?? DateTime.now().add(const Duration(days: 30)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365 * 2)),
    );
    if (picked != null) {
      setState(() {
        _recurrenceEndDate = picked;
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_startDate == null || _endDate == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select start and end dates',
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
            message: 'Please login to create events',
          );
        }
        return;
      }

      final provider = context.read<EventsProvider>();

      String? startTimeStr;
      String? endTimeStr;
      if (!_isAllDay) {
        if (_startTime != null) {
          startTimeStr =
              '${_startTime!.hour.toString().padLeft(2, '0')}:${_startTime!.minute.toString().padLeft(2, '0')}';
        }
        if (_endTime != null) {
          endTimeStr =
              '${_endTime!.hour.toString().padLeft(2, '0')}:${_endTime!.minute.toString().padLeft(2, '0')}';
        }
      }

      final success = await provider.addEvent(
        title: _titleController.text.trim(),
        description: _descriptionController.text.trim(),
        startDate: _startDate!,
        endDate: _endDate!,
        startTime: startTimeStr,
        endTime: endTimeStr,
        eventType: _selectedEventType!,
        location: _locationController.text.trim().isEmpty
            ? null
            : _locationController.text.trim(),
        isAllDay: _isAllDay,
        isRecurring: _isRecurring,
        recurrencePattern: _isRecurring ? _selectedRecurrencePattern : null,
        recurrenceInterval: _isRecurring ? _recurrenceInterval : null,
        recurrenceEndDate: _isRecurring ? _recurrenceEndDate : null,
        targetAudience: _selectedTargetAudience,
        classId: _selectedClassId,
        status: _selectedStatus,
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Event created successfully',
          );
          Navigator.pop(context, true);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message:
                provider.error ?? 'Failed to create event. Please try again.',
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
          'Add Event',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.pink,
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
                  hintText: 'Enter event title',
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
              // Description Field
              TextFormField(
                controller: _descriptionController,
                decoration: InputDecoration(
                  labelText: 'Description *',
                  hintText: 'Enter event description',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  alignLabelWithHint: true,
                ),
                maxLines: 4,
                textCapitalization: TextCapitalization.sentences,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter description';
                  }
                  if (value.trim().length < 10) {
                    return 'Description must be at least 10 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // Event Type Dropdown
              DropdownButtonFormField<String>(
                decoration: InputDecoration(
                  labelText: 'Event Type *',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.category),
                ),
                initialValue: _selectedEventType,
                items: const [
                  DropdownMenuItem<String>(
                    value: 'Academic',
                    child: Text('Academic'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Holiday',
                    child: Text('Holiday'),
                  ),
                  DropdownMenuItem<String>(value: 'Exam', child: Text('Exam')),
                  DropdownMenuItem<String>(
                    value: 'Activity',
                    child: Text('Activity'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Meeting',
                    child: Text('Meeting'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Other',
                    child: Text('Other'),
                  ),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedEventType = value;
                  });
                },
                validator: (value) {
                  if (value == null) {
                    return 'Please select event type';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              // Start Date
              InkWell(
                onTap: _selectStartDate,
                child: InputDecorator(
                  decoration: InputDecoration(
                    labelText: 'Start Date *',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.calendar_today),
                  ),
                  child: Text(
                    _startDate != null
                        ? DateFormat('yyyy-MM-dd').format(_startDate!)
                        : 'Select start date',
                  ),
                ),
              ),
              const SizedBox(height: 16),
              // End Date
              InkWell(
                onTap: _selectEndDate,
                child: InputDecorator(
                  decoration: InputDecoration(
                    labelText: 'End Date *',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.event),
                  ),
                  child: Text(
                    _endDate != null
                        ? DateFormat('yyyy-MM-dd').format(_endDate!)
                        : 'Select end date',
                  ),
                ),
              ),
              const SizedBox(height: 16),
              // All Day Toggle
              SwitchListTile(
                title: const Text('All Day Event'),
                value: _isAllDay,
                onChanged: (value) {
                  setState(() {
                    _isAllDay = value;
                    if (value) {
                      _startTime = null;
                      _endTime = null;
                    }
                  });
                },
                contentPadding: EdgeInsets.zero,
              ),
              // Time Fields (if not all day)
              if (!_isAllDay) ...[
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: InkWell(
                        onTap: _selectStartTime,
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'Start Time',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.access_time),
                          ),
                          child: Text(
                            _startTime != null
                                ? _startTime!.format(context)
                                : 'Select start time',
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: InkWell(
                        onTap: _selectEndTime,
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'End Time',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.access_time),
                          ),
                          child: Text(
                            _endTime != null
                                ? _endTime!.format(context)
                                : 'Select end time',
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
              const SizedBox(height: 16),
              // Location Field
              TextFormField(
                controller: _locationController,
                decoration: InputDecoration(
                  labelText: 'Location',
                  hintText: 'Enter event location (optional)',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  prefixIcon: const Icon(Icons.location_on),
                ),
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
                          color: Colors.pink.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.info_outline, color: Colors.pink),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'No classes available',
                                style: TextStyle(color: Colors.pink[700]),
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
                    value: 'Scheduled',
                    child: Text('Scheduled'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Ongoing',
                    child: Text('Ongoing'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'Completed',
                    child: Text('Completed'),
                  ),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedStatus = value;
                  });
                },
              ),
              const SizedBox(height: 16),
              // Recurring Toggle
              SwitchListTile(
                title: const Text('Recurring Event'),
                value: _isRecurring,
                onChanged: (value) {
                  setState(() {
                    _isRecurring = value;
                    if (!value) {
                      _selectedRecurrencePattern = null;
                      _recurrenceInterval = null;
                      _recurrenceEndDate = null;
                    }
                  });
                },
                contentPadding: EdgeInsets.zero,
              ),
              // Recurrence Options (if recurring)
              if (_isRecurring) ...[
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  decoration: InputDecoration(
                    labelText: 'Recurrence Pattern',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.repeat),
                  ),
                  initialValue: _selectedRecurrencePattern,
                  items: const [
                    DropdownMenuItem<String>(
                      value: 'Daily',
                      child: Text('Daily'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Weekly',
                      child: Text('Weekly'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Monthly',
                      child: Text('Monthly'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Yearly',
                      child: Text('Yearly'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedRecurrencePattern = value;
                    });
                  },
                ),
                const SizedBox(height: 16),
                TextFormField(
                  decoration: InputDecoration(
                    labelText: 'Recurrence Interval (e.g., every 2 weeks)',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.repeat_one),
                  ),
                  keyboardType: TextInputType.number,
                  onChanged: (value) {
                    setState(() {
                      _recurrenceInterval = int.tryParse(value);
                    });
                  },
                ),
                const SizedBox(height: 16),
                InkWell(
                  onTap: _selectRecurrenceEndDate,
                  child: InputDecorator(
                    decoration: InputDecoration(
                      labelText: 'Recurrence End Date',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      prefixIcon: const Icon(Icons.event_available),
                    ),
                    child: Text(
                      _recurrenceEndDate != null
                          ? DateFormat('yyyy-MM-dd').format(_recurrenceEndDate!)
                          : 'Select end date (optional)',
                    ),
                  ),
                ),
              ],
              const SizedBox(height: 24),
              // Submit Button
              Consumer<EventsProvider>(
                builder: (context, provider, child) {
                  final isLoading = provider.isLoading || _isSubmitting;
                  return ElevatedButton(
                    onPressed: isLoading ? null : _submit,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.pink,
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
                            'Create Event',
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
