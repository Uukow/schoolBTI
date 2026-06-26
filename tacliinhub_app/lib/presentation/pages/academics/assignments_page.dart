import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/academic_provider.dart';
import '../../providers/student_provider.dart';
import 'add_assignment_page.dart';

class AssignmentsPage extends StatefulWidget {
  const AssignmentsPage({super.key});

  @override
  State<AssignmentsPage> createState() => _AssignmentsPageState();
}

class _AssignmentsPageState extends State<AssignmentsPage> {
  int? _selectedClassId;

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
          'Assignments',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const AddAssignmentPage()),
              );
              if (result == true && _selectedClassId != null) {
                context.read<AcademicProvider>().loadClassAssignments(
                  _selectedClassId!,
                );
              }
            },
            tooltip: 'Add Assignment',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const AddAssignmentPage()),
          );
          if (result == true && _selectedClassId != null) {
            context.read<AcademicProvider>().loadClassAssignments(
              _selectedClassId!,
            );
          }
        },
        backgroundColor: Colors.orange,
        tooltip: 'Add Assignment',
        child: const Icon(Icons.add),
      ),
      body: Column(
        children: [
          // Class selector
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<StudentProvider>(
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
                    });
                    if (value != null) {
                      context.read<AcademicProvider>().loadClassAssignments(
                        value,
                      );
                    }
                  },
                );
              },
            ),
          ),
          // Assignments list
          Expanded(
            child: Consumer<AcademicProvider>(
              builder: (context, provider, child) {
                if (_selectedClassId == null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.assignment_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Please select a class',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

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
                        Text(provider.error ?? 'Error loading assignments'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () =>
                              provider.loadClassAssignments(_selectedClassId!),
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.assignments.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.assignment_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No assignments found',
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
                  itemCount: provider.assignments.length,
                  itemBuilder: (context, index) {
                    final assignment = provider.assignments[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: Colors.orange.withOpacity(0.1),
                          child: Icon(
                            Icons.assignment,
                            color: Colors.orange[700],
                          ),
                        ),
                        title: Text(
                          assignment.subjectName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            if (assignment.teacherName != null)
                              Text(
                                'Teacher: ${assignment.teacherName}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            if (assignment.subjectCode != null)
                              Text(
                                'Code: ${assignment.subjectCode}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                          ],
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
