import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/hr_provider.dart';
import '../../providers/auth_provider.dart';

class PayrollPage extends StatefulWidget {
  const PayrollPage({super.key});

  @override
  State<PayrollPage> createState() => _PayrollPageState();
}

class _PayrollPageState extends State<PayrollPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _tabController.addListener(() {
      setState(() {}); // Rebuild when tab changes to update FAB
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<HrProvider>();
      provider.loadPayrollStructures(userId: user?.id);
      provider.loadSalaryPayments(userId: user?.id);
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Payroll Management',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(text: 'Payroll Structures'),
            Tab(text: 'Salary Payments'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [_buildStructuresTab(), _buildPaymentsTab()],
      ),
      floatingActionButton: _tabController.index == 0
          ? FloatingActionButton(
              onPressed: () =>
                  Navigator.pushNamed(context, '/hr/payroll/add-structure'),
              backgroundColor: Colors.green,
              child: const Icon(Icons.add),
            )
          : FloatingActionButton(
              onPressed: () =>
                  Navigator.pushNamed(context, '/hr/payroll/process-salary'),
              backgroundColor: Colors.green,
              child: const Icon(Icons.payment),
            ),
    );
  }

  Widget _buildStructuresTab() {
    return Consumer<HrProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const Center(child: CircularProgressIndicator());
        }

        if (provider.error != null) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, size: 48, color: Colors.red),
                const SizedBox(height: 16),
                Text(provider.error ?? 'Error loading structures'),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () {
                    final user = Provider.of<AuthProvider>(
                      context,
                      listen: false,
                    ).user;
                    provider.loadPayrollStructures(userId: user?.id);
                  },
                  child: const Text('Retry'),
                ),
              ],
            ),
          );
        }

        if (provider.payrollStructures.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.account_balance_wallet_outlined,
                  size: 64,
                  color: Colors.grey[400],
                ),
                const SizedBox(height: 16),
                Text(
                  'No payroll structures found',
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
          itemCount: provider.payrollStructures.length,
          itemBuilder: (context, index) {
            final structure = provider.payrollStructures[index];
            return Card(
              margin: const EdgeInsets.only(bottom: 12),
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: ExpansionTile(
                leading: CircleAvatar(
                  backgroundColor: Colors.green.withOpacity(0.1),
                  child: const Icon(
                    Icons.account_balance_wallet,
                    color: Colors.green,
                  ),
                ),
                title: Text(
                  structure.staffName,
                  style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                ),
                subtitle: Text(
                  'Effective: ${DateFormat('MMM d, yyyy').format(DateTime.parse(structure.effectiveFrom))}',
                ),
                children: [
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildSalaryRow('Basic Salary', structure.basicSalary),
                        _buildSalaryRow(
                          'House Allowance',
                          structure.houseAllowance,
                        ),
                        _buildSalaryRow(
                          'Transport Allowance',
                          structure.transportAllowance,
                        ),
                        _buildSalaryRow(
                          'Medical Allowance',
                          structure.medicalAllowance,
                        ),
                        _buildSalaryRow(
                          'Other Allowances',
                          structure.otherAllowances,
                        ),
                        const Divider(),
                        _buildSalaryRow(
                          'Gross Salary',
                          structure.grossSalary,
                          isBold: true,
                        ),
                        _buildSalaryRow(
                          'Tax Deduction',
                          structure.taxDeduction,
                        ),
                        _buildSalaryRow(
                          'Other Deductions',
                          structure.otherDeductions,
                        ),
                        const Divider(),
                        _buildSalaryRow(
                          'Net Salary',
                          structure.netSalary,
                          isBold: true,
                          color: Colors.green,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildPaymentsTab() {
    return Consumer<HrProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const Center(child: CircularProgressIndicator());
        }

        if (provider.error != null) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, size: 48, color: Colors.red),
                const SizedBox(height: 16),
                Text(provider.error ?? 'Error loading payments'),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () {
                    final user = Provider.of<AuthProvider>(
                      context,
                      listen: false,
                    ).user;
                    provider.loadSalaryPayments(userId: user?.id);
                  },
                  child: const Text('Retry'),
                ),
              ],
            ),
          );
        }

        if (provider.salaryPayments.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.payment_outlined, size: 64, color: Colors.grey[400]),
                const SizedBox(height: 16),
                Text(
                  'No salary payments found',
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
          itemCount: provider.salaryPayments.length,
          itemBuilder: (context, index) {
            final payment = provider.salaryPayments[index];
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
                  payment.staffName,
                  style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                ),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Month: ${DateFormat('MMM yyyy').format(DateTime.parse(payment.paymentMonth))}',
                    ),
                    if (payment.paymentDate != null)
                      Text(
                        'Paid: ${DateFormat('MMM d, yyyy').format(DateTime.parse(payment.paymentDate!))}',
                      ),
                    if (payment.paymentMethod != null)
                      Text('Method: ${payment.paymentMethod}'),
                  ],
                ),
                trailing: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      '\$${payment.netSalary.toStringAsFixed(2)}',
                      style: GoogleFonts.montserrat(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
                    ),
                    Text(
                      'Net Salary',
                      style: GoogleFonts.montserrat(fontSize: 10),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
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
}
