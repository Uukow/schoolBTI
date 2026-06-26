import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../core/constants.dart';
import '../../data/models/fee_models.dart';
import '../providers/auth_provider.dart';
import '../providers/fee_provider.dart';
import '../widgets/dashboard_card.dart';

class FeesPage extends StatefulWidget {
  const FeesPage({super.key});

  @override
  State<FeesPage> createState() => _FeesPageState();
}

class _FeesPageState extends State<FeesPage> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadFees();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _loadFees() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user != null) {
      Provider.of<FeeProvider>(context, listen: false).loadFeesSummary(user.id);
    }
  }

  Future<void> _refresh() async {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user != null) {
      await Provider.of<FeeProvider>(context, listen: false)
          .refreshFeesSummary(user.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Fee Management'),
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          tabs: const [
            Tab(text: 'Summary'),
            Tab(text: 'History'),
          ],
        ),
      ),
      body: Consumer<FeeProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.feesSummary == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.feesSummary == null) {
            return _buildErrorState(provider.error!);
          }

          final summary = provider.feesSummary;
          if (summary == null) {
            return _buildEmptyState();
          }

          return TabBarView(
            controller: _tabController,
            children: [
              _buildSummaryTab(summary),
              _buildHistoryTab(summary),
            ],
          );
        },
      ),
    );
  }

  Widget _buildSummaryTab(FeesSummary summary) {
    return RefreshIndicator(
      onRefresh: _refresh,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Overview Cards
            Row(
              children: [
                Expanded(
                  child: DashboardCard(
                    title: 'Total Fees',
                    value: '\$${summary.totalFees.toStringAsFixed(2)}',
                    icon: Icons.account_balance_wallet_outlined,
                    color: Colors.blue,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: DashboardCard(
                    title: 'Paid',
                    value: '\$${summary.paidAmount.toStringAsFixed(2)}',
                    icon: Icons.check_circle_outline,
                    color: Colors.green,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: DashboardCard(
                    title: 'Due',
                    value: '\$${summary.dueAmount.toStringAsFixed(2)}',
                    icon: Icons.pending_outlined,
                    color: Colors.red,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: DashboardCard(
                    title: 'Discount',
                    value: '\$${summary.discountAmount.toStringAsFixed(2)}',
                    icon: Icons.local_offer_outlined,
                    color: Colors.orange,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Payment Progress
            _buildSectionTitle('Payment Progress'),
            const SizedBox(height: 16),
            ProgressCard(
              title: 'Fee Collection Status',
              percentage: summary.totalFees > 0
                  ? (summary.paidAmount / summary.totalFees * 100)
                  : 0.0,
              subtitle:
                  '${summary.paidInvoices} of ${summary.totalInvoices} invoices paid',
              color: Colors.green,
              icon: Icons.account_balance_wallet,
            ),

            const SizedBox(height: 24),

            // Invoice Stats
            _buildSectionTitle('Invoice Statistics'),
            const SizedBox(height: 16),
            Material(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              elevation: 2,
              shadowColor: Colors.black12,
              child: Container(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    _buildStatRow(
                      'Total Invoices',
                      summary.totalInvoices,
                      Icons.receipt_long,
                      Colors.blue,
                    ),
                    const Divider(height: 24),
                    _buildStatRow(
                      'Paid Invoices',
                      summary.paidInvoices,
                      Icons.check_circle,
                      Colors.green,
                    ),
                    const Divider(height: 24),
                    _buildStatRow(
                      'Overdue Invoices',
                      summary.overdueInvoices,
                      Icons.warning,
                      Colors.red,
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Recent Invoices
            if (summary.recentInvoices.isNotEmpty) ...[
              _buildSectionTitle('Recent Invoices'),
              const SizedBox(height: 16),
              ...summary.recentInvoices.take(5).map((invoice) => _buildInvoiceCard(invoice)),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildHistoryTab(FeesSummary summary) {
    return RefreshIndicator(
      onRefresh: _refresh,
      child: summary.recentPayments.isEmpty
          ? _buildEmptyPaymentHistory()
          : ListView.builder(
              padding: const EdgeInsets.all(20),
              itemCount: summary.recentPayments.length + 1,
              itemBuilder: (context, index) {
                if (index == 0) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 16),
                    child: _buildSectionTitle('Payment History'),
                  );
                }
                return _buildPaymentCard(summary.recentPayments[index - 1]);
              },
            ),
    );
  }

  Widget _buildInvoiceCard(FeeInvoice invoice) {
    final statusColor = _getStatusColor(invoice.status);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: 2,
        shadowColor: Colors.black12,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () {
            // TODO: Show invoice details
          },
          child: Container(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      invoice.invoiceNo,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        invoice.status,
                        style: TextStyle(
                          color: statusColor,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Total Amount',
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 12,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '\$${invoice.totalAmount.toStringAsFixed(2)}',
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    if (invoice.dueAmount > 0)
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            'Due Amount',
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 12,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '\$${invoice.dueAmount.toStringAsFixed(2)}',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.red,
                            ),
                          ),
                        ],
                      ),
                  ],
                ),
                if (invoice.dueDate != null) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(Icons.calendar_today, size: 14, color: Colors.grey[600]),
                      const SizedBox(width: 4),
                      Text(
                        'Due: ${DateFormat('MMM d, yyyy').format(DateTime.tryParse(invoice.dueDate!) ?? DateTime.now())}',
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPaymentCard(FeePayment payment) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: 2,
        shadowColor: Colors.black12,
        child: Container(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.green.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.payment,
                  color: Colors.green,
                  size: 24,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '\$${payment.amount.toStringAsFixed(2)}',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      payment.paymentMethod,
                      style: TextStyle(
                        color: Colors.grey[600],
                        fontSize: 13,
                      ),
                    ),
                    if (payment.transactionId != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        'Ref: ${payment.transactionId}',
                        style: TextStyle(
                          color: Colors.grey[500],
                          fontSize: 11,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              Text(
                DateFormat('MMM d').format(
                    DateTime.tryParse(payment.paymentDate) ?? DateTime.now()),
                style: TextStyle(
                  color: Colors.grey[600],
                  fontSize: 12,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatRow(String label, int value, IconData icon, Color color) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: color, size: 20),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        Text(
          '$value',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: const TextStyle(
        fontSize: 20,
        fontWeight: FontWeight.bold,
        color: AppConstants.primaryColor,
        letterSpacing: -0.5,
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.receipt_long, size: 80, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'No fee records available',
            style: TextStyle(
              fontSize: 18,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyPaymentHistory() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.history, size: 80, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'No payment history',
            style: TextStyle(
              fontSize: 18,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState(String error) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red[300]),
            const SizedBox(height: 16),
            Text(
              'Failed to load fees',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              error,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.grey[600],
                fontSize: 14,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _loadFees,
              icon: const Icon(Icons.refresh),
              label: const Text('Try Again'),
            ),
          ],
        ),
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
      case 'unpaid':
      default:
        return Colors.grey;
    }
  }
}














