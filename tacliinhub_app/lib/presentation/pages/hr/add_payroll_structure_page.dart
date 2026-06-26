import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/hr_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AddPayrollStructurePage extends StatefulWidget {
  const AddPayrollStructurePage({super.key});

  @override
  State<AddPayrollStructurePage> createState() =>
      _AddPayrollStructurePageState();
}

class _AddPayrollStructurePageState extends State<AddPayrollStructurePage> {
  final _formKey = GlobalKey<FormState>();
  final _basicSalaryController = TextEditingController();
  final _houseAllowanceController = TextEditingController();
  final _transportAllowanceController = TextEditingController();
  final _medicalAllowanceController = TextEditingController();
  final _otherAllowancesController = TextEditingController();
  final _taxDeductionController = TextEditingController();
  final _otherDeductionsController = TextEditingController();

  int? _selectedStaffId;
  DateTime _effectiveFrom = DateTime.now();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      context.read<HrProvider>().loadStaff(userId: user?.id, status: 'Active');
    });
  }

  @override
  void dispose() {
    _basicSalaryController.dispose();
    _houseAllowanceController.dispose();
    _transportAllowanceController.dispose();
    _medicalAllowanceController.dispose();
    _otherAllowancesController.dispose();
    _taxDeductionController.dispose();
    _otherDeductionsController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Add Payroll Structure',
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
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Payroll Structure',
                        style: GoogleFonts.montserrat(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 20),
                      Consumer<HrProvider>(
                        builder: (context, provider, child) {
                          if (provider.isLoading && provider.staff.isEmpty) {
                            return DropdownButtonFormField<int>(
                              decoration: InputDecoration(
                                labelText: 'Select Staff *',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.person),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 16,
                                ),
                              ),
                              isExpanded: true,
                              initialValue: null,
                              items: const [
                                DropdownMenuItem<int>(
                                  value: null,
                                  enabled: false,
                                  child: Text(
                                    'Loading...',
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                              onChanged: null,
                            );
                          }

                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select Staff *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.person),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 16,
                              ),
                            ),
                            isExpanded: true,
                            initialValue: _selectedStaffId,
                            items: provider.staff.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text(
                                        'No staff available',
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ]
                                : provider.staff.map((staff) {
                                    return DropdownMenuItem<int>(
                                      value: staff.id,
                                      child: Text(
                                        '${staff.fullName} (${staff.designation})',
                                        overflow: TextOverflow.ellipsis,
                                        maxLines: 1,
                                      ),
                                    );
                                  }).toList(),
                            selectedItemBuilder: (context) {
                              if (_selectedStaffId == null) {
                                return [const Text('Select Staff')];
                              }
                              final selectedStaff = provider.staff.firstWhere(
                                (staff) => staff.id == _selectedStaffId,
                                orElse: () => provider.staff.first,
                              );
                              return [
                                Text(
                                  '${selectedStaff.fullName} (${selectedStaff.designation})',
                                  overflow: TextOverflow.ellipsis,
                                  maxLines: 1,
                                ),
                              ];
                            },
                            onChanged: provider.staff.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedStaffId = value;
                                    });
                                  },
                            validator: (value) =>
                                value == null ? 'Please select staff' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      InkWell(
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: _effectiveFrom,
                            firstDate: DateTime(2000),
                            lastDate: DateTime.now().add(
                              const Duration(days: 365),
                            ),
                          );
                          if (picked != null) {
                            setState(() {
                              _effectiveFrom = picked;
                            });
                          }
                        },
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'Effective From *',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.calendar_today),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 16,
                            ),
                          ),
                          child: Text(
                            DateFormat('yyyy-MM-dd').format(_effectiveFrom),
                            style: const TextStyle(fontSize: 16),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),
                      Text(
                        'Salary Components',
                        style: GoogleFonts.montserrat(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextFormField(
                        controller: _basicSalaryController,
                        decoration: InputDecoration(
                          labelText: 'Basic Salary *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixText: '\$ ',
                          prefixIcon: const Icon(Icons.attach_money),
                        ),
                        keyboardType: const TextInputType.numberWithOptions(
                          decimal: true,
                        ),
                        validator: (value) {
                          if (value?.isEmpty ?? true) {
                            return 'Required';
                          }
                          final salary = double.tryParse(value!);
                          if (salary == null || salary <= 0) {
                            return 'Invalid';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _houseAllowanceController,
                              decoration: InputDecoration(
                                labelText: 'House Allowance',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 16,
                                ),
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: TextFormField(
                              controller: _transportAllowanceController,
                              decoration: InputDecoration(
                                labelText: 'Transport Allowance',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 16,
                                ),
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _medicalAllowanceController,
                              decoration: InputDecoration(
                                labelText: 'Medical Allowance',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 16,
                                ),
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: TextFormField(
                              controller: _otherAllowancesController,
                              decoration: InputDecoration(
                                labelText: 'Other Allowances',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 16,
                                ),
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
                      Text(
                        'Deductions',
                        style: GoogleFonts.montserrat(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _taxDeductionController,
                              decoration: InputDecoration(
                                labelText: 'Tax Deduction',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 16,
                                ),
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: TextFormField(
                              controller: _otherDeductionsController,
                              decoration: InputDecoration(
                                labelText: 'Other Deductions',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 16,
                                ),
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),
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
                            'Add Payroll Structure',
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

  Future<void> _submitForm() async {
    if (_formKey.currentState!.validate()) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<HrProvider>();

      final success = await provider.addPayrollStructure(
        staffId: _selectedStaffId!,
        basicSalary: double.parse(_basicSalaryController.text),
        houseAllowance: _houseAllowanceController.text.isEmpty
            ? 0
            : double.tryParse(_houseAllowanceController.text) ?? 0,
        transportAllowance: _transportAllowanceController.text.isEmpty
            ? 0
            : double.tryParse(_transportAllowanceController.text) ?? 0,
        medicalAllowance: _medicalAllowanceController.text.isEmpty
            ? 0
            : double.tryParse(_medicalAllowanceController.text) ?? 0,
        otherAllowances: _otherAllowancesController.text.isEmpty
            ? 0
            : double.tryParse(_otherAllowancesController.text) ?? 0,
        taxDeduction: _taxDeductionController.text.isEmpty
            ? 0
            : double.tryParse(_taxDeductionController.text) ?? 0,
        otherDeductions: _otherDeductionsController.text.isEmpty
            ? 0
            : double.tryParse(_otherDeductionsController.text) ?? 0,
        effectiveFrom: DateFormat('yyyy-MM-dd').format(_effectiveFrom),
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Payroll structure added successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to add payroll structure',
          );
        }
      }
    }
  }
}
