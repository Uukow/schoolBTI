import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../core/sweet_alert.dart';

class ClassGraduationPage extends StatelessWidget {
  const ClassGraduationPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Class Graduation',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.amber,
        elevation: 0,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.school,
                size: 80,
                color: Colors.amber[700],
              ),
              const SizedBox(height: 24),
              Text(
                'Class Graduation',
                style: GoogleFonts.montserrat(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Colors.amber[900],
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'This feature allows you to graduate classes and move students to the next academic level.',
                style: GoogleFonts.montserrat(
                  fontSize: 16,
                  color: Colors.grey[700],
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 32),
              ElevatedButton.icon(
                onPressed: () {
                  SweetAlert.showInfo(
                    context: context,
                    title: 'Coming Soon',
                    message: 'Class graduation feature will be available in the next update.',
                  );
                },
                icon: const Icon(Icons.info_outline),
                label: Text(
                  'Learn More',
                  style: GoogleFonts.montserrat(),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.amber[700],
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

