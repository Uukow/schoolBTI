import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../providers/library_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';

/// Teacher-specific Library Resources page
/// Filters resources by teacher assignment
class TeacherLibraryResourcesPage extends StatefulWidget {
  const TeacherLibraryResourcesPage({super.key});

  @override
  State<TeacherLibraryResourcesPage> createState() => _TeacherLibraryResourcesPageState();
}

class _TeacherLibraryResourcesPageState extends State<TeacherLibraryResourcesPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        context.read<LibraryProvider>().loadBooks(userId: user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Library Resources',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer<LibraryProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _buildResourceCard(
                context,
                icon: Icons.menu_book,
                title: 'Books',
                subtitle: 'View available books',
                onTap: () {
                  Navigator.pushNamed(context, '/teacher/books');
                },
              ),
              _buildResourceCard(
                context,
                icon: Icons.history,
                title: 'Issue History',
                subtitle: 'View book issue history',
                onTap: () {
                  Navigator.pushNamed(context, '/teacher/issue-history');
                },
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildResourceCard(
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

