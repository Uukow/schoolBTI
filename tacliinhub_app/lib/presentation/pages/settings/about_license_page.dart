import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/settings_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/constants.dart';

class AboutLicensePage extends StatefulWidget {
  const AboutLicensePage({super.key});

  @override
  State<AboutLicensePage> createState() => _AboutLicensePageState();
}

class _AboutLicensePageState extends State<AboutLicensePage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<SettingsProvider>();
      if (provider.systemInfo == null) {
        provider.loadSystemInfo(userId: user?.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'About & License',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.grey[700],
        elevation: 0,
      ),
      body: Consumer<SettingsProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.systemInfo == null) {
            return const Center(child: CircularProgressIndicator());
          }

          final systemInfo = provider.systemInfo;

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Card(
                  elevation: 2,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      children: [
                        // add a logo of the school
                        Image.asset(
                          'assets/images/logo-icon.png',
                          width: 100,
                          height: 100,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          systemInfo?.appName ?? 'TacliinHub ERP System',
                          style: GoogleFonts.montserrat(
                            fontSize: 24,
                            color: AppConstants.primaryColor,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Version ${systemInfo?.appVersion ?? '1.0.0'}',
                          style: GoogleFonts.montserrat(
                            fontSize: 16,
                            color: AppConstants.secondaryColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  'System Information',
                  style: GoogleFonts.montserrat(
                    fontSize: 20,
                    color: AppConstants.primaryColor,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
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
                        _buildInfoRow(
                          'PHP Version',
                          systemInfo?.phpVersion ?? 'Unknown',
                        ),
                        const Divider(),
                        _buildInfoRow(
                          'Database Version',
                          systemInfo?.databaseVersion ?? 'Unknown',
                        ),
                        const Divider(),
                        _buildInfoRow(
                          'Server Info',
                          systemInfo?.serverInfo ?? 'Unknown',
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  'License Information',
                  style: GoogleFonts.montserrat(
                    fontSize: 20,
                    color: AppConstants.primaryColor,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
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
                        _buildInfoRow(
                          'License Type',
                          systemInfo?.licenseType ?? 'Standard',
                        ),
                        const Divider(),
                        _buildInfoRow(
                          'License Key',
                          // add a link to the license key
                          systemInfo?.licenseKey ?? 'https://uukowtech.com',
                        ),
                        if (systemInfo?.licenseExpiry != null) ...[
                          const Divider(),
                          _buildInfoRow(
                            'Expiry Date',
                            systemInfo!.licenseExpiry.toString().split(' ')[0],
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  'Copyright',
                  style: GoogleFonts.montserrat(
                    fontSize: 20,
                    color: AppConstants.primaryColor,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
                Card(
                  elevation: 2,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Text(
                      '© 2025 TacliinHub ERP System. All rights reserved.\n\n'
                      'This Application is proprietary and confidential. Unauthorized copying, '
                      'distribution, or use of this Application, via any medium, is strictly prohibited. Developed by Uukow Technology Solutions (UTech)',
                      style: GoogleFonts.montserrat(),
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: GoogleFonts.montserrat(
                fontWeight: FontWeight.w600,
                color: Colors.grey[700],
              ),
            ),
          ),
          Expanded(child: Text(value, style: GoogleFonts.montserrat())),
        ],
      ),
    );
  }
}
