import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'academics/subjects_page.dart';
import 'academics/assignments_page.dart';
import 'academics/timetable_page.dart';
import 'academics/lesson_plans_page.dart';
import 'academics/syllabus_page.dart';
import 'academics/calendar_page.dart';
import 'academics/class_graduation_page.dart';
import 'classes_page.dart';

class AcademicsPage extends StatelessWidget {
  const AcademicsPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Academics',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF6D28D9),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Academic Management',
              style: GoogleFonts.montserrat(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF6D28D9),
              ),
            ),
            const SizedBox(height: 24),
            _buildFeatureGrid(context),
          ],
        ),
      ),
    );
  }

  Widget _buildFeatureGrid(BuildContext context) {
    final features = [
      _FeatureItem(
        icon: Icons.class_,
        title: 'Classes',
        color: Colors.blue,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const ClassesPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.book,
        title: 'Subjects',
        color: Colors.green,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const SubjectsPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.assignment,
        title: 'Assignments',
        color: Colors.orange,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const AssignmentsPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.schedule,
        title: 'Timetable',
        color: Colors.purple,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const TimetablePage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.menu_book,
        title: 'Lesson Plans',
        color: Colors.teal,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const LessonPlansPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.description,
        title: 'Syllabus',
        color: Colors.indigo,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const SyllabusPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.calendar_today,
        title: 'Academic Calendar',
        color: Colors.red,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const CalendarPage()),
        ),
      ),
      _FeatureItem(
        icon: Icons.school,
        title: 'Class Graduation',
        color: Colors.amber,
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const ClassGraduationPage()),
        ),
      ),
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 16,
        mainAxisSpacing: 16,
        childAspectRatio: 1.1,
      ),
      itemCount: features.length,
      itemBuilder: (context, index) => _buildFeatureCard(features[index]),
    );
  }

  Widget _buildFeatureCard(_FeatureItem feature) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        onTap: feature.onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                feature.color.withOpacity(0.1),
                feature.color.withOpacity(0.05),
              ],
            ),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                feature.icon,
                size: 48,
                color: feature.color,
              ),
              const SizedBox(height: 12),
              Text(
                feature.title,
                style: GoogleFonts.montserrat(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.black87,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FeatureItem {
  final IconData icon;
  final String title;
  final Color color;
  final VoidCallback onTap;

  _FeatureItem({
    required this.icon,
    required this.title,
    required this.color,
    required this.onTap,
  });
}

