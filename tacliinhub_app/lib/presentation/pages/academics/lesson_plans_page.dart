import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/academic_provider.dart';
import 'add_lesson_plan_page.dart';

class LessonPlansPage extends StatefulWidget {
  const LessonPlansPage({super.key});

  @override
  State<LessonPlansPage> createState() => _LessonPlansPageState();
}

class _LessonPlansPageState extends State<LessonPlansPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AcademicProvider>().loadLessonPlans();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Lesson Plans',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.teal,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const AddLessonPlanPage()),
              );
              if (result == true) {
                context.read<AcademicProvider>().loadLessonPlans();
              }
            },
            tooltip: 'Add Lesson Plan',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const AddLessonPlanPage()),
          );
          if (result == true) {
            context.read<AcademicProvider>().loadLessonPlans();
          }
        },
        backgroundColor: Colors.teal,
        tooltip: 'Add Lesson Plan',
        child: const Icon(Icons.add),
      ),
      body: Consumer<AcademicProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading lesson plans'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => provider.loadLessonPlans(),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.lessonPlans.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.menu_book, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No lesson plans found',
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
            itemCount: provider.lessonPlans.length,
            itemBuilder: (context, index) {
              final plan = provider.lessonPlans[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: ExpansionTile(
                  leading: CircleAvatar(
                    backgroundColor: Colors.teal.withOpacity(0.1),
                    child: Icon(Icons.menu_book, color: Colors.teal[700]),
                  ),
                  title: Text(
                    plan.title,
                    style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text(
                        '${plan.className ?? "N/A"} - ${plan.subjectName}',
                        style: GoogleFonts.montserrat(fontSize: 12),
                      ),
                      Text(
                        DateFormat('MMM dd, yyyy').format(plan.date),
                        style: GoogleFonts.montserrat(fontSize: 12),
                      ),
                    ],
                  ),
                  children: [
                    if (plan.description != null)
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Description',
                              style: GoogleFonts.montserrat(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              plan.description!,
                              style: GoogleFonts.montserrat(fontSize: 14),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }
}
