import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../providers/admission_provider.dart';

class AdmissionStatisticsPage extends StatefulWidget {
  const AdmissionStatisticsPage({super.key});

  @override
  State<AdmissionStatisticsPage> createState() => _AdmissionStatisticsPageState();
}

class _AdmissionStatisticsPageState extends State<AdmissionStatisticsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AdmissionProvider>().loadStats();
      context.read<AdmissionProvider>().loadAdmissions();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Admission Statistics',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF6D28D9),
        elevation: 0,
      ),
      body: Consumer<AdmissionProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.stats == null) {
            return const Center(
              child: CircularProgressIndicator(color: Color(0xFF6D28D9)),
            );
          }

          if (provider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    provider.error!,
                    style: GoogleFonts.montserrat(
                      fontSize: 16,
                      color: Colors.grey[600],
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      provider.loadStats();
                      provider.loadAdmissions();
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF6D28D9),
                    ),
                    child: Text('Retry', style: GoogleFonts.montserrat()),
                  ),
                ],
              ),
            );
          }

          final stats = provider.stats;
          
          if (stats == null) {
            return Center(
              child: Text(
                'No statistics available',
                style: GoogleFonts.montserrat(fontSize: 16),
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () async {
              await provider.loadStats();
              await provider.loadAdmissions();
            },
            color: const Color(0xFF6D28D9),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Overview Section
                _buildSectionTitle('Overview'),
                const SizedBox(height: 16),
                _buildOverviewCards(stats),
                
                const SizedBox(height: 32),
                
                // Status Breakdown
                _buildSectionTitle('Application Status'),
                const SizedBox(height: 16),
                _buildStatusBreakdown(stats),
                
                const SizedBox(height: 32),
                
                // Recent Activity
                _buildSectionTitle('Recent Activity'),
                const SizedBox(height: 16),
                _buildRecentActivity(stats),
                
                const SizedBox(height: 32),
                
                // Quick Stats
                _buildSectionTitle('Quick Stats'),
                const SizedBox(height: 16),
                _buildQuickStats(stats),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: GoogleFonts.montserrat(
        fontSize: 18,
        fontWeight: FontWeight.w600,
        color: const Color(0xFF6D28D9),
      ),
    );
  }

  Widget _buildOverviewCards(stats) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'Total Applications',
                stats.totalApplications.toString(),
                Icons.description,
                Colors.blue,
                Colors.blue.withOpacity(0.1),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                'This Month',
                stats.thisMonth.toString(),
                Icons.calendar_month,
                const Color(0xFF6D28D9),
                const Color(0xFF6D28D9).withOpacity(0.1),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildStatCard(
                'This Week',
                stats.thisWeek.toString(),
                Icons.calendar_today,
                const Color(0xFFFF9E02),
                const Color(0xFFFF9E02).withOpacity(0.1),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildStatCard(
                'Enrolled',
                stats.enrolled.toString(),
                Icons.check_circle,
                Colors.green,
                Colors.green.withOpacity(0.1),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String label,
    String value,
    IconData icon,
    Color color,
    Color bgColor,
  ) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.2)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: GoogleFonts.montserrat(
              fontSize: 28,
              fontWeight: FontWeight.w700,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: GoogleFonts.montserrat(
              fontSize: 12,
              color: Colors.grey[600],
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBreakdown(stats) {
    final total = stats.totalApplications;
    final pending = stats.pendingReview;
    final approved = stats.approved;
    final rejected = stats.rejected;
    
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          _buildStatusRow('Pending Review', pending, Colors.orange, total),
          const Divider(height: 24),
          _buildStatusRow('Approved', approved, Colors.green, total),
          const Divider(height: 24),
          _buildStatusRow('Rejected', rejected, Colors.red, total),
        ],
      ),
    );
  }

  Widget _buildStatusRow(String label, int count, Color color, int total) {
    final percentage = total > 0 ? (count / total * 100).toStringAsFixed(1) : '0.0';
    final progressValue = total > 0 ? count / total : 0.0;
    
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              children: [
                Container(
                  width: 12,
                  height: 12,
                  decoration: BoxDecoration(
                    color: color,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
                const SizedBox(width: 12),
                Text(
                  label,
                  style: GoogleFonts.montserrat(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
            Text(
              '$count ($percentage%)',
              style: GoogleFonts.montserrat(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: color,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: LinearProgressIndicator(
            value: progressValue,
            backgroundColor: Colors.grey[200],
            valueColor: AlwaysStoppedAnimation<Color>(color),
            minHeight: 8,
          ),
        ),
      ],
    );
  }

  Widget _buildRecentActivity(stats) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          _buildActivityItem(
            'New applications this week',
            stats.thisWeek.toString(),
            Icons.fiber_new,
            const Color(0xFFFF9E02),
          ),
          const Divider(height: 24),
          _buildActivityItem(
            'Applications this month',
            stats.thisMonth.toString(),
            Icons.trending_up,
            Colors.blue,
          ),
          const Divider(height: 24),
          _buildActivityItem(
            'Pending review',
            stats.pendingReview.toString(),
            Icons.pending_actions,
            Colors.orange,
          ),
        ],
      ),
    );
  }

  Widget _buildActivityItem(String label, String value, IconData icon, Color color) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(icon, color: color, size: 24),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Text(
            label,
            style: GoogleFonts.montserrat(
              fontSize: 14,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        Text(
          value,
          style: GoogleFonts.montserrat(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: color,
          ),
        ),
      ],
    );
  }

  Widget _buildQuickStats(stats) {
    final approvalRate = stats.totalApplications > 0
        ? (stats.approved / stats.totalApplications * 100).toStringAsFixed(1)
        : '0.0';
    final rejectionRate = stats.totalApplications > 0
        ? (stats.rejected / stats.totalApplications * 100).toStringAsFixed(1)
        : '0.0';
    final enrollmentRate = stats.approved > 0
        ? (stats.enrolled / stats.approved * 100).toStringAsFixed(1)
        : '0.0';
    
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _buildQuickStatCard(
                'Approval Rate',
                '$approvalRate%',
                Icons.thumb_up,
                Colors.green,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildQuickStatCard(
                'Rejection Rate',
                '$rejectionRate%',
                Icons.thumb_down,
                Colors.red,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        _buildQuickStatCard(
          'Enrollment Rate',
          '$enrollmentRate%',
          Icons.school,
          const Color(0xFF6D28D9),
        ),
      ],
    );
  }

  Widget _buildQuickStatCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 32),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: GoogleFonts.montserrat(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: color,
                  ),
                ),
                Text(
                  label,
                  style: GoogleFonts.montserrat(
                    fontSize: 12,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}














