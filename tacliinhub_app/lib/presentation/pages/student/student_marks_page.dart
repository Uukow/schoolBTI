import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/constants.dart';
import '../../providers/student_portal_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/examination_provider.dart';
import '../../widgets/role_based_drawer.dart';

class StudentMarksPage extends StatefulWidget {
  const StudentMarksPage({super.key});

  @override
  State<StudentMarksPage> createState() => _StudentMarksPageState();
}

class _StudentMarksPageState extends State<StudentMarksPage> {
  int? _selectedExamId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        context.read<ExaminationProvider>().loadExams(userId: user.id);
        context.read<StudentPortalProvider>().loadMarks(userId: user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Marks & Results',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      drawer: const RoleBasedDrawer(),
      body: Column(
        children: [
          // Exam Filter
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<ExaminationProvider>(
              builder: (context, examProvider, child) {
                if (examProvider.exams.isEmpty) {
                  return const SizedBox.shrink();
                }
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Select Exam',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.quiz_rounded),
                  ),
                  initialValue: _selectedExamId,
                  items: [
                    const DropdownMenuItem<int>(
                      value: null,
                      child: Text('All Exams'),
                    ),
                    ...examProvider.exams.map((exam) {
                      return DropdownMenuItem<int>(
                        value: exam.id,
                        child: Text(exam.examName),
                      );
                    }),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedExamId = value;
                    });
                    final user = Provider.of<AuthProvider>(
                      context,
                      listen: false,
                    ).user;
                    if (user != null) {
                      context.read<StudentPortalProvider>().loadMarks(
                        userId: user.id,
                        examId: value,
                      );
                    }
                  },
                );
              },
            ),
          ),
          // Marks List
          Expanded(
            child: Consumer<StudentPortalProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading && provider.marks.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null && provider.marks.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 64,
                          color: Colors.red,
                        ),
                        const SizedBox(height: 16),
                        Text(provider.error ?? 'Error loading marks'),
                      ],
                    ),
                  );
                }

                if (provider.marks.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.quiz_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No marks found',
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
                    final user = Provider.of<AuthProvider>(
                      context,
                      listen: false,
                    ).user;
                    if (user != null) {
                      await provider.loadMarks(
                        userId: user.id,
                        examId: _selectedExamId,
                      );
                    }
                  },
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: provider.marks.length,
                    itemBuilder: (context, index) {
                      final mark = provider.marks[index];
                      final percentage = mark.totalMarks > 0
                          ? ((mark.marksObtained ?? 0) / mark.totalMarks * 100)
                          : 0.0;
                      final color = percentage >= 80
                          ? Colors.green
                          : percentage >= 60
                          ? Colors.orange
                          : Colors.red;

                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        elevation: 2,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Row(
                            children: [
                              Container(
                                width: 60,
                                height: 60,
                                decoration: BoxDecoration(
                                  color: color.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Center(
                                  child: Text(
                                    mark.marksObtained != null
                                        ? mark.marksObtained!.toStringAsFixed(0)
                                        : '-',
                                    style: GoogleFonts.montserrat(
                                      fontSize: 20,
                                      fontWeight: FontWeight.bold,
                                      color: color,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      mark.subjectName,
                                      style: GoogleFonts.montserrat(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 16,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      mark.examName,
                                      style: GoogleFonts.montserrat(
                                        fontSize: 12,
                                        color: Colors.grey[600],
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Row(
                                      children: [
                                        Text(
                                          '${mark.marksObtained ?? 0}/${mark.totalMarks.toStringAsFixed(0)}',
                                          style: GoogleFonts.montserrat(
                                            fontSize: 14,
                                            color: Colors.grey[700],
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: 8,
                                            vertical: 4,
                                          ),
                                          decoration: BoxDecoration(
                                            color: color.withOpacity(0.1),
                                            borderRadius: BorderRadius.circular(
                                              12,
                                            ),
                                          ),
                                          child: Text(
                                            '${percentage.toStringAsFixed(0)}%',
                                            style: GoogleFonts.montserrat(
                                              fontSize: 12,
                                              fontWeight: FontWeight.w600,
                                              color: color,
                                            ),
                                          ),
                                        ),
                                        if (mark.grade != null) ...[
                                          const SizedBox(width: 8),
                                          Container(
                                            padding: const EdgeInsets.symmetric(
                                              horizontal: 8,
                                              vertical: 4,
                                            ),
                                            decoration: BoxDecoration(
                                              color: Colors.blue.withOpacity(
                                                0.1,
                                              ),
                                              borderRadius:
                                                  BorderRadius.circular(12),
                                            ),
                                            child: Text(
                                              mark.grade!,
                                              style: GoogleFonts.montserrat(
                                                fontSize: 12,
                                                fontWeight: FontWeight.w600,
                                                color: Colors.blue,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
