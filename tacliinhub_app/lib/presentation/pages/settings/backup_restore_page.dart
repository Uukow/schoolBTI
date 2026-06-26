import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/settings_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class BackupRestorePage extends StatefulWidget {
  const BackupRestorePage({super.key});

  @override
  State<BackupRestorePage> createState() => _BackupRestorePageState();
}

class _BackupRestorePageState extends State<BackupRestorePage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadBackups();
    });
  }

  void _loadBackups() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<SettingsProvider>();
    provider.loadBackups(userId: user?.id);
  }

  Future<void> _createBackup() async {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'User not logged in',
      );
      return;
    }

    // Show confirmation dialog
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Create Backup'),
        content: const Text('Are you sure you want to create a new backup?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Create'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    final provider = context.read<SettingsProvider>();
    final success = await provider.createBackup(userId: user.id);

    if (mounted) {
      if (success) {
        SweetAlert.showSuccess(
          context: context,
          title: 'Success',
          message: 'Backup created successfully',
        );
        _loadBackups();
      } else {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to create backup',
        );
      }
    }
  }

  String _formatFileSize(int bytes) {
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(2)} KB';
    return '${(bytes / (1024 * 1024)).toStringAsFixed(2)} MB';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Backup & Restore',
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
            onPressed: _createBackup,
            tooltip: 'Create Backup',
          ),
        ],
      ),
      body: Consumer<SettingsProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.backups.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.backups.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading backups'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadBackups,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.backups.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.backup_outlined, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No backups found',
                    style: GoogleFonts.montserrat(
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton.icon(
                    onPressed: _createBackup,
                    icon: const Icon(Icons.add),
                    label: const Text('Create Backup'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.teal,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                    ),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () async => _loadBackups(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.backups.length,
              itemBuilder: (context, index) {
                final backup = provider.backups[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  elevation: 2,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: Colors.teal.withOpacity(0.1),
                      child: Icon(Icons.backup, color: Colors.teal),
                    ),
                    title: Text(
                      backup.fileName,
                      style: GoogleFonts.montserrat(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Size: ${_formatFileSize(backup.fileSize)}'),
                        Text(
                          'Created: ${DateFormat('MMM d, yyyy HH:mm').format(backup.createdAt)}',
                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                        ),
                      ],
                    ),
                    trailing: IconButton(
                      icon: const Icon(Icons.download),
                      onPressed: () {
                        // TODO: Implement download
                        SweetAlert.showInfo(
                          context: context,
                          title: 'Download',
                          message: 'Download functionality coming soon',
                        );
                      },
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _createBackup,
        backgroundColor: Colors.teal,
        child: const Icon(Icons.add),
      ),
    );
  }
}

