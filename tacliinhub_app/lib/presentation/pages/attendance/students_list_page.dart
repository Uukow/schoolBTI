import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/student_provider.dart';
import '../../widgets/branch_selector.dart';
import '../../../core/constants.dart';

class StudentsListPage extends StatefulWidget {
  const StudentsListPage({super.key});

  @override
  State<StudentsListPage> createState() => _StudentsListPageState();
}

class _StudentsListPageState extends State<StudentsListPage> {
  int? _selectedClassId;
  int? _selectedSectionId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Students List',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: AppConstants.primaryColor,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Branch Selector (for Super Admin)
          const BranchSelector(),
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
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
                      items: studentProvider.classes.map((classItem) {
                        return DropdownMenuItem<int>(
                          value: classItem.id,
                          child: Text(classItem.className),
                        );
                      }).toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedClassId = value;
                          _selectedSectionId = null;
                        });
                        if (value != null) {
                          context.read<StudentProvider>().loadSectionsByClass(
                            value,
                          );
                        }
                      },
                    );
                  },
                ),
                if (_selectedClassId != null) ...[
                  const SizedBox(height: 16),
                  Consumer<StudentProvider>(
                    builder: (context, studentProvider, child) {
                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Select Section',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.group),
                        ),
                        initialValue: _selectedSectionId,
                        items: studentProvider.sections.map((section) {
                          return DropdownMenuItem<int>(
                            value: section.id,
                            child: Text(section.sectionName),
                          );
                        }).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedSectionId = value;
                          });
                          if (value != null) {
                            context.read<StudentProvider>().loadStudents(
                              classId: _selectedClassId,
                              sectionId: value,
                            );
                          }
                        },
                      );
                    },
                  ),
                ],
              ],
            ),
          ),

          // Students List
          Expanded(
            child: Consumer<StudentProvider>(
              builder: (context, studentProvider, child) {
                if (_selectedClassId == null || _selectedSectionId == null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.class_, size: 64, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        Text(
                          'Please select class and section',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                if (studentProvider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (studentProvider.error != null) {
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
                        Text(studentProvider.error ?? 'Error loading students'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () {
                            context.read<StudentProvider>().loadStudents(
                              classId: _selectedClassId,
                              sectionId: _selectedSectionId,
                            );
                          },
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                final students = studentProvider.students
                    .where(
                      (s) =>
                          (s as dynamic)?.currentClassId == _selectedClassId &&
                          (s as dynamic)?.currentSectionId ==
                              _selectedSectionId,
                    )
                    .toList();

                if (students.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.person_outline,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No students found',
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
                  itemCount: students.length,
                  itemBuilder: (context, index) {
                    final student = students[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: AppConstants.primaryColor
                              .withOpacity(0.1),
                          child: Text(
                            student.firstName[0].toUpperCase(),
                            style: TextStyle(
                              color: AppConstants.primaryColor,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        title: Text(
                          '${student.firstName} ${student.lastName}',
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(
                              'ID: ${student.admissionNo}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            if (student.email != null)
                              Text(
                                student.email!,
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                          ],
                        ),
                        trailing: IconButton(
                          icon: const Icon(Icons.visibility),
                          onPressed: () {
                            // Navigate to student attendance history
                            Navigator.pushNamed(
                              context,
                              '/attendance/reports',
                              arguments: {'student_id': student.id},
                            );
                          },
                          tooltip: 'View Attendance',
                        ),
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
}
