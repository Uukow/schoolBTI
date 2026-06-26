import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/fees_provider.dart';
import '../../providers/student_provider.dart';
import '../../widgets/branch_selector.dart';
import '../../../core/sweet_alert.dart';

class FeeStructurePage extends StatefulWidget {
  const FeeStructurePage({super.key});

  @override
  State<FeeStructurePage> createState() => _FeeStructurePageState();
}

class _FeeStructurePageState extends State<FeeStructurePage> {
  int? _selectedClassId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FeesProvider>().loadFeeTypes();
      context.read<FeesProvider>().loadFeeStructures(context: context);
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Fee Structure',
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
            onPressed: () => _showAddStructureDialog(context),
            tooltip: 'Add Fee Structure',
          ),
        ],
      ),
      body: Column(
        children: [
          // Branch Selector (for Super Admin)
          const BranchSelector(),
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
                    context.read<FeesProvider>().loadFeeStructures(
                      classId: value,
                      context: context,
                    );
                  },
                );
              },
            ),
          ),

          // Fee Structures List
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
                        Text(provider.error ?? 'Error loading fee structures'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () {
                            provider.loadFeeStructures(
                              classId: _selectedClassId,
                              context: context,
                            );
                          },
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                final structures = _selectedClassId == null
                    ? provider.feeStructures
                    : provider.feeStructures
                          .where((s) => s.classId == _selectedClassId)
                          .toList();

                if (structures.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.account_balance_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No fee structures found',
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
                  itemCount: structures.length,
                  itemBuilder: (context, index) {
                    final structure = structures[index];
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
                            Icons.account_balance,
                            color: Colors.blue,
                          ),
                        ),
                        title: Text(
                          structure.feeTypeName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(structure.className),
                            Text(
                              'Amount: ${structure.amount.toStringAsFixed(2)}',
                              style: GoogleFonts.montserrat(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: Colors.green[700],
                              ),
                            ),
                            Text(
                              'Frequency: ${structure.frequency}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            if (structure.dueDate != null)
                              Text(
                                'Due: ${DateFormat('MMM d, yyyy').format(structure.dueDate!)}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                          ],
                        ),
                        trailing: structure.isMandatory
                            ? const Chip(
                                label: Text('Mandatory'),
                                backgroundColor: Colors.orange,
                                labelStyle: TextStyle(fontSize: 10),
                              )
                            : null,
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
        onPressed: () => _showAddStructureDialog(context),
        backgroundColor: Colors.blue,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _showAddStructureDialog(BuildContext context) {
    final formKey = GlobalKey<FormState>();
    final amountController = TextEditingController();
    int? selectedFeeTypeId;
    int? selectedClassId;
    String selectedFrequency = 'Monthly';
    DateTime? dueDate;
    bool isMandatory = true;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Add Fee Structure',
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
                    controller: amountController,
                    decoration: InputDecoration(
                      labelText: 'Amount *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      prefixText: '\$ ',
                    ),
                    keyboardType: const TextInputType.numberWithOptions(
                      decimal: true,
                    ),
                    validator: (value) =>
                        value?.isEmpty ?? true ? 'Please enter amount' : null,
                  ),
                  const SizedBox(height: 16),
                  DropdownButtonFormField<String>(
                    decoration: InputDecoration(
                      labelText: 'Frequency *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    initialValue: selectedFrequency,
                    items: const [
                      DropdownMenuItem(
                        value: 'One Time',
                        child: Text('One Time'),
                      ),
                      DropdownMenuItem(
                        value: 'Monthly',
                        child: Text('Monthly'),
                      ),
                      DropdownMenuItem(
                        value: 'Quarterly',
                        child: Text('Quarterly'),
                      ),
                      DropdownMenuItem(
                        value: 'Annually',
                        child: Text('Annually'),
                      ),
                    ],
                    onChanged: (value) {
                      setState(() {
                        selectedFrequency = value!;
                      });
                    },
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
                  const SizedBox(height: 16),
                  CheckboxListTile(
                    title: const Text('Mandatory'),
                    value: isMandatory,
                    onChanged: (value) {
                      setState(() {
                        isMandatory = value ?? true;
                      });
                    },
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
                  selectedClassId != null) {
                final provider = context.read<FeesProvider>();
                final sessionId = 1; // TODO: Get from session provider

                final success = await provider.createFeeStructure(
                  classId: selectedClassId!,
                  feeTypeId: selectedFeeTypeId!,
                  sessionId: sessionId,
                  amount: double.parse(amountController.text),
                  frequency: selectedFrequency,
                  dueDate: dueDate,
                  isMandatory: isMandatory,
                );

                if (mounted) {
                  Navigator.pop(context);
                  if (success) {
                    SweetAlert.showSuccess(
                      context: context,
                      title: 'Success',
                      message: 'Fee structure created successfully!',
                    );
                  } else {
                    SweetAlert.showError(
                      context: context,
                      title: 'Error',
                      message:
                          provider.error ?? 'Failed to create fee structure',
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
