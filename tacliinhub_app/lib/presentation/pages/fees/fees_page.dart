import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../widgets/branch_selector.dart';

class FeesFinancePage extends StatelessWidget {
  const FeesFinancePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Fees & Finance',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: AppConstants.primaryColor,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Branch Selector (for Super Admin)
          const BranchSelector(),
          // Fees Features Grid
          Expanded(
            child: GridView.count(
        crossAxisCount: 2,
        padding: const EdgeInsets.all(16),
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
        children: [
          _buildFeatureCard(
            context,
            'Fee Structure',
            Icons.account_balance,
            Colors.blue,
            '/fees/fee-structure',
          ),
          _buildFeatureCard(
            context,
            'Monthly Fee Assignment',
            Icons.calendar_month,
            Colors.purple,
            '/fees/monthly-assignment',
          ),
          _buildFeatureCard(
            context,
            'Flexible Payment',
            Icons.payment,
            Colors.green,
            '/fees/flexible-payment',
          ),
          _buildFeatureCard(
            context,
            'Student Fee Ledger',
            Icons.book,
            Colors.orange,
            '/fees/ledger',
          ),
          _buildFeatureCard(
            context,
            'Invoices',
            Icons.receipt,
            Colors.teal,
            '/fees/invoices',
          ),
          _buildFeatureCard(
            context,
            'Payments',
            Icons.attach_money,
            Colors.indigo,
            '/fees/payments',
          ),
          _buildFeatureCard(
            context,
            'Defaulters',
            Icons.warning,
            Colors.red,
            '/fees/defaulters',
          ),
          _buildFeatureCard(
            context,
            'Income',
            Icons.trending_up,
            Colors.green[700]!,
            '/fees/income',
          ),
          _buildFeatureCard(
            context,
            'Expenses',
            Icons.trending_down,
            Colors.red[700]!,
            '/fees/expenses',
          ),
          _buildFeatureCard(
            context,
            'Finance Reports',
            Icons.assessment,
            Colors.amber[700]!,
            '/fees/reports',
          ),
        ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFeatureCard(
    BuildContext context,
    String title,
    IconData icon,
    Color color,
    String route,
  ) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        onTap: () {
          Navigator.pushNamed(context, route);
        },
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                color,
                color.withOpacity(0.7),
              ],
            ),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  color: Colors.white,
                  size: 32,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                title,
                textAlign: TextAlign.center,
                style: GoogleFonts.montserrat(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

