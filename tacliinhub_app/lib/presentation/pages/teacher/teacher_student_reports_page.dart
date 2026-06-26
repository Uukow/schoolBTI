import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../providers/teacher_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';

/// Teacher-specific Student Reports page
/// Filters data by teacher assignment only
class TeacherStudentReportsPage extends StatefulWidget {
  const TeacherStudentReportsPage({super.key});

  @override
  State<TeacherStudentReportsPage> createState() => _TeacherStudentReportsPageState();
}

class _TeacherStudentReportsPageState extends State<TeacherStudentReportsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        // Load teacher's classes to filter reports
        Provider.of<TeacherProvider>(context, listen: false).loadClasses(user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Student Reports',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer<TeacherProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.classes.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.classes.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading classes'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      final user = Provider.of<AuthProvider>(context, listen: false).user;
                      if (user != null) {
                        provider.loadClasses(user.id);
                      }
                    },
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.classes.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.assessment, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No classes assigned',
                    style: GoogleFonts.montserrat(
                      fontSize: 18,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: provider.classes.length,
            itemBuilder: (context, index) {
              final classItem = provider.classes[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: AppConstants.primaryColor.withOpacity(0.1),
                    child: const Icon(Icons.class_, color: AppConstants.primaryColor),
                  ),
                  title: Text(
                    '${classItem.className} - ${classItem.subjectName}',
                    style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                  ),
                  subtitle: Text('View student reports for this class'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    // Navigate to detailed reports for this class
                    Navigator.pushNamed(
                      context,
                      '/teacher/student-reports/detail',
                      arguments: {
                        'classId': classItem.classId,
                        'subjectId': classItem.subjectId,
                      },
                    );
                  },
                ),
              );
            },
          );
        },
      ),
    );
  }
}

