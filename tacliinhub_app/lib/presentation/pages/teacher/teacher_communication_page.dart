import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../widgets/role_based_drawer.dart';

/// Teacher-specific Communication page
class TeacherCommunicationPage extends StatefulWidget {
  const TeacherCommunicationPage({super.key});

  @override
  State<TeacherCommunicationPage> createState() => _TeacherCommunicationPageState();
}

class _TeacherCommunicationPageState extends State<TeacherCommunicationPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Communication',
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
          _buildCommCard(
            context,
            icon: Icons.campaign,
            title: 'Announcements',
            subtitle: 'View and create announcements',
            onTap: () {
              Navigator.pushNamed(context, '/teacher/announcements');
            },
          ),
          _buildCommCard(
            context,
            icon: Icons.chat,
            title: 'Messages',
            subtitle: 'Send and receive messages',
            onTap: () {
              Navigator.pushNamed(context, '/teacher/messages');
            },
          ),
        ],
      ),
    );
  }

  Widget _buildCommCard(
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

