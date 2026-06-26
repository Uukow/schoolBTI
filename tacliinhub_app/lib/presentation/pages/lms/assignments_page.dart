import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/lms_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';

class AssignmentsPage extends StatefulWidget {
  const AssignmentsPage({super.key});

  @override
  State<AssignmentsPage> createState() => _AssignmentsPageState();
}

class _AssignmentsPageState extends State<AssignmentsPage> {
  int? _selectedClassId;
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<LmsProvider>();
      provider.loadAssignments(userId: user?.id);

      final studentProvider = context.read<StudentProvider>();
      studentProvider.loadClasses();
    });
  }

  void _loadAssignments() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<LmsProvider>();
    provider.loadAssignments(
      userId: user?.id,
      classId: _selectedClassId,
      status: _selectedStatus,
    );
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
            onPressed: () =>
                Navigator.pushNamed(context, '/lms/assignments/add'),
            tooltip: 'Add Assignment',
          ),
        ],
      ),
      body: Column(
        children: [
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
                        labelText: 'Class',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 8,
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
                        _loadAssignments();
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
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
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
                      value: 'Completed',
                      child: Text('Completed'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                    _loadAssignments();
                  },
                ),
              ],
            ),
          ),
          // Assignments List
          Expanded(
            child: Consumer<LmsProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 48, color: Colors.red),
                        const SizedBox(height: 16),
                        Text(provider.error ?? 'Error loading assignments'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadAssignments,
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
                            fontSize: 16,
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
                    final dueDate = DateTime.parse(assignment.dueDate);
                    final isOverdue =
                        dueDate.isBefore(DateTime.now()) &&
                        assignment.status == 'Active';

                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        contentPadding: const EdgeInsets.all(16),
                        leading: CircleAvatar(
                          backgroundColor: isOverdue
                              ? Colors.red.withOpacity(0.1)
                              : Colors.orange.withOpacity(0.1),
                          child: Icon(
                            Icons.assignment,
                            color: isOverdue ? Colors.red : Colors.orange,
                          ),
                        ),
                        title: Text(
                          assignment.title,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(assignment.description),
                            const SizedBox(height: 8),
                            Wrap(
                              spacing: 8,
                              children: [
                                Chip(
                                  label: Text(assignment.className),
                                  labelStyle: const TextStyle(fontSize: 12),
                                  padding: EdgeInsets.zero,
                                ),
                                if (assignment.subjectName != null)
                                  Chip(
                                    label: Text(assignment.subjectName!),
                                    labelStyle: const TextStyle(fontSize: 12),
                                    padding: EdgeInsets.zero,
                                  ),
                                Chip(
                                  label: Text(assignment.status),
                                  labelStyle: const TextStyle(fontSize: 12),
                                  padding: EdgeInsets.zero,
                                  backgroundColor: assignment.status == 'Active'
                                      ? Colors.green.withOpacity(0.1)
                                      : Colors.grey.withOpacity(0.1),
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Due: ${DateFormat('MMM d, yyyy').format(dueDate)}',
                              style: TextStyle(
                                fontSize: 12,
                                color: isOverdue
                                    ? Colors.red
                                    : Colors.grey[600],
                                fontWeight: isOverdue
                                    ? FontWeight.bold
                                    : FontWeight.normal,
                              ),
                            ),
                            if (assignment.maxMarks != null)
                              Text(
                                'Max Marks: ${assignment.maxMarks}',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey[600],
                                ),
                              ),
                          ],
                        ),
                        trailing: Icon(
                          isOverdue ? Icons.warning : Icons.arrow_forward_ios,
                          color: isOverdue ? Colors.red : Colors.grey,
                          size: 20,
                        ),
                        onTap: () {
                          // Show assignment details
                        },
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.pushNamed(context, '/lms/assignments/add'),
        backgroundColor: Colors.orange,
        child: const Icon(Icons.add),
      ),
    );
  }
}
