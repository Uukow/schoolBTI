import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/reports_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class FinancialReportsPage extends StatefulWidget {
  const FinancialReportsPage({super.key});

  @override
  State<FinancialReportsPage> createState() => _FinancialReportsPageState();
}

class _FinancialReportsPageState extends State<FinancialReportsPage> {
  String? _selectedReportType = 'Fee Collection';
  DateTime? _startDate;
  DateTime? _endDate;
  bool _isLoading = false;

  Future<void> _selectStartDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _startDate ?? DateTime.now(),
      firstDate: DateTime.now().subtract(const Duration(days: 365 * 2)),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        _startDate = picked;
      });
    }
  }

  Future<void> _selectEndDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _endDate ?? DateTime.now(),
      firstDate:
          _startDate ?? DateTime.now().subtract(const Duration(days: 365 * 2)),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        _endDate = picked;
      });
    }
  }

  Future<void> _generateReport() async {
    if (_startDate == null || _endDate == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select start and end dates',
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<ReportsProvider>();

      await provider.loadFinancialReport(
        reportType: _selectedReportType!,
        startDate: _startDate,
        endDate: _endDate,
        userId: user?.id,
      );

      if (mounted) {
        if (provider.error != null) {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to generate report',
          );
        } else {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Report generated successfully',
          );
        }
      }
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to generate report: ${e.toString()}',
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
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
          'Financial Reports',
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
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                DropdownButtonFormField<String>(
                  decoration: InputDecoration(
                    labelText: 'Report Type',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  initialValue: _selectedReportType,
                  items: const [
                    DropdownMenuItem<String>(
                      value: 'Fee Collection',
                      child: Text('Fee Collection'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Income',
                      child: Text('Income'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Expenses',
                      child: Text('Expenses'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Balance Sheet',
                      child: Text('Balance Sheet'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Profit & Loss',
                      child: Text('Profit & Loss'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedReportType = value;
                    });
                  },
                ),
                const SizedBox(height: 12),
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
                const SizedBox(height: 12),
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
                ElevatedButton.icon(
                  onPressed: _isLoading ? null : _generateReport,
                  icon: _isLoading
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.description),
                  label: const Text('Generate Report'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ],
            ),
          ),
          // Report Results
          Expanded(
            child: Consumer<ReportsProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.error_outline,
                            size: 64,
                            color: Colors.red,
                          ),
                          const SizedBox(height: 16),
                          Text(
                            provider.error ?? 'Error loading report',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.montserrat(),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                if (provider.financialReport == null) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.description_outlined,
                            size: 64,
                            color: Colors.grey[400],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'No report generated yet',
                            style: GoogleFonts.montserrat(
                              fontSize: 16,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                final report = provider.financialReport!;
                return SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                report.reportType,
                                style: GoogleFonts.montserrat(
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceAround,
                                children: [
                                  _buildStatCard(
                                    'Total Income',
                                    report.totalIncome,
                                    Colors.green,
                                  ),
                                  _buildStatCard(
                                    'Total Expenses',
                                    report.totalExpenses,
                                    Colors.red,
                                  ),
                                  _buildStatCard(
                                    'Balance',
                                    report.balance,
                                    Colors.blue,
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                      if (report.transactions.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Text(
                          'Transactions',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        ...report.transactions.map((transaction) {
                          return Card(
                            margin: const EdgeInsets.only(bottom: 8),
                            child: ListTile(
                              title: Text(transaction['description'] ?? ''),
                              subtitle: Text(transaction['date'] ?? ''),
                              trailing: Text(
                                '${transaction['amount'] ?? 0}',
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.bold,
                                  color: (transaction['type'] == 'income'
                                      ? Colors.green
                                      : Colors.red),
                                ),
                              ),
                            ),
                          );
                        }),
                      ],
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String label, double value, Color color) {
    return Column(
      children: [
        Text(
          label,
          style: GoogleFonts.montserrat(fontSize: 12, color: Colors.grey[600]),
        ),
        const SizedBox(height: 4),
        Text(
          NumberFormat.currency(symbol: '\$').format(value),
          style: GoogleFonts.montserrat(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }
}
