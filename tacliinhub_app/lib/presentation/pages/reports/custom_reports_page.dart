import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/reports_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class CustomReportsPage extends StatefulWidget {
  const CustomReportsPage({super.key});

  @override
  State<CustomReportsPage> createState() => _CustomReportsPageState();
}

class _CustomReportsPageState extends State<CustomReportsPage> {
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadCustomReports();
    });
  }

  Future<void> _loadCustomReports() async {
    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<ReportsProvider>();
      await provider.loadCustomReports(userId: user?.id);
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to load custom reports: ${e.toString()}',
        );
      }
    }
  }

  Future<void> _executeReport(int reportId) async {
    setState(() {
      _isLoading = true;
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<ReportsProvider>();

      await provider.executeCustomReport(
        reportId: reportId,
        userId: user?.id,
      );

      if (mounted) {
        if (provider.error != null) {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to execute report',
          );
        } else {
          _showReportResult(provider.customReportResult);
        }
      }
    } catch (e) {
      if (mounted) {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to execute report: ${e.toString()}',
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _showReportResult(Map<String, dynamic>? result) {
    if (result == null) return;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Report Results'),
        content: SizedBox(
          width: double.maxFinite,
          child: SingleChildScrollView(
            child: Text(
              result.toString(),
              style: GoogleFonts.montserrat(),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Custom Reports',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.teal,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadCustomReports,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: Consumer<ReportsProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.customReports.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.customReports.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.error_outline, size: 64, color: Colors.red),
                    const SizedBox(height: 16),
                    Text(
                      provider.error ?? 'Error loading reports',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.montserrat(),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: _loadCustomReports,
                      child: const Text('Retry'),
                    ),
                  ],
                ),
              ),
            );
          }

          if (provider.customReports.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.assessment_outlined,
                        size: 64, color: Colors.grey[400]),
                    const SizedBox(height: 16),
                    Text(
                      'No custom reports available',
                      style: GoogleFonts.montserrat(
                        fontSize: 16,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Contact administrator to create custom reports',
                      style: GoogleFonts.montserrat(
                        fontSize: 14,
                        color: Colors.grey[500],
                      ),
                    ),
                  ],
                ),
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: provider.customReports.length,
            itemBuilder: (context, index) {
              final report = provider.customReports[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: Colors.teal.withOpacity(0.1),
                    child: const Icon(Icons.assessment, color: Colors.teal),
                  ),
                  title: Text(
                    report.name,
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text(report.description),
                      const SizedBox(height: 4),
                      Text(
                        'Type: ${report.reportType}',
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                    ],
                  ),
                  trailing: _isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : IconButton(
                          icon: const Icon(Icons.play_arrow),
                          onPressed: () => _executeReport(report.id),
                          tooltip: 'Execute Report',
                        ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}

