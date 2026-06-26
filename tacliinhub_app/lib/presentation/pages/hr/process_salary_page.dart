import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/hr_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';
import '../../../data/models/hr_models.dart';

class ProcessSalaryPage extends StatefulWidget {
  const ProcessSalaryPage({super.key});

  @override
  State<ProcessSalaryPage> createState() => _ProcessSalaryPageState();
}

class _ProcessSalaryPageState extends State<ProcessSalaryPage> {
  final _formKey = GlobalKey<FormState>();
  final _allowancesController = TextEditingController();
  final _deductionsController = TextEditingController();
  final _remarksController = TextEditingController();

  int? _selectedStaffId;
  DateTime _paymentMonth = DateTime.now();
  DateTime? _paymentDate;
  String _selectedPaymentMethod = 'Bank Transfer';
  PayrollStructure? _selectedStructure;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<HrProvider>();
      provider.loadStaff(userId: user?.id, status: 'Active');
      provider.loadPayrollStructures(userId: user?.id);
    });
  }

  @override
  void dispose() {
    _allowancesController.dispose();
    _deductionsController.dispose();
    _remarksController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Process Salary Payment',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Card(
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
                        'Payment Information',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Consumer<HrProvider>(
                        builder: (context, provider, child) {
                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select Staff *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.person),
                            ),
                            initialValue: _selectedStaffId,
                            items: provider.staff.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No staff available'),
                                    ),
                                  ]
                                : provider.staff.map((staff) {
                                    return DropdownMenuItem<int>(
                                      value: staff.id,
                                      child: Text(
                                        '${staff.fullName} (${staff.designation})',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: provider.staff.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedStaffId = value;
                                      _selectedStructure = provider
                                          .payrollStructures
                                          .where((s) => s.staffId == value)
                                          .firstOrNull;
                                    });
                                  },
                            validator: (value) =>
                                value == null ? 'Please select staff' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: InkWell(
                              onTap: () async {
                                final picked = await showDatePicker(
                                  context: context,
                                  initialDate: _paymentMonth,
                                  firstDate: DateTime(2000),
                                  lastDate: DateTime.now(),
                                );
                                if (picked != null) {
                                  setState(() {
                                    _paymentMonth = picked;
                                  });
                                }
                              },
                              child: InputDecorator(
                                decoration: InputDecoration(
                                  labelText: 'Payment Month *',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  prefixIcon: const Icon(Icons.calendar_today),
                                ),
                                child: Text(
                                  DateFormat('yyyy-MM').format(_paymentMonth),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: InkWell(
                              onTap: () async {
                                final picked = await showDatePicker(
                                  context: context,
                                  initialDate: _paymentDate ?? DateTime.now(),
                                  firstDate: DateTime.now(),
                                  lastDate: DateTime.now().add(
                                    const Duration(days: 30),
                                  ),
                                );
                                if (picked != null) {
                                  setState(() {
                                    _paymentDate = picked;
                                  });
                                }
                              },
                              child: InputDecorator(
                                decoration: InputDecoration(
                                  labelText: 'Payment Date',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  prefixIcon: const Icon(Icons.event),
                                ),
                                child: Text(
                                  _paymentDate == null
                                      ? 'Select date'
                                      : DateFormat(
                                          'yyyy-MM-dd',
                                        ).format(_paymentDate!),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: 'Payment Method',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.payment),
                        ),
                        initialValue: _selectedPaymentMethod,
                        items: const [
                          DropdownMenuItem(
                            value: 'Bank Transfer',
                            child: Text('Bank Transfer'),
                          ),
                          DropdownMenuItem(value: 'Cash', child: Text('Cash')),
                          DropdownMenuItem(
                            value: 'Cheque',
                            child: Text('Cheque'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedPaymentMethod = value!;
                          });
                        },
                      ),
                      if (_selectedStructure != null) ...[
                        const SizedBox(height: 24),
                        Text(
                          'Salary Breakdown',
                          style: GoogleFonts.montserrat(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Card(
                          color: Colors.grey[100],
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              children: [
                                _buildSalaryRow(
                                  'Basic Salary',
                                  _selectedStructure!.basicSalary,
                                ),
                                _buildSalaryRow(
                                  'Total Allowances',
                                  _selectedStructure!.totalAllowances,
                                ),
                                _buildSalaryRow(
                                  'Gross Salary',
                                  _selectedStructure!.grossSalary,
                                  isBold: true,
                                ),
                                const Divider(),
                                _buildSalaryRow(
                                  'Total Deductions',
                                  _selectedStructure!.totalDeductions,
                                ),
                                _buildSalaryRow(
                                  'Net Salary',
                                  _selectedStructure!.netSalary,
                                  isBold: true,
                                  color: Colors.green,
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _allowancesController,
                              decoration: InputDecoration(
                                labelText: 'Additional Allowances',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _deductionsController,
                              decoration: InputDecoration(
                                labelText: 'Additional Deductions',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _remarksController,
                        decoration: InputDecoration(
                          labelText: 'Remarks',
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
                          onPressed: _submitForm,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.green,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Process Payment',
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
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSalaryRow(
    String label,
    double amount, {
    bool isBold = false,
    Color? color,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: GoogleFonts.montserrat(
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            ),
          ),
          Text(
            '\$${amount.toStringAsFixed(2)}',
            style: GoogleFonts.montserrat(
              fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _submitForm() async {
    if (_formKey.currentState!.validate() && _selectedStructure != null) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<HrProvider>();

      final additionalAllowances = _allowancesController.text.isEmpty
          ? 0.0
          : (double.tryParse(_allowancesController.text) ?? 0.0);
      final additionalDeductions = _deductionsController.text.isEmpty
          ? 0.0
          : (double.tryParse(_deductionsController.text) ?? 0.0);

      final totalAllowances =
          _selectedStructure!.totalAllowances + additionalAllowances;
      final totalDeductions =
          _selectedStructure!.totalDeductions + additionalDeductions;
      final netSalary =
          _selectedStructure!.basicSalary + totalAllowances - totalDeductions;

      final success = await provider.processSalaryPayment(
        staffId: _selectedStaffId!,
        paymentMonth: DateFormat('yyyy-MM-01').format(_paymentMonth),
        basicSalary: _selectedStructure!.basicSalary,
        allowances: totalAllowances,
        deductions: totalDeductions,
        netSalary: netSalary,
        paymentDate: _paymentDate != null
            ? DateFormat('yyyy-MM-dd').format(_paymentDate!)
            : null,
        paymentMethod: _selectedPaymentMethod,
        remarks: _remarksController.text.isEmpty
            ? null
            : _remarksController.text,
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Salary payment processed successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to process salary payment',
          );
        }
      }
    }
  }
}
