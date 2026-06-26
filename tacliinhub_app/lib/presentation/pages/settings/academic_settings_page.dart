import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/settings_provider.dart';
import '../../providers/auth_provider.dart';

class AcademicSettingsPage extends StatefulWidget {
  const AcademicSettingsPage({super.key});

  @override
  State<AcademicSettingsPage> createState() => _AcademicSettingsPageState();
}

class _AcademicSettingsPageState extends State<AcademicSettingsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadSettings();
    });
  }

  void _loadSettings() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<SettingsProvider>();
    provider.loadAcademicSettings(userId: user?.id);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Academic Settings',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
        elevation: 0,
      ),
      body: Consumer<SettingsProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.academicSettings == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.academicSettings == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading settings'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadSettings,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          final currentSession = provider.academicSettings?['current_session_details'];
          final sessions = provider.academicSettings?['sessions'] ?? [];

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (currentSession != null) ...[
                  Card(
                    elevation: 2,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Current Academic Session',
                            style: GoogleFonts.montserrat(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            'Session: ${currentSession['session_name'] ?? 'N/A'}',
                            style: GoogleFonts.montserrat(),
                          ),
                          Text(
                            'Start Date: ${currentSession['start_date'] ?? 'N/A'}',
                            style: GoogleFonts.montserrat(),
                          ),
                          Text(
                            'End Date: ${currentSession['end_date'] ?? 'N/A'}',
                            style: GoogleFonts.montserrat(),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
                Text(
                  'Academic Sessions',
                  style: GoogleFonts.montserrat(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
                if (sessions.isEmpty)
                  Center(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Column(
                        children: [
                          Icon(Icons.school_outlined, size: 64, color: Colors.grey[400]),
                          const SizedBox(height: 16),
                          Text(
                            'No academic sessions found',
                            style: GoogleFonts.montserrat(
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  )
                else
                  ...sessions.map<Widget>((session) {
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: (session['is_active'] == 1 || session['is_active'] == true)
                              ? Colors.green.withOpacity(0.1)
                              : Colors.grey.withOpacity(0.1),
                          child: Icon(
                            Icons.calendar_today,
                            color: (session['is_active'] == 1 || session['is_active'] == true)
                                ? Colors.green
                                : Colors.grey,
                          ),
                        ),
                        title: Text(
                          session['session_name'] ?? 'Unnamed Session',
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Text(
                          '${session['start_date'] ?? 'N/A'} - ${session['end_date'] ?? 'N/A'}',
                          style: GoogleFonts.montserrat(),
                        ),
                        trailing: (session['is_active'] == 1 || session['is_active'] == true)
                            ? Chip(
                                label: const Text('Active'),
                                backgroundColor: Colors.green.withOpacity(0.1),
                              )
                            : null,
                      ),
                    );
                  }),
              ],
            ),
          );
        },
      ),
    );
  }
}

