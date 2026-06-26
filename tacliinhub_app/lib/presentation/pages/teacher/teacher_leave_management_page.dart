import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../providers/hr_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';

/// Teacher-specific Leave Management page
/// Filters leave data by teacher assignment
class TeacherLeaveManagementPage extends StatefulWidget {
  const TeacherLeaveManagementPage({super.key});

  @override
  State<TeacherLeaveManagementPage> createState() => _TeacherLeaveManagementPageState();
}

class _TeacherLeaveManagementPageState extends State<TeacherLeaveManagementPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        context.read<HrProvider>().loadLeaveApplications(userId: user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Leave Management',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.pushNamed(context, '/teacher/leave-management/apply');
        },
        backgroundColor: AppConstants.primaryColor,
        child: const Icon(Icons.add),
      ),
      body: Consumer<HrProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _buildActionCard(
                context,
                icon: Icons.add_circle,
                title: 'Apply for Leave',
                subtitle: 'Submit a new leave request',
                onTap: () {
                  Navigator.pushNamed(context, '/teacher/leave-management/apply');
                },
              ),
              _buildActionCard(
                context,
                icon: Icons.history,
                title: 'My Leave History',
                subtitle: 'View your leave requests and status',
                onTap: () {
                  Navigator.pushNamed(context, '/teacher/leave-management/history');
                },
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildActionCard(
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

