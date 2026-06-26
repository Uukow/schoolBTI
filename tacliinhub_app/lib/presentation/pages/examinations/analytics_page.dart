import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/examination_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/constants.dart';

class AnalyticsPage extends StatefulWidget {
  const AnalyticsPage({super.key});

  @override
  State<AnalyticsPage> createState() => _AnalyticsPageState();
}

class _AnalyticsPageState extends State<AnalyticsPage> {
  int? _selectedExamId;
  int? _selectedClassId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      if (user != null) {
        context.read<ExaminationProvider>().loadExams(userId: user.id);
        context.read<ExaminationProvider>().loadAnalytics(userId: user.id);
      }
      context.read<StudentProvider>().loadClasses();
    });
  }

  void _loadAnalytics() {
    final user = context.read<AuthProvider>().user;
    if (user != null) {
      context.read<ExaminationProvider>().loadAnalytics(
        userId: user.id,
        examId: _selectedExamId,
        classId: _selectedClassId,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Exam Analytics',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.red,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                Consumer<ExaminationProvider>(
                  builder: (context, examProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Select Exam',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.assignment),
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
                            child: Text('${exam.examName} - ${exam.className}'),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedExamId = value;
                        });
                        _loadAnalytics();
                      },
                    );
                  },
                ),
                const SizedBox(height: 16),
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Select Class',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.class_),
                      ),
                      initialValue: _selectedClassId,
                      items: [
                        const DropdownMenuItem<int>(
                          value: null,
                          child: Text('All Classes'),
                        ),
                        ...studentProvider.classes.map((classItem) {
                          return DropdownMenuItem<int>(
                            value: classItem.id,
                            child: Text(classItem.className),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedClassId = value;
                        });
                        _loadAnalytics();
                      },
                    );
                  },
                ),
              ],
            ),
          ),

          // Analytics Content
          Expanded(
            child: Consumer<ExaminationProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 48,
                          color: Colors.red,
                        ),
                        const SizedBox(height: 16),
                        Text(provider.error ?? 'Error loading analytics'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadAnalytics,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                final analytics = provider.analytics;
                if (analytics == null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.analytics_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No analytics data available',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Overview Cards
                      Row(
                        children: [
                          Expanded(
                            child: _buildStatCard(
                              'Total Exams',
                              analytics.totalExams.toString(),
                              Colors.blue,
                              Icons.assignment,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: _buildStatCard(
                              'Completed',
                              analytics.completedExams.toString(),
                              Colors.green,
                              Icons.check_circle,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            child: _buildStatCard(
                              'Pending',
                              analytics.pendingExams.toString(),
                              Colors.orange,
                              Icons.pending,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: _buildStatCard(
                              'Avg Marks',
                              analytics.averageMarks.toStringAsFixed(1),
                              Colors.purple,
                              Icons.trending_up,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),

                      // Pass Rate
                      Card(
                        elevation: 2,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Overall Pass Rate',
                                style: GoogleFonts.montserrat(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    '${analytics.passRate.toStringAsFixed(1)}%',
                                    style: GoogleFonts.montserrat(
                                      fontSize: 32,
                                      fontWeight: FontWeight.bold,
                                      color: analytics.passRate >= 75
                                          ? Colors.green
                                          : analytics.passRate >= 50
                                          ? Colors.orange
                                          : Colors.red,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),
                              ClipRRect(
                                borderRadius: BorderRadius.circular(10),
                                child: LinearProgressIndicator(
                                  value: analytics.passRate / 100,
                                  backgroundColor: Colors.grey[200],
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                    analytics.passRate >= 75
                                        ? Colors.green
                                        : analytics.passRate >= 50
                                        ? Colors.orange
                                        : Colors.red,
                                  ),
                                  minHeight: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Class Performance
                      if (analytics.classPerformance.isNotEmpty) ...[
                        Text(
                          'Class Performance',
                          style: GoogleFonts.montserrat(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: AppConstants.primaryColor,
                          ),
                        ),
                        const SizedBox(height: 16),
                        ...analytics.classPerformance.map((classPerf) {
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: Colors.blue.withOpacity(0.1),
                                child: const Icon(
                                  Icons.class_,
                                  color: Colors.blue,
                                ),
                              ),
                              title: Text(
                                classPerf.className,
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const SizedBox(height: 4),
                                  Text(
                                    'Avg Marks: ${classPerf.averageMarks.toStringAsFixed(1)}',
                                    style: GoogleFonts.montserrat(fontSize: 12),
                                  ),
                                  Text(
                                    'Pass Rate: ${classPerf.passRate.toStringAsFixed(1)}%',
                                    style: GoogleFonts.montserrat(fontSize: 12),
                                  ),
                                ],
                              ),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    '${classPerf.passedStudents}/${classPerf.totalStudents}',
                                    style: GoogleFonts.montserrat(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.green,
                                    ),
                                  ),
                                  Text(
                                    'Passed',
                                    style: GoogleFonts.montserrat(
                                      fontSize: 12,
                                      color: Colors.grey[600],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        }),
                        const SizedBox(height: 24),
                      ],

                      // Subject Performance
                      if (analytics.subjectPerformance.isNotEmpty) ...[
                        Text(
                          'Subject Performance',
                          style: GoogleFonts.montserrat(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: AppConstants.primaryColor,
                          ),
                        ),
                        const SizedBox(height: 16),
                        ...analytics.subjectPerformance.map((subjectPerf) {
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: Colors.purple.withOpacity(0.1),
                                child: const Icon(
                                  Icons.book,
                                  color: Colors.purple,
                                ),
                              ),
                              title: Text(
                                subjectPerf.subjectName,
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const SizedBox(height: 4),
                                  Text(
                                    'Avg Marks: ${subjectPerf.averageMarks.toStringAsFixed(1)}',
                                    style: GoogleFonts.montserrat(fontSize: 12),
                                  ),
                                  Text(
                                    'Pass Rate: ${subjectPerf.passRate.toStringAsFixed(1)}%',
                                    style: GoogleFonts.montserrat(fontSize: 12),
                                  ),
                                ],
                              ),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    '${subjectPerf.passedStudents}/${subjectPerf.totalStudents}',
                                    style: GoogleFonts.montserrat(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.green,
                                    ),
                                  ),
                                  Text(
                                    'Passed',
                                    style: GoogleFonts.montserrat(
                                      fontSize: 12,
                                      color: Colors.grey[600],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        }),
                      ],
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    Color color,
    IconData icon,
  ) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(height: 12),
            Text(
              value,
              style: GoogleFonts.montserrat(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: GoogleFonts.montserrat(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
