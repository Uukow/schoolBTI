import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/fees_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class MonthlyAssignmentPage extends StatefulWidget {
  const MonthlyAssignmentPage({super.key});

  @override
  State<MonthlyAssignmentPage> createState() => _MonthlyAssignmentPageState();
}

class _MonthlyAssignmentPageState extends State<MonthlyAssignmentPage> {
  int? _selectedClassId;
  int? _selectedFeeTypeId;
  String? _selectedMonth;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FeesProvider>().loadFeeTypes();
      context.read<FeesProvider>().loadMonthlyAssignments();
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Monthly Fee Assignment',
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
            onPressed: () => _showAssignDialog(context),
            tooltip: 'Assign Monthly Fees',
          ),
        ],
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                Consumer<FeesProvider>(
                  builder: (context, feesProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Fee Type',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.account_balance),
                      ),
                      initialValue: _selectedFeeTypeId,
                      items: [
                        const DropdownMenuItem<int>(
                          value: null,
                          child: Text('All Fee Types'),
                        ),
                        ...feesProvider.feeTypes.map((type) {
                          return DropdownMenuItem<int>(
                            value: type.id,
                            child: Text(type.feeName),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedFeeTypeId = value;
                        });
                        _loadAssignments();
                      },
                    );
                  },
                ),
                const SizedBox(height: 16),
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Class',
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
                        _loadAssignments();
                      },
                    );
                  },
                ),
              ],
            ),
          ),

          // Assignments List
          Expanded(
            child: Consumer<FeesProvider>(
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
                        Text(provider.error ?? 'Error loading assignments'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadAssignments,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var assignments = provider.monthlyAssignments;
                if (_selectedClassId != null) {
                  assignments = assignments
                      .where((a) => a.classId == _selectedClassId)
                      .toList();
                }
                if (_selectedFeeTypeId != null) {
                  assignments = assignments
                      .where((a) => a.feeTypeId == _selectedFeeTypeId)
                      .toList();
                }
                if (_selectedMonth != null) {
                  assignments = assignments
                      .where((a) => a.month == _selectedMonth)
                      .toList();
                }

                if (assignments.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.calendar_month_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No monthly assignments found',
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
                  itemCount: assignments.length,
                  itemBuilder: (context, index) {
                    final assignment = assignments[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: _getStatusColor(
                            assignment.status,
                          ).withOpacity(0.1),
                          child: Icon(
                            _getStatusIcon(assignment.status),
                            color: _getStatusColor(assignment.status),
                          ),
                        ),
                        title: Text(
                          assignment.studentName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(
                              '${assignment.className} - ${assignment.feeTypeName}',
                            ),
                            Text(
                              'Month: ${assignment.month}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            Text(
                              'Amount: ${assignment.amount.toStringAsFixed(2)} | Paid: ${assignment.paidAmount.toStringAsFixed(2)} | Due: ${assignment.dueAmount.toStringAsFixed(2)}',
                              style: GoogleFonts.montserrat(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        trailing: Chip(
                          label: Text(assignment.status),
                          backgroundColor: _getStatusColor(
                            assignment.status,
                          ).withOpacity(0.1),
                          labelStyle: TextStyle(
                            color: _getStatusColor(assignment.status),
                            fontSize: 10,
                          ),
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
        onPressed: () => _showAssignDialog(context),
        backgroundColor: Colors.purple,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadAssignments() {
    context.read<FeesProvider>().loadMonthlyAssignments(
      classId: _selectedClassId,
      feeTypeId: _selectedFeeTypeId,
      month: _selectedMonth,
    );
  }

  void _showAssignDialog(BuildContext context) {
    final formKey = GlobalKey<FormState>();
    int? selectedFeeTypeId;
    int? selectedClassId;
    DateTime? selectedMonth;
    DateTime? dueDate;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Assign Monthly Fees',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        content: StatefulBuilder(
          builder: (context, setState) => Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Consumer<FeesProvider>(
                    builder: (context, feesProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Fee Type *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        initialValue: selectedFeeTypeId,
                        items: feesProvider.feeTypes.map((type) {
                          return DropdownMenuItem<int>(
                            value: type.id,
                            child: Text(type.feeName),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            selectedFeeTypeId = value;
                          });
                        },
                        validator: (value) =>
                            value == null ? 'Please select fee type' : null,
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  Consumer<StudentProvider>(
                    builder: (context, studentProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Class (Optional)',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                          helperText: 'Leave empty to assign to all classes',
                        ),
                        initialValue: selectedClassId,
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
                            selectedClassId = value;
                          });
                        },
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final now = DateTime.now();
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: selectedMonth ?? now,
                        firstDate: DateTime(now.year, now.month - 12),
                        lastDate: DateTime(now.year, now.month + 12),
                        initialDatePickerMode: DatePickerMode.year,
                      );
                      if (picked != null) {
                        setState(() {
                          selectedMonth = DateTime(picked.year, picked.month);
                        });
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Month *',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                        helperText: 'Select month for fee assignment',
                      ),
                      child: Text(
                        selectedMonth != null
                            ? DateFormat('MMMM yyyy').format(selectedMonth!)
                            : 'Select month',
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: dueDate ?? DateTime.now(),
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 365)),
                      );
                      if (picked != null) {
                        setState(() {
                          dueDate = picked;
                        });
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Due Date (Optional)',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text(
                        dueDate != null
                            ? DateFormat('yyyy-MM-dd').format(dueDate!)
                            : 'Select date',
                      ),
                    ),
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
                  selectedFeeTypeId != null &&
                  selectedMonth != null) {
                final provider = context.read<FeesProvider>();
                final sessionId = 1; // TODO: Get from session provider
                final monthStr =
                    '${selectedMonth!.year}-${selectedMonth!.month.toString().padLeft(2, '0')}';

                final success = await provider.assignMonthlyFees(
                  month: monthStr,
                  feeTypeId: selectedFeeTypeId!,
                  classId: selectedClassId,
                  sessionId: sessionId,
                  dueDate: dueDate,
                );

                if (mounted) {
                  Navigator.pop(context);
                  if (success) {
                    SweetAlert.showSuccess(
                      context: context,
                      title: 'Success',
                      message: 'Monthly fees assigned successfully!',
                    );
                    _loadAssignments();
                  } else {
                    SweetAlert.showError(
                      context: context,
                      title: 'Error',
                      message:
                          provider.error ?? 'Failed to assign monthly fees',
                    );
                  }
                }
              }
            },
            child: const Text('Assign'),
          ),
        ],
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'paid':
        return Colors.green;
      case 'partially paid':
        return Colors.orange;
      case 'overdue':
        return Colors.red;
      case 'waived':
        return Colors.grey;
      default:
        return Colors.blue;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'paid':
        return Icons.check_circle;
      case 'partially paid':
        return Icons.pending;
      case 'overdue':
        return Icons.warning;
      case 'waived':
        return Icons.block;
      default:
        return Icons.assignment;
    }
  }
}
