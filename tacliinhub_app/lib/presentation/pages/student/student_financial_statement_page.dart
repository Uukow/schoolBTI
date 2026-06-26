import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../../../core/constants.dart';
import '../../providers/student_portal_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';
import '../../../data/models/student_models.dart';

class StudentFinancialStatementPage extends StatefulWidget {
  const StudentFinancialStatementPage({super.key});

  @override
  State<StudentFinancialStatementPage> createState() =>
      _StudentFinancialStatementPageState();
}

class _StudentFinancialStatementPageState
    extends State<StudentFinancialStatementPage> {
  int? _selectedSessionId;
  DateTime? _dateFrom;
  DateTime? _dateTo;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadFinancialStatement();
    });
  }

  void _loadFinancialStatement() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user != null) {
      Provider.of<StudentPortalProvider>(
        context,
        listen: false,
      ).loadFinancialStatement(
        userId: user.id,
        sessionId: _selectedSessionId,
        dateFrom: _dateFrom,
        dateTo: _dateTo,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppConstants.scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text(
          'Financial Statement',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadFinancialStatement,
            tooltip: 'Refresh',
          ),
        ],
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer<StudentPortalProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.financialStatement == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(
                      AppConstants.primaryColor,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Loading financial data...',
                    style: GoogleFonts.montserrat(
                      color: Colors.grey[600],
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            );
          }

          if (provider.error != null && provider.financialStatement == null) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.red.withOpacity(0.1),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.error_outline,
                        size: 64,
                        color: Colors.red,
                      ),
                    ),
                    const SizedBox(height: 24),
                    Text(
                      'Error Loading Financial Statement',
                      style: GoogleFonts.montserrat(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.grey[900],
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      provider.error ?? 'An unexpected error occurred',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.montserrat(
                        fontSize: 14,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton.icon(
                      onPressed: _loadFinancialStatement,
                      icon: const Icon(Icons.refresh_rounded),
                      label: const Text('Retry'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppConstants.primaryColor,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 24,
                          vertical: 12,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          }

          final financialData = provider.financialStatement;
          if (financialData == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.receipt_long_outlined,
                    size: 64,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No financial data available',
                    style: GoogleFonts.montserrat(
                      fontSize: 18,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            );
          }

          final sessions =
              (financialData['sessions'] as List<dynamic>?)
                  ?.map((s) => AcademicSession.fromJson(s))
                  .toList() ??
              [];
          final outstandingFees =
              (financialData['outstanding_fees'] as List<dynamic>?)
                  ?.map((f) => OutstandingFee.fromJson(f))
                  .toList() ??
              [];
          final summary = financialData['financial_summary'] != null
              ? FinancialSummary.fromJson(financialData['financial_summary'])
              : null;
          final statementEntries =
              (financialData['financial_statement'] as List<dynamic>?)
                  ?.map((e) => FinancialStatementEntry.fromJson(e))
                  .toList() ??
              [];

          return RefreshIndicator(
            onRefresh: () async => _loadFinancialStatement(),
            color: AppConstants.primaryColor,
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header Section
                  _buildHeader(),
                  const SizedBox(height: 24),

                  // Filters Section
                  _buildFiltersSection(sessions),
                  const SizedBox(height: 24),

                  // Financial Summary Cards
                  if (summary != null) ...[
                    _buildFinancialSummarySection(summary),
                    const SizedBox(height: 24),
                  ],

                  // Outstanding Fees Section
                  if (outstandingFees.isNotEmpty) ...[
                    _buildOutstandingFeesSection(outstandingFees),
                    const SizedBox(height: 24),
                  ],

                  // Financial Statement Section
                  _buildFinancialStatementSection(statementEntries),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppConstants.primaryColor,
            AppConstants.primaryColor.withOpacity(0.8),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: AppConstants.primaryColor.withOpacity(0.3),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(
              Icons.account_balance_wallet_rounded,
              color: Colors.white,
              size: 32,
            ),
          ),
          const SizedBox(width: 20),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Financial Statement',
                  style: GoogleFonts.montserrat(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'View your complete financial transactions',
                  style: GoogleFonts.montserrat(
                    fontSize: 14,
                    color: Colors.white.withOpacity(0.9),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFiltersSection(List<AcademicSession> sessions) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 20,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppConstants.primaryColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  Icons.filter_list_rounded,
                  color: AppConstants.primaryColor,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Text(
                'Filter Options',
                style: GoogleFonts.montserrat(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey[900],
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          // Academic Session Dropdown
          _buildFilterDropdown(
            label: 'Academic Session',
            icon: Icons.calendar_today_rounded,
            child: DropdownButtonFormField<int?>(
              initialValue: _selectedSessionId,
              decoration: InputDecoration(
                filled: true,
                fillColor: Colors.grey[50],
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(color: Colors.grey[300]!),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: AppConstants.primaryColor,
                    width: 2,
                  ),
                ),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 16,
                ),
              ),
              style: GoogleFonts.montserrat(fontSize: 14),
              items: [
                DropdownMenuItem<int?>(
                  value: null,
                  child: Text('All Sessions', style: GoogleFonts.montserrat()),
                ),
                ...sessions.map((session) {
                  return DropdownMenuItem<int?>(
                    value: session.id,
                    child: Text(
                      session.sessionName,
                      style: GoogleFonts.montserrat(),
                    ),
                  );
                }),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedSessionId = value;
                });
                _loadFinancialStatement();
              },
            ),
          ),
          const SizedBox(height: 16),
          // Date Range Row
          Row(
            children: [
              Expanded(
                child: _buildDatePicker(
                  label: 'Date From',
                  date: _dateFrom,
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: _dateFrom ?? DateTime.now(),
                      firstDate: DateTime(2020),
                      lastDate: DateTime.now(),
                      builder: (context, child) {
                        return Theme(
                          data: Theme.of(context).copyWith(
                            colorScheme: ColorScheme.light(
                              primary: AppConstants.primaryColor,
                            ),
                          ),
                          child: child!,
                        );
                      },
                    );
                    if (date != null) {
                      setState(() {
                        _dateFrom = date;
                      });
                      _loadFinancialStatement();
                    }
                  },
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildDatePicker(
                  label: 'Date To',
                  date: _dateTo,
                  onTap: () async {
                    final date = await showDatePicker(
                      context: context,
                      initialDate: _dateTo ?? DateTime.now(),
                      firstDate: _dateFrom ?? DateTime(2020),
                      lastDate: DateTime.now(),
                      builder: (context, child) {
                        return Theme(
                          data: Theme.of(context).copyWith(
                            colorScheme: ColorScheme.light(
                              primary: AppConstants.primaryColor,
                            ),
                          ),
                          child: child!,
                        );
                      },
                    );
                    if (date != null) {
                      setState(() {
                        _dateTo = date;
                      });
                      _loadFinancialStatement();
                    }
                  },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildFilterDropdown({
    required String label,
    required IconData icon,
    required Widget child,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 16, color: Colors.grey[600]),
            const SizedBox(width: 8),
            Text(
              label,
              style: GoogleFonts.montserrat(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Colors.grey[700],
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        child,
      ],
    );
  }

  Widget _buildDatePicker({
    required String label,
    required DateTime? date,
    required VoidCallback onTap,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(
              Icons.calendar_today_rounded,
              size: 16,
              color: Colors.grey[600],
            ),
            const SizedBox(width: 8),
            Text(
              label,
              style: GoogleFonts.montserrat(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Colors.grey[700],
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(12),
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey[50],
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey[300]!),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.calendar_today_rounded,
                  size: 20,
                  color: AppConstants.primaryColor,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    date != null
                        ? DateFormat('MM/dd/yyyy').format(date)
                        : 'mm/dd/yyyy',
                    style: GoogleFonts.montserrat(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: date != null ? Colors.grey[900] : Colors.grey[400],
                    ),
                  ),
                ),
                Icon(Icons.arrow_drop_down, color: Colors.grey[400]),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFinancialSummarySection(FinancialSummary summary) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppConstants.primaryColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                Icons.analytics_rounded,
                color: AppConstants.primaryColor,
                size: 20,
              ),
            ),
            const SizedBox(width: 12),
            Text(
              'Financial Summary',
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[900],
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: _buildSummaryCard(
                'Opening Balance',
                '\$${summary.openingBalance.toStringAsFixed(2)}',
                Icons.account_balance_wallet_outlined,
                Colors.blue,
                Colors.blue.withOpacity(0.1),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildSummaryCard(
                'Total Charges',
                '\$${summary.totalCharges.toStringAsFixed(2)}',
                Icons.add_circle_outline_rounded,
                Colors.red,
                Colors.red.withOpacity(0.1),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildSummaryCard(
                'Total Receipts',
                '\$${summary.totalReceipts.toStringAsFixed(2)}',
                Icons.remove_circle_outline_rounded,
                Colors.green,
                Colors.green.withOpacity(0.1),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildSummaryCard(
                'Closing Balance',
                '\$${summary.closingBalance.toStringAsFixed(2)}',
                Icons.account_balance_rounded,
                AppConstants.primaryColor,
                AppConstants.primaryColor.withOpacity(0.1),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildSummaryCard(
    String label,
    String value,
    IconData icon,
    Color iconColor,
    Color bgColor,
  ) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey[200]!),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 24),
          ),
          const SizedBox(height: 16),
          Text(
            value,
            style: GoogleFonts.montserrat(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.grey[900],
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: GoogleFonts.montserrat(
              fontSize: 13,
              color: Colors.grey[600],
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOutstandingFeesSection(List<OutstandingFee> fees) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.red.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                Icons.warning_amber_rounded,
                color: Colors.red,
                size: 20,
              ),
            ),
            const SizedBox(width: 12),
            Text(
              'Outstanding Fees',
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[900],
              ),
            ),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.red.withOpacity(0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '${fees.length}',
                style: GoogleFonts.montserrat(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: Colors.red,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 20,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(20),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  DataTable(
                    headingRowHeight: 56,
                    dataRowMinHeight: 60,
                    dataRowMaxHeight: 80,
                    headingRowColor: WidgetStateProperty.all(
                      AppConstants.primaryColor.withOpacity(0.08),
                    ),
                    columns: [
                      _buildDataColumn(
                        'Invoice No',
                        Icons.receipt_long_rounded,
                      ),
                      _buildDataColumn('Fee Types', Icons.category_rounded),
                      _buildDataColumn(
                        'Total Amount',
                        Icons.attach_money_rounded,
                      ),
                      _buildDataColumn(
                        'Paid Amount',
                        Icons.check_circle_rounded,
                      ),
                      _buildDataColumn(
                        'Due Amount',
                        Icons.error_outline_rounded,
                      ),
                      _buildDataColumn(
                        'Due Date',
                        Icons.calendar_today_rounded,
                      ),
                      _buildDataColumn('Status', Icons.info_rounded),
                    ],
                    rows: fees.map<DataRow>((fee) {
                      return DataRow(
                        cells: [
                          DataCell(
                            Text(
                              fee.invoiceNo,
                              style: GoogleFonts.montserrat(
                                fontWeight: FontWeight.w600,
                                fontSize: 13,
                              ),
                            ),
                          ),
                          DataCell(
                            SizedBox(
                              width: 150,
                              child: Text(
                                fee.feeTypes,
                                style: GoogleFonts.montserrat(fontSize: 13),
                                overflow: TextOverflow.ellipsis,
                                maxLines: 2,
                              ),
                            ),
                          ),
                          DataCell(
                            Text(
                              '\$${fee.totalAmount.toStringAsFixed(2)}',
                              style: GoogleFonts.montserrat(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                          DataCell(
                            Text(
                              '\$${fee.paidAmount.toStringAsFixed(2)}',
                              style: GoogleFonts.montserrat(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: Colors.green[700],
                              ),
                            ),
                          ),
                          DataCell(
                            Text(
                              '\$${fee.dueAmount.toStringAsFixed(2)}',
                              style: GoogleFonts.montserrat(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: Colors.red[700],
                              ),
                            ),
                          ),
                          DataCell(
                            Text(
                              DateFormat('MM/dd/yyyy').format(fee.dueDate),
                              style: GoogleFonts.montserrat(fontSize: 13),
                            ),
                          ),
                          DataCell(
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 6,
                              ),
                              decoration: BoxDecoration(
                                color: _getStatusColor(
                                  fee.status,
                                ).withOpacity(0.15),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: _getStatusColor(
                                    fee.status,
                                  ).withOpacity(0.3),
                                  width: 1,
                                ),
                              ),
                              child: Text(
                                fee.status,
                                style: GoogleFonts.montserrat(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: _getStatusColor(fee.status),
                                ),
                              ),
                            ),
                          ),
                        ],
                      );
                    }).toList(),
                  ),
                  // Totals Row for Outstanding Fees
                  _buildOutstandingFeesTotalsRowDataTable(fees),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFinancialStatementSection(
    List<FinancialStatementEntry> entries,
  ) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppConstants.primaryColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                Icons.receipt_long_rounded,
                color: AppConstants.primaryColor,
                size: 20,
              ),
            ),
            const SizedBox(width: 12),
            Text(
              'Financial Statement',
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[900],
              ),
            ),
            const Spacer(),
            if (entries.isNotEmpty)
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: AppConstants.primaryColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${entries.length} transactions',
                  style: GoogleFonts.montserrat(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppConstants.primaryColor,
                  ),
                ),
              ),
          ],
        ),
        const SizedBox(height: 16),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 20,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: entries.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(48),
                  child: Column(
                    children: [
                      Icon(
                        Icons.receipt_long_outlined,
                        size: 64,
                        color: Colors.grey[300],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'No financial statement entries found',
                        style: GoogleFonts.montserrat(
                          fontSize: 16,
                          color: Colors.grey[600],
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Transactions will appear here once available',
                        style: GoogleFonts.montserrat(
                          fontSize: 13,
                          color: Colors.grey[500],
                        ),
                      ),
                    ],
                  ),
                )
              : ClipRRect(
                  borderRadius: BorderRadius.circular(20),
                  child: LayoutBuilder(
                    builder: (context, constraints) {
                      return SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: ConstrainedBox(
                          constraints: BoxConstraints(
                            minWidth: constraints.maxWidth,
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              DataTable(
                                headingRowHeight: 56,
                                dataRowMinHeight: 60,
                                dataRowMaxHeight: 80,
                                headingRowColor: WidgetStateProperty.all(
                                  AppConstants.primaryColor.withOpacity(0.08),
                                ),
                                columns: [
                                  _buildDataColumn(
                                    'Date',
                                    Icons.calendar_today_rounded,
                                  ),
                                  _buildDataColumn(
                                    'Type',
                                    Icons.category_rounded,
                                  ),
                                  _buildDataColumn(
                                    'Description',
                                    Icons.description_rounded,
                                  ),
                                  _buildDataColumn(
                                    'Charge',
                                    Icons.arrow_upward_rounded,
                                  ),
                                  _buildDataColumn(
                                    'Receipt',
                                    Icons.arrow_downward_rounded,
                                  ),
                                  _buildDataColumn(
                                    'Balance',
                                    Icons.account_balance_rounded,
                                  ),
                                ],
                                rows: entries.map<DataRow>((entry) {
                                  final isCharge =
                                      entry.transactionType == 'Charge';
                                  return DataRow(
                                    cells: [
                                      DataCell(
                                        Text(
                                          DateFormat(
                                            'MM/dd/yyyy',
                                          ).format(entry.transactionDate),
                                          style: GoogleFonts.montserrat(
                                            fontSize: 13,
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                      ),
                                      DataCell(
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: 10,
                                            vertical: 6,
                                          ),
                                          decoration: BoxDecoration(
                                            color: isCharge
                                                ? Colors.red.withOpacity(0.15)
                                                : Colors.green.withOpacity(
                                                    0.15,
                                                  ),
                                            borderRadius: BorderRadius.circular(
                                              20,
                                            ),
                                            border: Border.all(
                                              color: isCharge
                                                  ? Colors.red.withOpacity(0.3)
                                                  : Colors.green.withOpacity(
                                                      0.3,
                                                    ),
                                              width: 1,
                                            ),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Icon(
                                                isCharge
                                                    ? Icons.arrow_upward
                                                    : Icons.arrow_downward,
                                                size: 14,
                                                color: isCharge
                                                    ? Colors.red[700]
                                                    : Colors.green[700],
                                              ),
                                              const SizedBox(width: 4),
                                              Text(
                                                entry.transactionType,
                                                style: GoogleFonts.montserrat(
                                                  fontSize: 11,
                                                  fontWeight: FontWeight.bold,
                                                  color: isCharge
                                                      ? Colors.red[700]
                                                      : Colors.green[700],
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                      DataCell(
                                        SizedBox(
                                          width: 200,
                                          child: Text(
                                            entry.description,
                                            style: GoogleFonts.montserrat(
                                              fontSize: 13,
                                            ),
                                            overflow: TextOverflow.ellipsis,
                                            maxLines: 2,
                                          ),
                                        ),
                                      ),
                                      DataCell(
                                        Text(
                                          entry.charge > 0
                                              ? '\$${entry.charge.toStringAsFixed(2)}'
                                              : '-',
                                          style: GoogleFonts.montserrat(
                                            fontSize: 13,
                                            fontWeight: FontWeight.w600,
                                            color: entry.charge > 0
                                                ? Colors.red[700]
                                                : Colors.grey[400],
                                          ),
                                        ),
                                      ),
                                      DataCell(
                                        Text(
                                          entry.receipt > 0
                                              ? '\$${entry.receipt.toStringAsFixed(2)}'
                                              : '-',
                                          style: GoogleFonts.montserrat(
                                            fontSize: 13,
                                            fontWeight: FontWeight.w600,
                                            color: entry.receipt > 0
                                                ? Colors.green[700]
                                                : Colors.grey[400],
                                          ),
                                        ),
                                      ),
                                      DataCell(
                                        Text(
                                          entry.balance != null
                                              ? '\$${entry.balance!.toStringAsFixed(2)}'
                                              : '-',
                                          style: GoogleFonts.montserrat(
                                            fontSize: 13,
                                            fontWeight: FontWeight.bold,
                                            color: (entry.balance ?? 0) < 0
                                                ? Colors.red[700]
                                                : Colors.grey[900],
                                          ),
                                        ),
                                      ),
                                    ],
                                  );
                                }).toList(),
                              ),
                              // Totals Row - using DataTable with same structure for alignment
                              _buildTotalsRowDataTable(entries),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
        ),
      ],
    );
  }

  Widget _buildTotalsRowDataTable(List<FinancialStatementEntry> entries) {
    // Calculate totals
    double totalCharges = 0.0;
    double totalReceipts = 0.0;
    double? finalBalance;

    for (var entry in entries) {
      totalCharges += entry.charge;
      totalReceipts += entry.receipt;
      if (entry.balance != null) {
        finalBalance = entry.balance;
      }
    }

    return Container(
      decoration: BoxDecoration(
        color: AppConstants.primaryColor.withOpacity(0.05),
        border: Border(
          top: BorderSide(
            color: AppConstants.primaryColor.withOpacity(0.2),
            width: 2,
          ),
        ),
      ),
      child: DataTable(
        headingRowHeight: 0,
        dataRowHeight: 56,
        columns: [
          _buildDataColumn('', Icons.calendar_today_rounded),
          _buildDataColumn('', Icons.category_rounded),
          _buildDataColumn('', Icons.description_rounded),
          _buildDataColumn('', Icons.arrow_upward_rounded),
          _buildDataColumn('', Icons.arrow_downward_rounded),
          _buildDataColumn('', Icons.account_balance_rounded),
        ],
        rows: [
          DataRow(
            cells: [
              const DataCell(SizedBox.shrink()),
              const DataCell(SizedBox.shrink()),
              DataCell(
                Text(
                  'Totals:',
                  style: GoogleFonts.montserrat(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey[900],
                  ),
                ),
              ),
              DataCell(
                Text(
                  '\$${totalCharges.toStringAsFixed(2)}',
                  style: GoogleFonts.montserrat(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Colors.red[700],
                  ),
                ),
              ),
              DataCell(
                Text(
                  '\$${totalReceipts.toStringAsFixed(2)}',
                  style: GoogleFonts.montserrat(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Colors.green[700],
                  ),
                ),
              ),
              DataCell(
                Text(
                  finalBalance != null
                      ? '\$${finalBalance.toStringAsFixed(2)}'
                      : '-',
                  style: GoogleFonts.montserrat(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: (finalBalance ?? 0) < 0
                        ? Colors.red[700]
                        : Colors.grey[900],
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildOutstandingFeesTotalsRowDataTable(List<OutstandingFee> fees) {
    // Calculate totals
    double totalAmount = 0.0;
    double totalPaid = 0.0;
    double totalDue = 0.0;

    for (var fee in fees) {
      totalAmount += fee.totalAmount;
      totalPaid += fee.paidAmount;
      totalDue += fee.dueAmount;
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.red.withOpacity(0.05),
        border: Border(
          top: BorderSide(color: Colors.red.withOpacity(0.2), width: 2),
        ),
      ),
      child: DataTable(
        headingRowHeight: 0,
        dataRowHeight: 56,
        columns: [
          _buildDataColumn('', Icons.receipt_long_rounded),
          _buildDataColumn('', Icons.category_rounded),
          _buildDataColumn('', Icons.attach_money_rounded),
          _buildDataColumn('', Icons.check_circle_rounded),
          _buildDataColumn('', Icons.error_outline_rounded),
          _buildDataColumn('', Icons.calendar_today_rounded),
          _buildDataColumn('', Icons.info_rounded),
        ],
        rows: [
          DataRow(
            cells: [
              const DataCell(SizedBox.shrink()),
              const DataCell(SizedBox.shrink()),
              DataCell(
                Text(
                  'Totals:',
                  style: GoogleFonts.montserrat(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey[900],
                  ),
                ),
              ),
              DataCell(
                Text(
                  '\$${totalAmount.toStringAsFixed(2)}',
                  style: GoogleFonts.montserrat(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey[900],
                  ),
                ),
              ),
              DataCell(
                Text(
                  '\$${totalPaid.toStringAsFixed(2)}',
                  style: GoogleFonts.montserrat(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Colors.green[700],
                  ),
                ),
              ),
              DataCell(
                Text(
                  '\$${totalDue.toStringAsFixed(2)}',
                  style: GoogleFonts.montserrat(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Colors.red[700],
                  ),
                ),
              ),
              const DataCell(SizedBox.shrink()),
              const DataCell(SizedBox.shrink()),
            ],
          ),
        ],
      ),
    );
  }

  DataColumn _buildDataColumn(String label, IconData icon) {
    return DataColumn(
      label: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: AppConstants.primaryColor),
          const SizedBox(width: 8),
          Text(
            label,
            style: GoogleFonts.montserrat(
              fontWeight: FontWeight.bold,
              fontSize: 13,
              color: AppConstants.primaryColor,
            ),
          ),
        ],
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Paid':
        return Colors.green;
      case 'Unpaid':
        return Colors.red;
      case 'Partially Paid':
        return Colors.orange;
      case 'Overdue':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
