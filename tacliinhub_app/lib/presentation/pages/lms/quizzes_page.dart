import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/lms_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';

class QuizzesPage extends StatefulWidget {
  const QuizzesPage({super.key});

  @override
  State<QuizzesPage> createState() => _QuizzesPageState();
}

class _QuizzesPageState extends State<QuizzesPage> {
  int? _selectedClassId;
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<LmsProvider>();
      provider.loadQuizzes(userId: user?.id);

      final studentProvider = context.read<StudentProvider>();
      studentProvider.loadClasses();
    });
  }

  void _loadQuizzes() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<LmsProvider>();
    provider.loadQuizzes(
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
          'Online Quizzes',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.pushNamed(context, '/lms/quizzes/add'),
            tooltip: 'Add Quiz',
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
                        _loadQuizzes();
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
                    _loadQuizzes();
                  },
                ),
              ],
            ),
          ),
          // Quizzes List
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
                        Text(provider.error ?? 'Error loading quizzes'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadQuizzes,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.quizzes.isEmpty) {
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
                          'No quizzes found',
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
                  itemCount: provider.quizzes.length,
                  itemBuilder: (context, index) {
                    final quiz = provider.quizzes[index];
                    final startDate = DateTime.parse(quiz.startDate);
                    final endDate = quiz.endDate != null
                        ? DateTime.parse(quiz.endDate!)
                        : null;
                    final isActive =
                        quiz.status == 'Active' &&
                        DateTime.now().isAfter(startDate) &&
                        (endDate == null || DateTime.now().isBefore(endDate));

                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        contentPadding: const EdgeInsets.all(16),
                        leading: CircleAvatar(
                          backgroundColor: isActive
                              ? Colors.purple.withOpacity(0.1)
                              : Colors.grey.withOpacity(0.1),
                          child: Icon(
                            Icons.quiz,
                            color: isActive ? Colors.purple : Colors.grey,
                          ),
                        ),
                        title: Text(
                          quiz.title,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(quiz.description),
                            const SizedBox(height: 8),
                            Wrap(
                              spacing: 8,
                              children: [
                                Chip(
                                  label: Text(quiz.className),
                                  labelStyle: const TextStyle(fontSize: 12),
                                  padding: EdgeInsets.zero,
                                ),
                                if (quiz.subjectName != null)
                                  Chip(
                                    label: Text(quiz.subjectName!),
                                    labelStyle: const TextStyle(fontSize: 12),
                                    padding: EdgeInsets.zero,
                                  ),
                                Chip(
                                  label: Text('${quiz.durationMinutes} min'),
                                  labelStyle: const TextStyle(fontSize: 12),
                                  padding: EdgeInsets.zero,
                                ),
                                Chip(
                                  label: Text('${quiz.questionCount} Q'),
                                  labelStyle: const TextStyle(fontSize: 12),
                                  padding: EdgeInsets.zero,
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Start: ${DateFormat('MMM d, yyyy').format(startDate)}',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey[600],
                              ),
                            ),
                            if (endDate != null)
                              Text(
                                'End: ${DateFormat('MMM d, yyyy').format(endDate)}',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey[600],
                                ),
                              ),
                            Text(
                              'Total Marks: ${quiz.totalMarks}',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                        trailing: Icon(
                          isActive ? Icons.play_arrow : Icons.info_outline,
                          color: isActive ? Colors.purple : Colors.grey,
                          size: 24,
                        ),
                        onTap: () {
                          if (isActive) {
                            // Start quiz
                          } else {
                            // Show quiz details
                          }
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
        onPressed: () => Navigator.pushNamed(context, '/lms/quizzes/add'),
        backgroundColor: Colors.purple,
        child: const Icon(Icons.add),
      ),
    );
  }
}
