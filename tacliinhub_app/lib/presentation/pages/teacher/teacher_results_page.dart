import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../providers/examination_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';

/// Teacher-specific Results page
/// Filters exam results by teacher assignment only
class TeacherResultsPage extends StatefulWidget {
  const TeacherResultsPage({super.key});

  @override
  State<TeacherResultsPage> createState() => _TeacherResultsPageState();
}

class _TeacherResultsPageState extends State<TeacherResultsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        context.read<ExaminationProvider>().loadExams(userId: user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Results',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer<ExaminationProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.exams.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.exams.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading exams'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      final user = Provider.of<AuthProvider>(context, listen: false).user;
                      if (user != null) {
                        provider.loadExams(userId: user.id);
                      }
                    },
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.exams.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.emoji_events, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No exams found',
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
            itemCount: provider.exams.length,
            itemBuilder: (context, index) {
              final exam = provider.exams[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: AppConstants.primaryColor.withOpacity(0.1),
                    child: const Icon(Icons.emoji_events, color: AppConstants.primaryColor),
                  ),
                  title: Text(
                    exam.examName,
                    style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                  ),
                  subtitle: Text('${exam.className} - View results'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () {
                    Navigator.pushNamed(
                      context,
                      '/examinations/results',
                      arguments: {'examId': exam.id},
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

