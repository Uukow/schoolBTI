import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/fees_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class InvoicesPage extends StatefulWidget {
  const InvoicesPage({super.key});

  @override
  State<InvoicesPage> createState() => _InvoicesPageState();
}

class _InvoicesPageState extends State<InvoicesPage> {
  int? _selectedClassId;
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FeesProvider>().loadFeeTypes();
      context.read<FeesProvider>().loadInvoices();
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Invoices',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.teal,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => _showGenerateInvoiceDialog(context),
            tooltip: 'Generate Invoice',
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
                Consumer<StudentProvider>(
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
                        _loadInvoices();
                      },
                    );
                  },
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  decoration: InputDecoration(
                    labelText: 'Filter by Status',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.filter_list),
                  ),
                  initialValue: _selectedStatus,
                  items: const [
                    DropdownMenuItem<String>(
                      value: null,
                      child: Text('All Statuses'),
                    ),
                    DropdownMenuItem(value: 'Unpaid', child: Text('Unpaid')),
                    DropdownMenuItem(
                      value: 'Partially Paid',
                      child: Text('Partially Paid'),
                    ),
                    DropdownMenuItem(value: 'Paid', child: Text('Paid')),
                    DropdownMenuItem(value: 'Overdue', child: Text('Overdue')),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                    _loadInvoices();
                  },
                ),
              ],
            ),
          ),

          // Invoices List
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
                        Text(provider.error ?? 'Error loading invoices'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadInvoices,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var invoices = provider.invoices;
                if (_selectedStatus != null) {
                  invoices = invoices
                      .where((i) => i.status == _selectedStatus)
                      .toList();
                }

                if (invoices.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.receipt_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No invoices found',
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
                  itemCount: invoices.length,
                  itemBuilder: (context, index) {
                    final invoice = invoices[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: _getStatusColor(
                            invoice.status,
                          ).withOpacity(0.1),
                          child: Icon(
                            _getStatusIcon(invoice.status),
                            color: _getStatusColor(invoice.status),
                          ),
                        ),
                        title: Text(
                          invoice.invoiceNo,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(invoice.studentName),
                            Text(
                              'Total: ${invoice.totalAmount.toStringAsFixed(2)} | Paid: ${invoice.paidAmount.toStringAsFixed(2)} | Due: ${invoice.dueAmount.toStringAsFixed(2)}',
                              style: GoogleFonts.montserrat(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            if (invoice.dueDate != null)
                              Text(
                                'Due: ${DateFormat('MMM d, yyyy').format(invoice.dueDate!)}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                          ],
                        ),
                        trailing: Chip(
                          label: Text(invoice.status),
                          backgroundColor: _getStatusColor(
                            invoice.status,
                          ).withOpacity(0.1),
                          labelStyle: TextStyle(
                            color: _getStatusColor(invoice.status),
                            fontSize: 10,
                          ),
                        ),
                        onTap: () {
                          // TODO: Show invoice details
                        },
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
        onPressed: () => _showGenerateInvoiceDialog(context),
        backgroundColor: Colors.teal,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadInvoices() {
    context.read<FeesProvider>().loadInvoices(
      classId: _selectedClassId,
      status: _selectedStatus,
    );
  }

  void _showGenerateInvoiceDialog(BuildContext context) {
    final formKey = GlobalKey<FormState>();
    final amountController = TextEditingController();
    final discountController = TextEditingController(text: '0');
    int? selectedStudentId;
    int? selectedFeeTypeId;
    DateTime? dueDate;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Generate Invoice',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        content: StatefulBuilder(
          builder: (context, setState) => Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Consumer<StudentProvider>(
                    builder: (context, studentProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Student *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        initialValue: selectedStudentId,
                        items: studentProvider.students.map((student) {
                          final s = student as dynamic;
                          final name =
                              '${s?.firstName ?? ''} ${s?.lastName ?? ''}'
                                  .trim();
                          return DropdownMenuItem<int>(
                            value: s?.id ?? 0,
                            child: Text(
                              name.isEmpty ? 'Unknown Student' : name,
                            ),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            selectedStudentId = value;
                          });
                        },
                        validator: (value) =>
                            value == null ? 'Please select student' : null,
                      );
                    },
                  ),
                  const SizedBox(height: 16),
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
                  TextFormField(
                    controller: discountController,
                    decoration: InputDecoration(
                      labelText: 'Discount',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      prefixText: '\$ ',
                    ),
                    keyboardType: const TextInputType.numberWithOptions(
                      decimal: true,
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
                  selectedStudentId != null &&
                  selectedFeeTypeId != null) {
                final provider = context.read<FeesProvider>();
                final success = await provider.generateInvoice(
                  studentId: selectedStudentId!,
                  feeTypeId: selectedFeeTypeId!,
                  amount: double.parse(amountController.text),
                  discount: double.tryParse(discountController.text) ?? 0,
                  dueDate: dueDate,
                );

                if (mounted) {
                  Navigator.pop(context);
                  if (success) {
                    SweetAlert.showSuccess(
                      context: context,
                      title: 'Success',
                      message: 'Invoice generated successfully!',
                    );
                    _loadInvoices();
                  } else {
                    SweetAlert.showError(
                      context: context,
                      title: 'Error',
                      message: provider.error ?? 'Failed to generate invoice',
                    );
                  }
                }
              }
            },
            child: const Text('Generate'),
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
        return Icons.receipt;
    }
  }
}
