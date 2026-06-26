import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants.dart';
import '../../data/models/dashboard_models.dart';
import '../providers/auth_provider.dart';
import '../providers/dashboard_provider.dart';
import '../providers/notification_provider.dart';
import '../providers/permissions_provider.dart';
import '../providers/branch_filter_provider.dart';
import '../widgets/role_based_drawer.dart';
import '../widgets/dashboard_card.dart';
import '../widgets/branch_selector.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  int? _lastBranchId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      // Clear old cache on first load to avoid parsing issues
      final prefs = await SharedPreferences.getInstance();
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        await prefs.remove('dashboard_data_${user.id}');
      }
      
      // Initialize branch filter provider
      final branchFilterProvider = Provider.of<BranchFilterProvider>(
        context,
        listen: false,
      );
      branchFilterProvider.initialize(
        Provider.of<AuthProvider>(context, listen: false),
      );
      
      // Store initial branch ID
      _lastBranchId = branchFilterProvider.selectedBranchId;
      
      _loadDashboard();
    });
  }

  void _loadDashboard() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user != null) {
      print('👤 Loading dashboard for user ID: ${user.id}, Role: ${user.role}');
      
      // Initialize branch filter provider
      final branchFilterProvider = Provider.of<BranchFilterProvider>(
        context,
        listen: false,
      );
      branchFilterProvider.initialize(
        Provider.of<AuthProvider>(context, listen: false),
      );
      
      Provider.of<DashboardProvider>(
        context,
        listen: false,
      ).loadDashboardData(user.id, user.role, context: context);
      // Also load notifications count
      Provider.of<NotificationProvider>(
        context,
        listen: false,
      ).loadNotifications(user.id);
      // Load permissions
      Provider.of<PermissionsProvider>(
        context,
        listen: false,
      ).loadPermissions(user.id);
    } else {
      print('⚠️ User is null. Cannot load dashboard.');
      // Show error if user is not logged in
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Please login to view dashboard'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _refreshDashboard() async {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user != null) {
      await Provider.of<DashboardProvider>(
        context,
        listen: false,
      ).refreshDashboardData(user.id, user.role, context: context);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('TacliinHub Dashboard'),
        elevation: 0,
        actions: [
          Consumer<BranchFilterProvider>(
            builder: (context, branchProvider, child) {
              if (!branchProvider.showBranchSelector) {
                return const SizedBox.shrink();
              }
              return BranchSelectorChip();
            },
          ),
          Consumer<NotificationProvider>(
            builder: (context, notifProvider, child) {
              return Stack(
                children: [
                  IconButton(
                    icon: const Icon(Icons.notifications_outlined),
                    onPressed: () {
                      Navigator.pushNamed(context, '/notifications');
                    },
                  ),
                  if (notifProvider.unreadCount > 0)
                    Positioned(
                      right: 8,
                      top: 8,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: Colors.red,
                          shape: BoxShape.circle,
                        ),
                        constraints: const BoxConstraints(
                          minWidth: 16,
                          minHeight: 16,
                        ),
                        child: Center(
                          child: Text(
                            notifProvider.unreadCount > 99
                                ? '99+'
                                : notifProvider.unreadCount.toString(),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              );
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer2<DashboardProvider, AuthProvider>(
        builder: (context, dashboard, authProvider, child) {
          // Check if user is logged in
          final user = authProvider.user;
          if (user == null) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.person_off, size: 64, color: Colors.grey[400]),
                    const SizedBox(height: 16),
                    Text(
                      'Please login to view dashboard',
                      style: TextStyle(fontSize: 18, color: Colors.grey[600]),
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () {
                        Navigator.pushReplacementNamed(context, '/login');
                      },
                      child: const Text('Go to Login'),
                    ),
                  ],
                ),
              ),
            );
          }

          if (dashboard.isLoading && dashboard.data == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (dashboard.error != null && dashboard.data == null) {
            return _buildErrorState(dashboard.error!);
          }

          final data = dashboard.data;
          if (data == null) {
            return _buildEmptyState();
          }

          return Consumer<BranchFilterProvider>(
            builder: (context, branchProvider, child) {
              // Reload dashboard when branch selection changes
              final currentBranchId = branchProvider.selectedBranchId;
              if (_lastBranchId != currentBranchId && mounted) {
                _lastBranchId = currentBranchId;
                WidgetsBinding.instance.addPostFrameCallback((_) {
                  if (mounted) {
                    _loadDashboard();
                  }
                });
              }

              return RefreshIndicator(
                onRefresh: _refreshDashboard,
                child: ListView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  children: [
                    // Header with user greeting
                    _buildHeader(user, dashboard.lastRefresh),

                    // Branch Selector (for Super Admin)
                    const BranchSelector(),

                    // Role-specific dashboard content
                    if (user.role == 'Student' && data.studentDashboard != null)
                      _buildStudentDashboard(data.studentDashboard!)
                    else if (data.adminDashboard != null)
                      _buildAdminDashboard(data.adminDashboard!),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }

  Widget _buildHeader(user, DateTime? lastRefresh) {
    final now = DateTime.now();
    final greeting = now.hour < 12
        ? 'Good Morning'
        : now.hour < 17
        ? 'Good Afternoon'
        : 'Good Evening';

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: AppConstants.primaryColor,
        borderRadius: const BorderRadius.only(
          bottomLeft: Radius.circular(30),
          bottomRight: Radius.circular(30),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(20, 10, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            greeting,
            style: TextStyle(
              color: Colors.white.withOpacity(0.9),
              fontSize: 16,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            user?.fullName ?? 'User',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          if (lastRefresh != null) ...[
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(Icons.access_time, color: Colors.white70, size: 14),
                const SizedBox(width: 4),
                Text(
                  'Last updated: ${DateFormat('MMM d, h:mm a').format(lastRefresh)}',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.8),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ],
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
              'Oops! Something went wrong',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              error,
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey[600], fontSize: 14),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue[50],
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.blue[200]!),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Troubleshooting:',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Colors.blue[900],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '1. Ensure XAMPP Apache is running\n2. Check API URL: ${AppConstants.baseUrl}\n3. Verify network connectivity\n4. Check console logs for details',
                    style: TextStyle(fontSize: 12, color: Colors.blue[800]),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _loadDashboard,
              icon: const Icon(Icons.refresh),
              label: const Text('Try Again'),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(
                  horizontal: 32,
                  vertical: 14,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inbox_outlined, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'No data available',
            style: TextStyle(fontSize: 18, color: Colors.grey[600]),
          ),
        ],
      ),
    );
  }

  Widget _buildStudentDashboard(StudentDashboard data) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Quick Stats Grid
          _buildSectionTitle('Quick Overview'),
          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: DashboardCard(
                  title: 'Attendance',
                  value: '${data.attendance.percentage.toStringAsFixed(1)}%',
                  icon: Icons.check_circle_outline,
                  color: _getAttendanceColor(data.attendance.percentage),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: DashboardCard(
                  title: 'Today',
                  value: data.todayStatus,
                  icon: _getStatusIcon(data.todayStatus),
                  color: _getStatusColor(data.todayStatus),
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: DashboardCard(
                  title: 'Assignments',
                  value: '${data.upcomingAssignments}',
                  icon: Icons.assignment_outlined,
                  color: Colors.orange,
                  onTap: () => Navigator.pushNamed(context, '/assignments'),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: DashboardCard(
                  title: 'Exams',
                  value: '${data.upcomingExams}',
                  icon: Icons.event_note,
                  color: Colors.deepPurple,
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Outstanding Fees (if any)
          if (data.outstandingFees > 0)
            DashboardCardHorizontal(
              title: 'Outstanding Fees',
              value: '\$${data.outstandingFees.toStringAsFixed(2)}',
              icon: Icons.account_balance_wallet_outlined,
              color: Colors.red,
              subtitle: 'Please clear your dues',
            ),

          const SizedBox(height: 24),

          // Attendance Details
          _buildSectionTitle('Attendance Summary'),
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
                  _buildAttendanceRow(
                    'Present Days',
                    data.attendance.present,
                    data.attendance.total,
                    Colors.green,
                  ),
                  const Divider(height: 24),
                  _buildAttendanceRow(
                    'Absent Days',
                    data.attendance.absent,
                    data.attendance.total,
                    Colors.red,
                  ),
                  const Divider(height: 24),
                  _buildAttendanceRow(
                    'Total Days',
                    data.attendance.total,
                    data.attendance.total,
                    AppConstants.primaryColor,
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 24),

          // Today's Timetable
          _buildSectionTitle('Today\'s Schedule'),
          const SizedBox(height: 16),

          if (data.timetable.isEmpty)
            Material(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              elevation: 2,
              child: Container(
                padding: const EdgeInsets.all(24),
                child: Center(
                  child: Column(
                    children: [
                      Icon(Icons.event_busy, size: 48, color: Colors.grey[400]),
                      const SizedBox(height: 12),
                      Text(
                        'No classes scheduled for today',
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                    ],
                  ),
                ),
              ),
            )
          else
            ...data.timetable.map((entry) => _buildTimetableCard(entry)),

          const SizedBox(height: 24),

          // Announcements
          if (data.announcements.isNotEmpty) ...[
            _buildSectionTitle('Recent Announcements'),
            const SizedBox(height: 16),
            ...data.announcements.map(
              (announcement) => _buildAnnouncementCard(announcement),
            ),
          ],

          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildAttendanceRow(String label, int value, int total, Color color) {
    final percentage = total > 0 ? (value / total * 100) : 0.0;
    return Row(
      children: [
        Container(
          width: 8,
          height: 8,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w500),
          ),
        ),
        Text(
          '$value',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        const SizedBox(width: 8),
        Text(
          '(${percentage.toStringAsFixed(1)}%)',
          style: TextStyle(fontSize: 14, color: Colors.grey[600]),
        ),
      ],
    );
  }

  Widget _buildTimetableCard(TimetableEntry entry) {
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
                  color: AppConstants.primaryColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.access_time,
                  color: AppConstants.primaryColor,
                  size: 24,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      entry.subjectName,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Text(
                          '${entry.startTime} - ${entry.endTime}',
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 13,
                          ),
                        ),
                        if (entry.roomNo != null) ...[
                          const Text(
                            ' • ',
                            style: TextStyle(color: Colors.grey),
                          ),
                          Text(
                            'Room ${entry.roomNo}',
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
              if (entry.subjectCode != null)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: AppConstants.secondaryColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    entry.subjectCode!,
                    style: const TextStyle(
                      color: AppConstants.secondaryColor,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAnnouncementCard(Announcement announcement) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: 2,
        shadowColor: Colors.black12,
        child: Container(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.blue.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.campaign,
                      color: Colors.blue,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      announcement.title,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                announcement.content,
                style: TextStyle(
                  color: Colors.grey[700],
                  fontSize: 14,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                DateFormat('MMM d, yyyy').format(
                  DateTime.tryParse(announcement.createdAt) ?? DateTime.now(),
                ),
                style: TextStyle(color: Colors.grey[500], fontSize: 12),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Color _getAttendanceColor(double percentage) {
    if (percentage >= 90) return Colors.green;
    if (percentage >= 75) return Colors.orange;
    return Colors.red;
  }

  IconData _getStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'present':
        return Icons.check_circle;
      case 'absent':
        return Icons.cancel;
      case 'late':
        return Icons.access_time;
      default:
        return Icons.help_outline;
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'present':
        return Colors.green;
      case 'absent':
        return Colors.red;
      case 'late':
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  Widget _buildAdminDashboard(AdminDashboard data) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Key Metrics
          _buildSectionTitle('Key Metrics'),
          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: DashboardCard(
                  title: 'Total Students',
                  value: '${data.students.total}',
                  icon: Icons.people_outline,
                  color: Colors.blue,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: DashboardCard(
                  title: 'Total Staff',
                  value: '${data.staff.total}',
                  icon: Icons.badge_outlined,
                  color: Colors.orange,
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: DashboardCard(
                  title: 'Active Classes',
                  value: '${data.classes.active}',
                  icon: Icons.class_outlined,
                  color: Colors.purple,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: DashboardCard(
                  title: 'Active Students',
                  value: '${data.students.active}',
                  icon: Icons.verified_user_outlined,
                  color: Colors.green,
                ),
              ),
            ],
          ),

          const SizedBox(height: 24),

          // Financial Overview
          _buildSectionTitle('Financial Overview'),
          const SizedBox(height: 16),

          ProgressCard(
            title: 'Fee Collection Rate',
            percentage: data.fees.total > 0
                ? (data.fees.paid / data.fees.total * 100)
                : 0.0,
            subtitle:
                '\$${data.fees.paid.toStringAsFixed(2)} of \$${data.fees.total.toStringAsFixed(2)}',
            color: Colors.green,
            icon: Icons.monetization_on,
          ),

          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: DashboardCard(
                  title: 'Revenue (Month)',
                  value: '\$${_formatNumber(data.revenueMonth)}',
                  icon: Icons.trending_up,
                  color: Colors.teal,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: DashboardCard(
                  title: 'Outstanding',
                  value: '\$${_formatNumber(data.outstandingFees)}',
                  icon: Icons.account_balance_wallet_outlined,
                  color: Colors.red,
                ),
              ),
            ],
          ),

          const SizedBox(height: 24),

          // Attendance Overview
          _buildSectionTitle('Attendance Today'),
          const SizedBox(height: 16),

          ProgressCard(
            title: 'Attendance Rate',
            percentage: data.attendanceToday.percentage,
            subtitle:
                '${data.attendanceToday.present} present of ${data.attendanceToday.total} total',
            color: _getAttendanceColor(data.attendanceToday.percentage),
            icon: Icons.how_to_reg,
          ),

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
                  _buildMetricRow(
                    'Present',
                    data.attendanceToday.present,
                    Icons.check_circle,
                    Colors.green,
                  ),
                  const Divider(height: 24),
                  _buildMetricRow(
                    'Absent',
                    data.attendanceToday.absent ?? 0,
                    Icons.cancel,
                    Colors.red,
                  ),
                  if (data.attendanceToday.late != null) ...[
                    const Divider(height: 24),
                    _buildMetricRow(
                      'Late',
                      data.attendanceToday.late!,
                      Icons.access_time,
                      Colors.orange,
                    ),
                  ],
                ],
              ),
            ),
          ),

          const SizedBox(height: 24),

          // Academic Progress
          _buildSectionTitle('Academic Progress'),
          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: DashboardCard(
                  title: 'Ongoing Exams',
                  value: '${data.exams.ongoing}',
                  icon: Icons.event_available,
                  color: Colors.blue,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: DashboardCard(
                  title: 'Assignments',
                  value: '${data.assignments.total}',
                  icon: Icons.assignment_outlined,
                  color: Colors.orange,
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          ProgressCard(
            title: 'Attendance Completion',
            percentage: data.attendanceCompletion.percentage,
            subtitle:
                '${data.attendanceCompletion.completed} of ${data.attendanceCompletion.totalClasses} classes',
            color: data.attendanceCompletion.percentage >= 80
                ? Colors.green
                : Colors.orange,
            icon: Icons.task_alt,
          ),

          const SizedBox(height: 24),

          // Alerts & Notifications
          _buildSectionTitle('Alerts & Notifications'),
          const SizedBox(height: 16),

          if (data.pendingAdmissions > 0)
            _buildAlertCard(
              'Pending Admissions',
              '${data.pendingAdmissions} applications need review',
              Icons.person_add,
              Colors.blue,
            ),

          if (data.overdueInvoices > 0)
            _buildAlertCard(
              'Overdue Invoices',
              '${data.overdueInvoices} invoices are overdue',
              Icons.warning,
              Colors.red,
            ),

          if (data.incompleteProfiles > 0)
            _buildAlertCard(
              'Incomplete Profiles',
              '${data.incompleteProfiles} student profiles need completion',
              Icons.account_circle_outlined,
              Colors.orange,
            ),

          if (data.openTickets > 0)
            _buildAlertCard(
              'Support Tickets',
              '${data.openTickets} tickets awaiting response',
              Icons.support_agent,
              Colors.purple,
            ),

          const SizedBox(height: 24),

          // Top Performing Classes
          if (data.topClasses.isNotEmpty) ...[
            _buildSectionTitle('Top Performing Classes'),
            const SizedBox(height: 16),
            Material(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              elevation: 2,
              shadowColor: Colors.black12,
              child: Container(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: data.topClasses
                      .take(5)
                      .map((cls) => _buildTopClassRow(cls))
                      .toList(),
                ),
              ),
            ),
          ],

          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildMetricRow(String label, int value, IconData icon, Color color) {
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
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w500),
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

  Widget _buildAlertCard(
    String title,
    String description,
    IconData icon,
    Color color,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: color.withOpacity(0.05),
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () {
            // TODO: Navigate to relevant page
          },
          child: Container(
            padding: const EdgeInsets.all(16),
            child: Row(
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
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: color,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        description,
                        style: TextStyle(color: Colors.grey[700], fontSize: 13),
                      ),
                    ],
                  ),
                ),
                Icon(
                  Icons.arrow_forward_ios,
                  color: Colors.grey[400],
                  size: 16,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildTopClassRow(TopClass cls) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: Colors.grey[200]!)),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: AppConstants.primaryColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Center(
              child: Text(
                cls.classCode ?? cls.className.substring(0, 1),
                style: const TextStyle(
                  color: AppConstants.primaryColor,
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  cls.className,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${cls.studentsCount} students',
                  style: TextStyle(color: Colors.grey[600], fontSize: 12),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.green.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              '${cls.avgPercentage.toStringAsFixed(1)}%',
              style: const TextStyle(
                color: Colors.green,
                fontSize: 14,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatNumber(double number) {
    if (number >= 1000000) {
      return '${(number / 1000000).toStringAsFixed(1)}M';
    } else if (number >= 1000) {
      return '${(number / 1000).toStringAsFixed(1)}K';
    }
    return number.toStringAsFixed(0);
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
}
