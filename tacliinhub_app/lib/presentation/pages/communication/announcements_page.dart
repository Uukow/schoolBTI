import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/communication_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AnnouncementsPage extends StatefulWidget {
  const AnnouncementsPage({super.key});

  @override
  State<AnnouncementsPage> createState() => _AnnouncementsPageState();
}

class _AnnouncementsPageState extends State<AnnouncementsPage> {
  String? _selectedStatus;
  String? _selectedAudience;
  bool _isInitialLoad = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadAnnouncements(showLoading: true);
    });
  }

  Future<void> _loadAnnouncements({bool showLoading = false}) async {
    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user?.id == null) {
        if (mounted) {
          SweetAlert.showError(
            context: context,
            title: 'Authentication Error',
            message: 'Please login to view announcements',
          );
        }
        return;
      }

      final provider = context.read<CommunicationProvider>();
      await provider.loadAnnouncements(
        userId: user?.id,
        status: _selectedStatus,
        targetAudience: _selectedAudience,
      );

      if (mounted) {
        setState(() {
          _isInitialLoad = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isInitialLoad = false;
        });
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to load announcements: ${e.toString()}',
        );
      }
    }
  }

  void _handleFilterChange() {
    setState(() {
      _isInitialLoad = true;
    });
    _loadAnnouncements(showLoading: true);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Announcements',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => _loadAnnouncements(showLoading: true),
            tooltip: 'Refresh',
          ),
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () async {
              final result = await Navigator.pushNamed(
                context,
                '/communication/announcements/add',
              );
              if (result == true && mounted) {
                _loadAnnouncements(showLoading: false);
              }
            },
            tooltip: 'Add Announcement',
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
                      value: 'Published',
                      child: Text('Published'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Draft',
                      child: Text('Draft'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                    _handleFilterChange();
                  },
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  decoration: InputDecoration(
                    labelText: 'Target Audience',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                  ),
                  initialValue: _selectedAudience,
                  items: const [
                    DropdownMenuItem<String>(
                      value: null,
                      child: Text('All Audiences'),
                    ),
                    DropdownMenuItem<String>(value: 'All', child: Text('All')),
                    DropdownMenuItem<String>(
                      value: 'Students',
                      child: Text('Students'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Teachers',
                      child: Text('Teachers'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Parents',
                      child: Text('Parents'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedAudience = value;
                    });
                    _handleFilterChange();
                  },
                ),
              ],
            ),
          ),
          // Announcements List
          Expanded(
            child: Consumer<CommunicationProvider>(
              builder: (context, provider, child) {
                // Initial loading state
                if (_isInitialLoad && provider.isLoading) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const CircularProgressIndicator(),
                        const SizedBox(height: 16),
                        Text(
                          'Loading announcements...',
                          style: GoogleFonts.montserrat(
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                // Error state with retry
                if (provider.error != null && !provider.isLoading) {
                  return _buildErrorState(provider.error!, _loadAnnouncements);
                }

                // Empty state
                if (provider.announcements.isEmpty && !provider.isLoading) {
                  return _buildEmptyState();
                }

                // Success state with data
                return RefreshIndicator(
                  onRefresh: () => _loadAnnouncements(showLoading: false),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: provider.announcements.length,
                    itemBuilder: (context, index) {
                      final announcement = provider.announcements[index];
                      return _buildAnnouncementCard(announcement);
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final result = await Navigator.pushNamed(
            context,
            '/communication/announcements/add',
          );
          if (result == true && mounted) {
            _loadAnnouncements(showLoading: false);
          }
        },
        backgroundColor: Colors.orange,
        icon: const Icon(Icons.add),
        label: Text(
          'Add',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  Widget _buildErrorState(String error, VoidCallback onRetry) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.red.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.error_outline,
                size: 64,
                color: Colors.red[700],
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Oops! Something went wrong',
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
              ),
            ),
            const SizedBox(height: 12),
            Text(
              error,
              textAlign: TextAlign.center,
              style: GoogleFonts.montserrat(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.orange,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.orange.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.campaign_outlined,
                size: 64,
                color: Colors.orange[700],
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'No Announcements Found',
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Get started by creating your first announcement',
              textAlign: TextAlign.center,
              style: GoogleFonts.montserrat(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () async {
                final result = await Navigator.pushNamed(
                  context,
                  '/communication/announcements/add',
                );
                if (result == true && mounted) {
                  _loadAnnouncements(showLoading: false);
                }
              },
              icon: const Icon(Icons.add),
              label: const Text('Create Announcement'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.orange,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAnnouncementCard(announcement) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ExpansionTile(
        leading: CircleAvatar(
          backgroundColor: announcement.status == 'Published'
              ? Colors.green.withOpacity(0.1)
              : Colors.grey.withOpacity(0.1),
          child: Icon(
            Icons.campaign,
            color: announcement.status == 'Published'
                ? Colors.green
                : Colors.grey,
          ),
        ),
        title: Text(
          announcement.title,
          style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Wrap(
              spacing: 8,
              children: [
                Chip(
                  label: Text(announcement.status),
                  labelStyle: const TextStyle(fontSize: 12),
                  padding: EdgeInsets.zero,
                  backgroundColor: announcement.status == 'Published'
                      ? Colors.green.withOpacity(0.1)
                      : Colors.grey.withOpacity(0.1),
                ),
                if (announcement.targetAudience != null)
                  Chip(
                    label: Text(announcement.targetAudience!),
                    labelStyle: const TextStyle(fontSize: 12),
                    padding: EdgeInsets.zero,
                  ),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              'Created: ${DateFormat('MMM d, yyyy').format(DateTime.parse(announcement.createdAt))}',
              style: TextStyle(fontSize: 12, color: Colors.grey[600]),
            ),
          ],
        ),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(announcement.content, style: GoogleFonts.montserrat()),
                if (announcement.attachmentUrl != null) ...[
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Icon(Icons.attach_file, size: 16, color: Colors.blue),
                      const SizedBox(width: 4),
                      Text(
                        'Attachment available',
                        style: TextStyle(fontSize: 12, color: Colors.blue),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
