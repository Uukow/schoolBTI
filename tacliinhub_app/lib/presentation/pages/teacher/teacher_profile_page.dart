import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';

/// Teacher-specific Profile page
class TeacherProfilePage extends StatefulWidget {
  const TeacherProfilePage({super.key});

  @override
  State<TeacherProfilePage> createState() => _TeacherProfilePageState();
}

class _TeacherProfilePageState extends State<TeacherProfilePage> {
  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          'My Profile',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    CircleAvatar(
                      radius: 50,
                      backgroundColor: AppConstants.primaryColor.withOpacity(0.1),
                      backgroundImage: user?.profileImage != null && user!.profileImage!.isNotEmpty
                          ? NetworkImage('${AppConstants.baseUrl}/${user.profileImage}')
                          : null,
                      child: user?.profileImage == null || (user?.profileImage?.isEmpty ?? true)
                          ? Text(
                              (user?.fullName != null && user!.fullName.isNotEmpty)
                                  ? user.fullName[0].toUpperCase()
                                  : 'U',
                              style: const TextStyle(fontSize: 40, color: AppConstants.primaryColor),
                            )
                          : null,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      user?.fullName ?? 'Teacher',
                      style: GoogleFonts.montserrat(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      user?.email ?? '',
                      style: GoogleFonts.montserrat(
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: ListTile(
                leading: const Icon(Icons.person),
                title: Text('Full Name', style: GoogleFonts.montserrat(fontWeight: FontWeight.w600)),
                subtitle: Text(user?.fullName ?? 'N/A'),
              ),
            ),
            Card(
              child: ListTile(
                leading: const Icon(Icons.email),
                title: Text('Email', style: GoogleFonts.montserrat(fontWeight: FontWeight.w600)),
                subtitle: Text(user?.email ?? 'N/A'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

