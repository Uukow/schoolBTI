import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/fees_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class PaymentsPage extends StatefulWidget {
  const PaymentsPage({super.key});

  @override
  State<PaymentsPage> createState() => _PaymentsPageState();
}

class _PaymentsPageState extends State<PaymentsPage> {
  int? _selectedStudentId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FeesProvider>().loadPayments();
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
          'Payments',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.indigo,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => _showRecordPaymentDialog(context),
            tooltip: 'Record Payment',
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
                    labelText: 'Filter by Student',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.person),
                  ),
                  initialValue: _selectedStudentId,
                  items: [
                    const DropdownMenuItem<int>(
                      value: null,
                      child: Text('All Students'),
                    ),
                    ...studentProvider.students.map((student) {
                      final s = student as dynamic;
                      final name = '${s?.firstName ?? ''} ${s?.lastName ?? ''}'
                          .trim();
                      return DropdownMenuItem<int>(
                        value: s?.id ?? 0,
                        child: Text(name.isEmpty ? 'Unknown Student' : name),
                      );
                    }),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStudentId = value;
                    });
                    _loadPayments();
                  },
                );
              },
            ),
          ),

          // Payments List
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
                        Text(provider.error ?? 'Error loading payments'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadPayments,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var payments = provider.payments;
                if (_selectedStudentId != null) {
                  payments = payments
                      .where((p) => p.studentId == _selectedStudentId)
                      .toList();
                }

                if (payments.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.attach_money_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No payments found',
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
                  itemCount: payments.length,
                  itemBuilder: (context, index) {
                    final payment = payments[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: Colors.green.withOpacity(0.1),
                          child: const Icon(Icons.payment, color: Colors.green),
                        ),
                        title: Text(
                          payment.receiptNo,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(payment.studentName),
                            Text(
                              'Amount: ${payment.amount.toStringAsFixed(2)}',
                              style: GoogleFonts.montserrat(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: Colors.green[700],
                              ),
                            ),
                            Text(
                              'Method: ${payment.paymentMethod}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            if (payment.transactionId != null)
                              Text(
                                'Transaction: ${payment.transactionId}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            Text(
                              'Date: ${DateFormat('MMM d, yyyy').format(payment.paymentDate)}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                          ],
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
        onPressed: () => _showRecordPaymentDialog(context),
        backgroundColor: Colors.indigo,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadPayments() {
    context.read<FeesProvider>().loadPayments(studentId: _selectedStudentId);
  }

  void _showRecordPaymentDialog(BuildContext context) {
    final formKey = GlobalKey<FormState>();
    final amountController = TextEditingController();
    final transactionIdController = TextEditingController();
    final remarksController = TextEditingController();
    int? selectedInvoiceId;
    String selectedPaymentMethod = 'Cash';
    DateTime paymentDate = DateTime.now();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Record Payment',
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
                          labelText: 'Invoice *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        initialValue: selectedInvoiceId,
                        items: feesProvider.invoices.map((invoice) {
                          return DropdownMenuItem<int>(
                            value: invoice.id,
                            child: Text(
                              '${invoice.invoiceNo} - ${invoice.studentName} (Due: ${invoice.dueAmount.toStringAsFixed(2)})',
                            ),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            selectedInvoiceId = value;
                          });
                        },
                        validator: (value) =>
                            value == null ? 'Please select invoice' : null,
                      );
                    },
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: amountController,
                    decoration: InputDecoration(
                      labelText: 'Payment Amount *',
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
                      labelText: 'Payment Method *',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    initialValue: selectedPaymentMethod,
                    items: const [
                      DropdownMenuItem(value: 'Cash', child: Text('Cash')),
                      DropdownMenuItem(
                        value: 'Bank Transfer',
                        child: Text('Bank Transfer'),
                      ),
                      DropdownMenuItem(
                        value: 'Credit Card',
                        child: Text('Credit Card'),
                      ),
                      DropdownMenuItem(
                        value: 'Debit Card',
                        child: Text('Debit Card'),
                      ),
                      DropdownMenuItem(value: 'Online', child: Text('Online')),
                      DropdownMenuItem(value: 'EVC', child: Text('EVC')),
                      DropdownMenuItem(value: 'Zaad', child: Text('Zaad')),
                      DropdownMenuItem(
                        value: 'Mobile Money',
                        child: Text('Mobile Money'),
                      ),
                    ],
                    onChanged: (value) {
                      setState(() {
                        selectedPaymentMethod = value!;
                      });
                    },
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: paymentDate,
                        firstDate: DateTime.now().subtract(
                          const Duration(days: 365),
                        ),
                        lastDate: DateTime.now(),
                      );
                      if (picked != null) {
                        setState(() {
                          paymentDate = picked;
                        });
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Payment Date *',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text(DateFormat('yyyy-MM-dd').format(paymentDate)),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: transactionIdController,
                    decoration: InputDecoration(
                      labelText: 'Transaction ID (Optional)',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: remarksController,
                    decoration: InputDecoration(
                      labelText: 'Remarks (Optional)',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    maxLines: 2,
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
                  selectedInvoiceId != null) {
                final provider = context.read<FeesProvider>();
                final success = await provider.recordPayment(
                  invoiceId: selectedInvoiceId!,
                  amount: double.parse(amountController.text),
                  paymentMethod: selectedPaymentMethod,
                  paymentDate: paymentDate,
                  transactionId: transactionIdController.text.isEmpty
                      ? null
                      : transactionIdController.text,
                  remarks: remarksController.text.isEmpty
                      ? null
                      : remarksController.text,
                );

                if (mounted) {
                  Navigator.pop(context);
                  if (success) {
                    SweetAlert.showSuccess(
                      context: context,
                      title: 'Success',
                      message: 'Payment recorded successfully!',
                    );
                    _loadPayments();
                  } else {
                    SweetAlert.showError(
                      context: context,
                      title: 'Error',
                      message: provider.error ?? 'Failed to record payment',
                    );
                  }
                }
              }
            },
            child: const Text('Record'),
          ),
        ],
      ),
    );
  }
}
