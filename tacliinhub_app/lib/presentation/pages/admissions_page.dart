import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../widgets/branch_selector.dart';

class AdmissionsPage extends StatelessWidget {
  const AdmissionsPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Admissions Management',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF6D28D9),
        elevation: 0,
      ),
      body: Column(
        children: [
          // Branch Selector (for Super Admin)
          const BranchSelector(),
          // Admissions Features Grid
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: GridView.count(
                crossAxisCount: 2,
                crossAxisSpacing: 16,
                mainAxisSpacing: 16,
                children: [
                  _buildModuleCard(
                    context,
                    icon: Icons.list_alt,
                    title: 'All Applications',
                    subtitle: 'View all admission applications',
                    color: const Color(0xFF6D28D9),
                    onTap: () {
                      Navigator.pushNamed(context, '/admissions/applications');
                    },
                  ),
                  _buildModuleCard(
                    context,
                    icon: Icons.pending_actions,
                    title: 'Pending Review',
                    subtitle: 'Applications awaiting review',
                    color: const Color(0xFFFF9E02),
                    onTap: () {
                      Navigator.pushNamed(context, '/admissions/pending');
                    },
                  ),
                  _buildModuleCard(
                    context,
                    icon: Icons.check_circle,
                    title: 'Approved',
                    subtitle: 'Approved applications',
                    color: Colors.green,
                    onTap: () {
                      Navigator.pushNamed(context, '/admissions/approved');
                    },
                  ),
                  _buildModuleCard(
                    context,
                    icon: Icons.add_circle,
                    title: 'New Application',
                    subtitle: 'Register new admission',
                    color: const Color(0xFFFF9E02),
                    onTap: () {
                      Navigator.pushNamed(context, '/admissions/add');
                    },
                  ),
                  _buildModuleCard(
                    context,
                    icon: Icons.assessment,
                    title: 'Statistics',
                    subtitle: 'View admission reports',
                    color: Colors.blue,
                    onTap: () {
                      Navigator.pushNamed(context, '/admissions/statistics');
                    },
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildModuleCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                color.withOpacity(0.1),
                color.withOpacity(0.05),
              ],
            ),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  size: 40,
                  color: color,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                title,
                style: GoogleFonts.montserrat(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey[800],
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 4),
              Text(
                subtitle,
                style: GoogleFonts.montserrat(
                  fontSize: 12,
                  color: Colors.grey[600],
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

