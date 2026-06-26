import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../providers/examination_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';

class StudentReportCardsPage extends StatefulWidget {
  const StudentReportCardsPage({super.key});

  @override
  State<StudentReportCardsPage> createState() => _StudentReportCardsPageState();
}

class _StudentReportCardsPageState extends State<StudentReportCardsPage> {
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
          'Report Cards',
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
                  const Icon(Icons.error_outline, size: 64, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading exams'),
                ],
              ),
            );
          }

          if (provider.exams.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.description_outlined,
                      size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No report cards available',
                    style: GoogleFonts.montserrat(
                      fontSize: 18,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () async {
              final user = Provider.of<AuthProvider>(context, listen: false).user;
              if (user != null) {
                await provider.loadExams(userId: user.id);
              }
            },
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.exams.length,
              itemBuilder: (context, index) {
                final exam = provider.exams[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  elevation: 2,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: ListTile(
                    contentPadding: const EdgeInsets.all(16),
                    leading: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppConstants.primaryColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.description_rounded,
                        color: AppConstants.primaryColor,
                        size: 28,
                      ),
                    ),
                    title: Text(
                      exam.examName,
                      style: GoogleFonts.montserrat(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 4),
                        Text(
                          'Class: ${exam.className}',
                          style: GoogleFonts.montserrat(fontSize: 14),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'View your report card',
                          style: GoogleFonts.montserrat(
                            fontSize: 12,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                    trailing: const Icon(Icons.chevron_right_rounded),
                    onTap: () {
                      Navigator.pushNamed(
                        context,
                        '/examinations/report-cards',
                        arguments: {'examId': exam.id},
                      );
                    },
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}

