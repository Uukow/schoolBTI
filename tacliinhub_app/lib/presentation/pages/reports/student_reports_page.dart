import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/reports_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/sweet_alert.dart';

class StudentReportsPage extends StatefulWidget {
  const StudentReportsPage({super.key});

  @override
  State<StudentReportsPage> createState() => _StudentReportsPageState();
}

class _StudentReportsPageState extends State<StudentReportsPage> {
  int? _selectedClassId;
  int? _selectedSectionId;
  int? _selectedStudentId;
  String? _selectedStatus;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final studentProvider = context.read<StudentProvider>();
      if (studentProvider.classes.isEmpty) {
        studentProvider.loadClasses();
      }
    });
  }

  Future<void> _generateReport() async {
    if (_selectedClassId == null && _selectedStudentId == null) {
      SweetAlert.showError(
        context: context,
        title: 'Validation Error',
        message: 'Please select at least a class or a student',
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<ReportsProvider>();

      await provider.loadStudentReports(
        studentId: _selectedStudentId,
        classId: _selectedClassId,
        sectionId: _selectedSectionId,
        status: _selectedStatus,
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
          'Student Reports',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.blue,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: SingleChildScrollView(
              child: Column(
                children: [
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
                          if (studentProvider.isLoading)
                            const DropdownMenuItem<int>(
                              value: null,
                              enabled: false,
                              child: Text('Loading classes...'),
                            ),
                          ...studentProvider.classes.map((cls) {
                            return DropdownMenuItem<int>(
                              value: cls.id,
                              child: Text(cls.className),
                            );
                          }),
                        ],
                        onChanged: studentProvider.isLoading
                            ? null
                            : (value) {
                                setState(() {
                                  _selectedClassId = value;
                                  _selectedSectionId = null;
                                  _selectedStudentId = null;
                                  if (value != null) {
                                    studentProvider.loadSectionsByClass(value);
                                  }
                                });
                              },
                      );
                    },
                  ),
                  const SizedBox(height: 12),
                  if (_selectedClassId != null)
                    Consumer<StudentProvider>(
                      builder: (context, studentProvider, child) {
                        return DropdownButtonFormField<int>(
                          decoration: InputDecoration(
                            labelText: 'Section',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          initialValue: _selectedSectionId,
                          items: [
                            const DropdownMenuItem<int>(
                              value: null,
                              child: Text('All Sections'),
                            ),
                            ...studentProvider.sections.map((section) {
                              return DropdownMenuItem<int>(
                                value: section.id,
                                child: Text(section.sectionName),
                              );
                            }),
                          ],
                          onChanged: (value) {
                            setState(() {
                              _selectedSectionId = value;
                              _selectedStudentId = null;
                              if (value != null && _selectedClassId != null) {
                                studentProvider.loadStudents();
                              }
                            });
                          },
                        );
                      },
                    ),
                  if (_selectedSectionId != null) const SizedBox(height: 12),
                  if (_selectedSectionId != null)
                    Consumer<StudentProvider>(
                      builder: (context, studentProvider, child) {
                        return DropdownButtonFormField<int>(
                          decoration: InputDecoration(
                            labelText: 'Student',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          initialValue: _selectedStudentId,
                          items: [
                            const DropdownMenuItem<int>(
                              value: null,
                              child: Text('All Students'),
                            ),
                            ...studentProvider.students.map((student) {
                              final s = student as dynamic;
                              final name =
                                  s?.fullName ??
                                  '${s?.firstName ?? ''} ${s?.lastName ?? ''}'
                                      .trim();
                              return DropdownMenuItem<int>(
                                value: s?.id ?? 0,
                                child: Text(
                                  name.isEmpty ? 'Unknown Student' : name,
                                ),
                              );
                            }),
                          ],
                          onChanged: (value) {
                            setState(() {
                              _selectedStudentId = value;
                            });
                          },
                        );
                      },
                    ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    decoration: InputDecoration(
                      labelText: 'Status',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    initialValue: _selectedStatus,
                    items: const [
                      DropdownMenuItem<String>(
                        value: null,
                        child: Text('All Status'),
                      ),
                      DropdownMenuItem<String>(
                        value: 'Active',
                        child: Text('Active'),
                      ),
                      DropdownMenuItem<String>(
                        value: 'Inactive',
                        child: Text('Inactive'),
                      ),
                      DropdownMenuItem<String>(
                        value: 'Graduated',
                        child: Text('Graduated'),
                      ),
                    ],
                    onChanged: (value) {
                      setState(() {
                        _selectedStatus = value;
                      });
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
                      backgroundColor: Colors.blue,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ],
              ),
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

                if (provider.studentReports.isEmpty) {
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
                            'No reports generated yet',
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

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: provider.studentReports.length,
                  itemBuilder: (context, index) {
                    final report = provider.studentReports[index];
                    return _buildReportCard(report);
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildReportCard(report) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ExpansionTile(
        leading: CircleAvatar(
          backgroundColor: Colors.blue.withOpacity(0.1),
          child: const Icon(Icons.person, color: Colors.blue),
        ),
        title: Text(
          report.studentName,
          style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text('Admission: ${report.admissionNumber}'),
            Text('Class: ${report.className} - ${report.sectionName}'),
            Text('Status: ${report.status}'),
          ],
        ),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (report.parentName != null) ...[
                  _buildDetailRow('Parent Name', report.parentName!),
                  const SizedBox(height: 8),
                ],
                if (report.parentPhone != null) ...[
                  _buildDetailRow('Parent Phone', report.parentPhone!),
                  const SizedBox(height: 8),
                ],
                if (report.dateOfBirth != null) ...[
                  _buildDetailRow('Date of Birth', report.dateOfBirth!),
                  const SizedBox(height: 8),
                ],
                _buildDetailRow('Admission Date', report.admissionDate),
                if (report.academicSummary != null) ...[
                  const SizedBox(height: 12),
                  Text(
                    'Academic Summary',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 8),
                  ...report.academicSummary!.entries.map((entry) {
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 4),
                      child: Text('${entry.key}: ${entry.value}'),
                    );
                  }),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 120,
          child: Text(
            '$label:',
            style: GoogleFonts.montserrat(
              fontWeight: FontWeight.w600,
              fontSize: 12,
            ),
          ),
        ),
        Expanded(
          child: Text(value, style: GoogleFonts.montserrat(fontSize: 12)),
        ),
      ],
    );
  }
}
