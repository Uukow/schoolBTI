import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/reports_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/examination_provider.dart';
import '../../../core/sweet_alert.dart';

class AcademicReportsPage extends StatefulWidget {
  const AcademicReportsPage({super.key});

  @override
  State<AcademicReportsPage> createState() => _AcademicReportsPageState();
}

class _AcademicReportsPageState extends State<AcademicReportsPage> {
  String? _selectedReportType = 'Class Performance';
  int? _selectedClassId;
  int? _selectedSubjectId;
  int? _selectedExamId;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      final studentProvider = context.read<StudentProvider>();
      if (studentProvider.classes.isEmpty) {
        studentProvider.loadClasses();
      }
      final examProvider = context.read<ExaminationProvider>();
      if (examProvider.exams.isEmpty && user != null) {
        examProvider.loadExams(userId: user.id);
      }
    });
  }

  Future<void> _generateReport() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<ReportsProvider>();

      await provider.loadAcademicReport(
        reportType: _selectedReportType!,
        classId: _selectedClassId,
        subjectId: _selectedSubjectId,
        examId: _selectedExamId,
        userId: user?.id,
      );

      if (mounted) {
        if (provider.error != null) {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to generate report',
          );
        } else {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Report generated successfully',
          );
        }
      }
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to generate report: ${e.toString()}',
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Academic Reports',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
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
                DropdownButtonFormField<String>(
                  decoration: InputDecoration(
                    labelText: 'Report Type',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  initialValue: _selectedReportType,
                  items: const [
                    DropdownMenuItem<String>(
                      value: 'Class Performance',
                      child: Text('Class Performance'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Subject Performance',
                      child: Text('Subject Performance'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Exam Results',
                      child: Text('Exam Results'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Grade Distribution',
                      child: Text('Grade Distribution'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedReportType = value;
                    });
                  },
                ),
                const SizedBox(height: 12),
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Class',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      initialValue: _selectedClassId,
                      items: [
                        const DropdownMenuItem<int>(
                          value: null,
                          child: Text('All Classes'),
                        ),
                        ...studentProvider.classes.map((cls) {
                          return DropdownMenuItem<int>(
                            value: cls.id,
                            child: Text(cls.className),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedClassId = value;
                        });
                      },
                    );
                  },
                ),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: _isLoading ? null : _generateReport,
                  icon: _isLoading
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.description),
                  label: const Text('Generate Report'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.purple,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ],
            ),
          ),
          // Report Results
          Expanded(
            child: Consumer<ReportsProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.error_outline,
                            size: 64,
                            color: Colors.red,
                          ),
                          const SizedBox(height: 16),
                          Text(
                            provider.error ?? 'Error loading report',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.montserrat(),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                if (provider.academicReport == null) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.description_outlined,
                            size: 64,
                            color: Colors.grey[400],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'No report generated yet',
                            style: GoogleFonts.montserrat(
                              fontSize: 16,
                              color: Colors.grey[600],
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Select filters and click "Generate Report"',
                            style: GoogleFonts.montserrat(
                              fontSize: 14,
                              color: Colors.grey[500],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                final report = provider.academicReport!;
                return SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                report.reportType,
                                style: GoogleFonts.montserrat(
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              if (report.className != null) ...[
                                const SizedBox(height: 8),
                                Text('Class: ${report.className}'),
                              ],
                              if (report.summary.isNotEmpty) ...[
                                const SizedBox(height: 16),
                                Text(
                                  'Summary',
                                  style: GoogleFonts.montserrat(
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                ...report.summary.entries.map((entry) {
                                  return Padding(
                                    padding: const EdgeInsets.only(bottom: 4),
                                    child: Text('${entry.key}: ${entry.value}'),
                                  );
                                }),
                              ],
                            ],
                          ),
                        ),
                      ),
                      if (report.details.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        Text(
                          'Details',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        ...report.details.map((detail) {
                          return Card(
                            margin: const EdgeInsets.only(bottom: 8),
                            child: ListTile(
                              title: Text(detail['name'] ?? ''),
                              subtitle: Text(detail['description'] ?? ''),
                              trailing: Text(
                                detail['value']?.toString() ?? '',
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.bold,
                                ),
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
}
