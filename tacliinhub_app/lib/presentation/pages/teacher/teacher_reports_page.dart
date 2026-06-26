import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../providers/teacher_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';

/// Teacher-specific Reports page
/// Filters data by teacher assignment only
class TeacherReportsPage extends StatefulWidget {
  const TeacherReportsPage({super.key});

  @override
  State<TeacherReportsPage> createState() => _TeacherReportsPageState();
}

class _TeacherReportsPageState extends State<TeacherReportsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        Provider.of<TeacherProvider>(context, listen: false).loadStats(user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Reports',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _buildReportCard(
            context,
            icon: Icons.assessment,
            title: 'Attendance Reports',
            subtitle: 'View attendance statistics for your classes',
            onTap: () {
              Navigator.pushNamed(context, '/teacher/reports/attendance');
            },
          ),
          _buildReportCard(
            context,
            icon: Icons.emoji_events,
            title: 'Performance Reports',
            subtitle: 'View student performance analytics',
            onTap: () {
              Navigator.pushNamed(context, '/teacher/reports/performance');
            },
          ),
          _buildReportCard(
            context,
            icon: Icons.bar_chart,
            title: 'Class Reports',
            subtitle: 'View comprehensive class reports',
            onTap: () {
              Navigator.pushNamed(context, '/teacher/reports/class');
            },
          ),
        ],
      ),
    );
  }

  Widget _buildReportCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: AppConstants.primaryColor.withOpacity(0.1),
          child: Icon(icon, color: AppConstants.primaryColor),
        ),
        title: Text(title, style: GoogleFonts.montserrat(fontWeight: FontWeight.w600)),
        subtitle: Text(subtitle),
        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
        onTap: onTap,
      ),
    );
  }
}

