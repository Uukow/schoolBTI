import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/fees_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class FlexiblePaymentPage extends StatefulWidget {
  const FlexiblePaymentPage({super.key});

  @override
  State<FlexiblePaymentPage> createState() => _FlexiblePaymentPageState();
}

class _FlexiblePaymentPageState extends State<FlexiblePaymentPage> {
  int? _selectedStudentId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Flexible Payment',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Student Selection
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<StudentProvider>(
              builder: (context, studentProvider, child) {
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Select Student',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.person),
                  ),
                  initialValue: _selectedStudentId,
                  items: studentProvider.students.map((student) {
                    final s = student as dynamic;
                    final name = '${s?.firstName ?? ''} ${s?.lastName ?? ''}'
                        .trim();
                    final admissionNo = s?.admissionNo ?? '';
                    final studentId = s?.id ?? 0;
                    return DropdownMenuItem<int>(
                      value: studentId,
                      child: Text(
                        name.isEmpty
                            ? 'Unknown Student (ID: $studentId)'
                            : '$name${admissionNo.isNotEmpty ? ' ($admissionNo)' : ' (ID: $studentId)'}',
                      ),
                    );
                  }).toList(),
                  onChanged: (value) {
                    setState(() {
                      _selectedStudentId = value;
                    });
                  },
                );
              },
            ),
          ),

          // Payment Form
          Expanded(
            child: _selectedStudentId == null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.payment_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Please select a student',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  )
                : SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Card(
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Record Payment',
                              style: GoogleFonts.montserrat(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 24),
                            _buildPaymentForm(),
                          ],
                        ),
                      ),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentForm() {
    final formKey = GlobalKey<FormState>();
    final amountController = TextEditingController();
    final transactionIdController = TextEditingController();
    final remarksController = TextEditingController();
    String selectedPaymentMethod = 'Cash';
    DateTime paymentDate = DateTime.now();

    return Form(
      key: formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextFormField(
            controller: amountController,
            decoration: InputDecoration(
              labelText: 'Payment Amount *',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              prefixText: '\$ ',
            ),
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            validator: (value) =>
                value?.isEmpty ?? true ? 'Please enter amount' : null,
          ),
          const SizedBox(height: 16),
          DropdownButtonFormField<String>(
            decoration: InputDecoration(
              labelText: 'Payment Method *',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
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
              DropdownMenuItem(value: 'Debit Card', child: Text('Debit Card')),
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
                firstDate: DateTime.now().subtract(const Duration(days: 365)),
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
                  borderRadius: BorderRadius.circular(12),
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
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: remarksController,
            decoration: InputDecoration(
              labelText: 'Remarks (Optional)',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            maxLines: 3,
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () async {
                if (formKey.currentState!.validate()) {
                  final provider = context.read<FeesProvider>();
                  final success = await provider.recordFlexiblePayment(
                    studentId: _selectedStudentId!,
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
                    if (success) {
                      SweetAlert.showSuccess(
                        context: context,
                        title: 'Success',
                        message: 'Payment recorded successfully!',
                      );
                      // Clear form
                      amountController.clear();
                      transactionIdController.clear();
                      remarksController.clear();
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
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: Text(
                'Record Payment',
                style: GoogleFonts.montserrat(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
