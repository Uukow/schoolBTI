import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/examination_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/auth_provider.dart';

class ResultsPage extends StatefulWidget {
  const ResultsPage({super.key});

  @override
  State<ResultsPage> createState() => _ResultsPageState();
}

class _ResultsPageState extends State<ResultsPage> {
  int? _selectedExamId;
  int? _selectedClassId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = context.read<AuthProvider>().user;
      if (user != null) {
        context.read<ExaminationProvider>().loadExams(userId: user.id);
      }
      context.read<StudentProvider>().loadClasses();
    });
  }

  void _loadResults() {
    final user = context.read<AuthProvider>().user;
    if (user != null) {
      context.read<ExaminationProvider>().loadExamResults(
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
          'Exam Results',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
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
                        _loadResults();
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
                        _loadResults();
                      },
                    );
                  },
                ),
              ],
            ),
          ),

          // Results List
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
                        Text(provider.error ?? 'Error loading results'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadResults,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.examResults.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.assessment_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No results found',
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
                  itemCount: provider.examResults.length,
                  itemBuilder: (context, index) {
                    final result = provider.examResults[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ExpansionTile(
                        leading: CircleAvatar(
                          backgroundColor: _getGradeColor(
                            result.grade,
                          ).withOpacity(0.1),
                          child: Text(
                            result.grade,
                            style: TextStyle(
                              color: _getGradeColor(result.grade),
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        title: Text(
                          result.examName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text('${result.studentName} - ${result.className}'),
                            Text(
                              'Total: ${result.obtainedMarks.toStringAsFixed(0)} / ${result.totalMarks.toStringAsFixed(0)} (${result.percentage.toStringAsFixed(1)}%)',
                              style: GoogleFonts.montserrat(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: _getGradeColor(result.grade),
                              ),
                            ),
                          ],
                        ),
                        children: result.subjects.map((subject) {
                          return ListTile(
                            leading: Icon(
                              subject.isPass
                                  ? Icons.check_circle
                                  : Icons.cancel,
                              color: subject.isPass ? Colors.green : Colors.red,
                            ),
                            title: Text(
                              subject.subjectName,
                              style: GoogleFonts.montserrat(fontSize: 14),
                            ),
                            trailing: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  '${subject.obtainedMarks.toStringAsFixed(0)}/${subject.totalMarks.toStringAsFixed(0)}',
                                  style: GoogleFonts.montserrat(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                Text(
                                  subject.grade,
                                  style: GoogleFonts.montserrat(
                                    fontSize: 12,
                                    color: _getGradeColor(subject.grade),
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Color _getGradeColor(String grade) {
    switch (grade) {
      case 'A+':
      case 'A':
        return Colors.green;
      case 'B':
        return Colors.blue;
      case 'C':
        return Colors.orange;
      case 'D':
      case 'E':
        return Colors.orange[700]!;
      default:
        return Colors.red;
    }
  }
}
