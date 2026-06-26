import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'general_settings_page.dart';
import 'academic_settings_page.dart';
import 'user_management_page.dart';
import 'roles_permissions_page.dart';
import 'granular_permissions_page.dart';
import 'backup_restore_page.dart';
import 'about_license_page.dart';

class SettingsMainPage extends StatelessWidget {
  const SettingsMainPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Settings',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.indigo,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'System Settings',
              style: GoogleFonts.montserrat(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Colors.indigo,
              ),
            ),
            const SizedBox(height: 24),
            _buildFeatureGrid(context),
          ],
        ),
      ),
    );
  }

  Widget _buildFeatureGrid(BuildContext context) {
    final features = [
      _FeatureItem(
        icon: Icons.settings,
        title: 'General Settings',
        color: Colors.blue,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const GeneralSettingsPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.school,
        title: 'Academic Settings',
        color: Colors.green,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const AcademicSettingsPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.people,
        title: 'User Management',
        color: Colors.orange,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const UserManagementPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.admin_panel_settings,
        title: 'Roles & Permissions',
        color: Colors.purple,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const RolesPermissionsPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.security,
        title: 'Granular Permissions',
        color: Colors.red,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const GranularPermissionsPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.backup,
        title: 'Backup & Restore',
        color: Colors.teal,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const BackupRestorePage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.info,
        title: 'About & License',
        color: Colors.grey,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const AboutLicensePage()),
        ),
      ),
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
        childAspectRatio: 1.2,
      ),
      itemCount: features.length,
      itemBuilder: (context, index) {
        return _FeatureCard(item: features[index]);
      },
    );
  }
}

class _FeatureItem {
  final IconData icon;
  final String title;
  final Color color;
  final VoidCallback onTap;

  _FeatureItem({
    required this.icon,
    required this.title,
    required this.color,
    required this.onTap,
  });
}

class _FeatureCard extends StatelessWidget {
  final _FeatureItem item;

  const _FeatureCard({required this.item});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: InkWell(
        onTap: item.onTap,
        borderRadius: BorderRadius.circular(12),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              item.icon,
              size: 48,
              color: item.color,
            ),
            const SizedBox(height: 12),
            Text(
              item.title,
              textAlign: TextAlign.center,
              style: GoogleFonts.montserrat(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: Colors.grey[800],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

