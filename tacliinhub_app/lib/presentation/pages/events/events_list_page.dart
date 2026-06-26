import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/events_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';
import '../../../data/models/events_models.dart' as events_models;
import 'add_event_page.dart';

class EventsListPage extends StatefulWidget {
  const EventsListPage({super.key});

  @override
  State<EventsListPage> createState() => _EventsListPageState();
}

class _EventsListPageState extends State<EventsListPage> {
  String? _selectedEventType;
  String? _selectedStatus;
  String? _selectedAudience;
  bool _isInitialLoad = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadEvents();
    });
  }

  Future<void> _loadEvents() async {
    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user?.id == null) {
        if (mounted) {
          SweetAlert.showError(
            context: context,
            title: 'Authentication Error',
            message: 'Please login to view events',
          );
        }
        return;
      }

      final provider = context.read<EventsProvider>();
      await provider.loadEvents(
        userId: user!.id,
        eventType: _selectedEventType,
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
          message: 'Failed to load events: ${e.toString()}',
        );
      }
    }
  }

  void _handleFilterChange() {
    setState(() {
      _isInitialLoad = true;
    });
    _loadEvents();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Events List',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.indigo,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              setState(() {
                _isInitialLoad = true;
              });
              _loadEvents();
            },
            tooltip: 'Refresh',
          ),
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const AddEventPage()),
              );
              if (result == true && mounted) {
                _loadEvents();
              }
            },
            tooltip: 'Add Event',
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
                    labelText: 'Event Type',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                  ),
                  initialValue: _selectedEventType,
                  items: const [
                    DropdownMenuItem<String>(
                      value: null,
                      child: Text('All Types'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Academic',
                      child: Text('Academic'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Holiday',
                      child: Text('Holiday'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Exam',
                      child: Text('Exam'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Activity',
                      child: Text('Activity'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Meeting',
                      child: Text('Meeting'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Other',
                      child: Text('Other'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedEventType = value;
                    });
                    _handleFilterChange();
                  },
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
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
                            value: 'Scheduled',
                            child: Text('Scheduled'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'Ongoing',
                            child: Text('Ongoing'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'Completed',
                            child: Text('Completed'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedStatus = value;
                          });
                          _handleFilterChange();
                        },
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: 'Audience',
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
                          DropdownMenuItem<String>(
                            value: 'All',
                            child: Text('All'),
                          ),
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
                    ),
                  ],
                ),
              ],
            ),
          ),
          // Events List
          Expanded(
            child: Consumer<EventsProvider>(
              builder: (context, provider, child) {
                if (_isInitialLoad && provider.isLoading) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const CircularProgressIndicator(),
                        const SizedBox(height: 16),
                        Text(
                          'Loading events...',
                          style: GoogleFonts.montserrat(
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.error != null && !provider.isLoading) {
                  return _buildErrorState(provider.error!, _loadEvents);
                }

                if (provider.events.isEmpty && !provider.isLoading) {
                  return _buildEmptyState();
                }

                // Group events by date
                final groupedEvents = <String, List<events_models.Event>>{};
                for (var event in provider.events) {
                  final dateKey = DateFormat(
                    'yyyy-MM-dd',
                  ).format(event.startDate);
                  groupedEvents.putIfAbsent(dateKey, () => []).add(event);
                }

                final sortedDates = groupedEvents.keys.toList()..sort();

                return RefreshIndicator(
                  onRefresh: () => _loadEvents(),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: sortedDates.length,
                    itemBuilder: (context, index) {
                      final dateKey = sortedDates[index];
                      final date = DateTime.parse(dateKey);
                      final events = groupedEvents[dateKey]!;
                      return _buildDateSection(date, events);
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
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const AddEventPage()),
          );
          if (result == true && mounted) {
            _loadEvents();
          }
        },
        backgroundColor: Colors.indigo,
        icon: const Icon(Icons.add),
        label: Text(
          'Add Event',
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
              'Failed to Load Events',
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
                backgroundColor: Colors.indigo,
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
                color: Colors.indigo.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.event_busy,
                size: 64,
                color: Colors.indigo[700],
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'No Events Found',
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Get started by creating your first event',
              textAlign: TextAlign.center,
              style: GoogleFonts.montserrat(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () async {
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const AddEventPage()),
                );
                if (result == true && mounted) {
                  _loadEvents();
                }
              },
              icon: const Icon(Icons.add),
              label: const Text('Create Event'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.indigo,
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

  Widget _buildDateSection(DateTime date, List<events_models.Event> events) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 12),
          child: Row(
            children: [
              Container(
                width: 4,
                height: 20,
                decoration: BoxDecoration(
                  color: Colors.indigo,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 12),
              Text(
                DateFormat('EEEE, MMMM d, yyyy').format(date),
                style: GoogleFonts.montserrat(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey[800],
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.indigo.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '${events.length}',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: Colors.indigo[700],
                  ),
                ),
              ),
            ],
          ),
        ),
        ...events.map((event) => _buildEventCard(event)),
        const SizedBox(height: 8),
      ],
    );
  }

  Widget _buildEventCard(events_models.Event event) {
    Color eventColor = _getEventColor(event.eventType);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: eventColor.withOpacity(0.3), width: 2),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.all(16),
        leading: Container(
          width: 4,
          decoration: BoxDecoration(
            color: eventColor,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        title: Text(
          event.title,
          style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            if (event.location != null) ...[
              Row(
                children: [
                  Icon(Icons.location_on, size: 14, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      event.location!,
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
            ],
            Row(
              children: [
                Icon(Icons.access_time, size: 14, color: Colors.grey[600]),
                const SizedBox(width: 4),
                Text(
                  event.displayDate,
                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                ),
              ],
            ),
          ],
        ),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Chip(
              label: Text(
                event.eventType,
                style: const TextStyle(fontSize: 10),
              ),
              backgroundColor: eventColor.withOpacity(0.1),
              labelStyle: TextStyle(color: eventColor),
              padding: EdgeInsets.zero,
            ),
            const SizedBox(height: 4),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: _getStatusColor(event.status).withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                event.status,
                style: TextStyle(
                  fontSize: 10,
                  color: _getStatusColor(event.status),
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        ),
        onTap: () {
          _showEventDetails(event);
        },
      ),
    );
  }

  Color _getEventColor(String eventType) {
    switch (eventType) {
      case 'Academic':
        return Colors.blue;
      case 'Holiday':
        return Colors.red;
      case 'Exam':
        return Colors.orange;
      case 'Activity':
        return Colors.green;
      case 'Meeting':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Scheduled':
        return Colors.blue;
      case 'Ongoing':
        return Colors.orange;
      case 'Completed':
        return Colors.green;
      case 'Cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  void _showEventDetails(events_models.Event event) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Container(
              width: 4,
              height: 24,
              decoration: BoxDecoration(
                color: _getEventColor(event.eventType),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                event.title,
                style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildDetailRow(
                Icons.description,
                'Description',
                event.description,
              ),
              const SizedBox(height: 12),
              _buildDetailRow(Icons.calendar_today, 'Date', event.displayDate),
              if (event.location != null) ...[
                const SizedBox(height: 12),
                _buildDetailRow(Icons.location_on, 'Location', event.location!),
              ],
              const SizedBox(height: 12),
              _buildDetailRow(Icons.category, 'Type', event.eventType),
              const SizedBox(height: 12),
              _buildDetailRow(Icons.flag, 'Status', event.status),
              if (event.targetAudience != null) ...[
                const SizedBox(height: 12),
                _buildDetailRow(
                  Icons.people,
                  'Audience',
                  event.targetAudience!,
                ),
              ],
            ],
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

  Widget _buildDetailRow(IconData icon, String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: Colors.grey[600]),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 2),
              Text(value, style: GoogleFonts.montserrat()),
            ],
          ),
        ),
      ],
    );
  }
}
