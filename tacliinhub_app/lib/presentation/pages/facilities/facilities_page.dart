import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class FacilitiesPage extends StatelessWidget {
  const FacilitiesPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Facilities',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.teal,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: GridView.count(
          crossAxisCount: 2,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
          children: [
            _buildFeatureCard(
              context,
              title: 'Hostels',
              icon: Icons.hotel,
              color: Colors.blue,
              onTap: () => Navigator.pushNamed(context, '/facilities/hostels'),
            ),
            _buildFeatureCard(
              context,
              title: 'Hostel Rooms',
              icon: Icons.room,
              color: Colors.indigo,
              onTap: () => Navigator.pushNamed(context, '/facilities/hostel-rooms'),
            ),
            _buildFeatureCard(
              context,
              title: 'Hostel Allocations',
              icon: Icons.assignment_ind,
              color: Colors.purple,
              onTap: () => Navigator.pushNamed(context, '/facilities/hostel-allocations'),
            ),
            _buildFeatureCard(
              context,
              title: 'Transport Routes',
              icon: Icons.route,
              color: Colors.green,
              onTap: () => Navigator.pushNamed(context, '/facilities/transport-routes'),
            ),
            _buildFeatureCard(
              context,
              title: 'Vehicles',
              icon: Icons.directions_bus,
              color: Colors.orange,
              onTap: () => Navigator.pushNamed(context, '/facilities/vehicles'),
            ),
            _buildFeatureCard(
              context,
              title: 'Transport Assignments',
              icon: Icons.assignment,
              color: Colors.red,
              onTap: () => Navigator.pushNamed(context, '/facilities/transport-assignments'),
            ),
            _buildFeatureCard(
              context,
              title: 'Vehicle Maintenance',
              icon: Icons.build,
              color: Colors.brown,
              onTap: () => Navigator.pushNamed(context, '/facilities/vehicle-maintenance'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFeatureCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                color,
                color.withOpacity(0.7),
              ],
            ),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                icon,
                size: 48,
                color: Colors.white,
              ),
              const SizedBox(height: 12),
              Text(
                title,
                style: GoogleFonts.montserrat(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
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

