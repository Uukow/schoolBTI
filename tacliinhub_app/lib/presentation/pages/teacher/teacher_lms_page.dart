import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../widgets/role_based_drawer.dart';

/// Teacher-specific LMS page
/// Filters LMS content by teacher assignment
class TeacherLmsPage extends StatefulWidget {
  const TeacherLmsPage({super.key});

  @override
  State<TeacherLmsPage> createState() => _TeacherLmsPageState();
}

class _TeacherLmsPageState extends State<TeacherLmsPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'LMS',
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
          _buildLmsCard(
            context,
            icon: Icons.description,
            title: 'Study Materials',
            subtitle: 'Upload and manage study materials',
            onTap: () {
              Navigator.pushNamed(context, '/teacher/study-materials');
            },
          ),
          _buildLmsCard(
            context,
            icon: Icons.assignment,
            title: 'Assignments',
            subtitle: 'Create and manage assignments',
            onTap: () {
              Navigator.pushNamed(context, '/teacher/assignments');
            },
          ),
          _buildLmsCard(
            context,
            icon: Icons.quiz,
            title: 'Online Quizzes',
            subtitle: 'Create and manage quizzes',
            onTap: () {
              Navigator.pushNamed(context, '/teacher/quizzes');
            },
          ),
        ],
      ),
    );
  }

  Widget _buildLmsCard(
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

