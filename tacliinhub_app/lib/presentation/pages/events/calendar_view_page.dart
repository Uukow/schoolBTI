import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/events_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';
import '../../../data/models/events_models.dart' as events_models;
import 'add_event_page.dart';

class CalendarViewPage extends StatefulWidget {
  const CalendarViewPage({super.key});

  @override
  State<CalendarViewPage> createState() => _CalendarViewPageState();
}

class _CalendarViewPageState extends State<CalendarViewPage> {
  DateTime _selectedDate = DateTime.now();
  DateTime _focusedDate = DateTime.now();
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
      // Load events for current month
      final firstDay = DateTime(_focusedDate.year, _focusedDate.month, 1);
      final lastDay = DateTime(_focusedDate.year, _focusedDate.month + 1, 0);

      await provider.loadEvents(
        userId: user!.id,
        startDate: firstDay,
        endDate: lastDay,
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

  void _onMonthChanged(DateTime focusedDate) {
    setState(() {
      _focusedDate = focusedDate;
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
          'Calendar View',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
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
        ],
      ),
      body: Column(
        children: [
          // Calendar Widget
          SizedBox(
            height: 450,
            child: Container(
              margin: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey.withOpacity(0.1),
                    spreadRadius: 1,
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: _buildCalendar(),
            ),
          ),
          // Events for selected date
          Expanded(
            child: _buildEventsList(),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => AddEventPage(initialDate: _selectedDate),
            ),
          );
          if (result == true && mounted) {
            _loadEvents();
          }
        },
        backgroundColor: Colors.purple,
        icon: const Icon(Icons.add),
        label: Text(
          'Add Event',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  Widget _buildCalendar() {
    return Consumer<EventsProvider>(
      builder: (context, provider, child) {
        return TableCalendar(
          firstDay: DateTime.utc(2020, 1, 1),
          lastDay: DateTime.utc(2030, 12, 31),
          focusedDay: _focusedDate,
          selectedDayPredicate: (day) => isSameDay(_selectedDate, day),
          onDaySelected: (selectedDay, focusedDay) {
            setState(() {
              _selectedDate = selectedDay;
              _focusedDate = focusedDay;
            });
          },
          onPageChanged: _onMonthChanged,
          calendarStyle: CalendarStyle(
            todayDecoration: BoxDecoration(
              color: Colors.purple.withOpacity(0.3),
              shape: BoxShape.circle,
            ),
            selectedDecoration: BoxDecoration(
              color: Colors.purple,
              shape: BoxShape.circle,
            ),
            markerDecoration: BoxDecoration(
              color: Colors.purple[700],
              shape: BoxShape.circle,
            ),
            outsideDaysVisible: false,
          ),
          headerStyle: HeaderStyle(
            formatButtonVisible: false,
            titleCentered: true,
            titleTextStyle: GoogleFonts.montserrat(
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          eventLoader: (day) {
            return provider.getEventsForDate(day);
          },
          calendarBuilders: CalendarBuilders(
            markerBuilder: (context, date, events) {
              if (events.isNotEmpty) {
                return Positioned(
                  bottom: 1,
                  child: Container(
                    width: 6,
                    height: 6,
                    decoration: BoxDecoration(
                      color: Colors.purple[700],
                      shape: BoxShape.circle,
                    ),
                  ),
                );
              }
              return const SizedBox.shrink();
            },
          ),
        );
      },
    );
  }

  Widget _buildEventsList() {
    return Consumer<EventsProvider>(
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

        final dayEvents = provider.getEventsForDate(_selectedDate);

        if (dayEvents.isEmpty) {
          return _buildEmptyState();
        }

        return ListView.builder(
          padding: const EdgeInsets.all(16),
          itemCount: dayEvents.length,
          itemBuilder: (context, index) {
            final event = dayEvents[index];
            return _buildEventCard(event);
          },
        );
      },
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
              child: Icon(Icons.error_outline, size: 64, color: Colors.red[700]),
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
                backgroundColor: Colors.purple,
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
                color: Colors.purple.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.event_busy,
                size: 64,
                color: Colors.purple[700],
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'No Events on ${DateFormat('MMM d, yyyy').format(_selectedDate)}',
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Tap the + button to add an event',
              textAlign: TextAlign.center,
              style: GoogleFonts.montserrat(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
      ),
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
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
          ),
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
                  Text(
                    event.location!,
                    style: TextStyle(fontSize: 12, color: Colors.grey[600]),
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
        trailing: Chip(
          label: Text(
            event.eventType,
            style: const TextStyle(fontSize: 10),
          ),
          backgroundColor: eventColor.withOpacity(0.1),
          labelStyle: TextStyle(color: eventColor),
          padding: EdgeInsets.zero,
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

  void _showEventDetails(events_models.Event event) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
        ),
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
              _buildDetailRow(Icons.description, 'Description', event.description),
              const SizedBox(height: 12),
              _buildDetailRow(Icons.calendar_today, 'Date', event.displayDate),
              if (event.location != null) ...[
                const SizedBox(height: 12),
                _buildDetailRow(Icons.location_on, 'Location', event.location!),
              ],
              const SizedBox(height: 12),
              _buildDetailRow(Icons.category, 'Type', event.eventType),
              if (event.targetAudience != null) ...[
                const SizedBox(height: 12),
                _buildDetailRow(Icons.people, 'Audience', event.targetAudience!),
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
              Text(
                value,
                style: GoogleFonts.montserrat(),
              ),
            ],
          ),
        ),
      ],
    );
  }

  bool isSameDay(DateTime a, DateTime b) {
    return a.year == b.year && a.month == b.month && a.day == b.day;
  }
}

// Simple TableCalendar implementation
class TableCalendar extends StatefulWidget {
  final DateTime firstDay;
  final DateTime lastDay;
  final DateTime focusedDay;
  final DateTime? selectedDay;
  final bool Function(DateTime)? selectedDayPredicate;
  final void Function(DateTime, DateTime)? onDaySelected;
  final void Function(DateTime)? onPageChanged;
  final CalendarStyle? calendarStyle;
  final HeaderStyle? headerStyle;
  final List<dynamic> Function(DateTime)? eventLoader;
  final CalendarBuilders? calendarBuilders;

  const TableCalendar({
    super.key,
    required this.firstDay,
    required this.lastDay,
    required this.focusedDay,
    this.selectedDay,
    this.selectedDayPredicate,
    this.onDaySelected,
    this.onPageChanged,
    this.calendarStyle,
    this.headerStyle,
    this.eventLoader,
    this.calendarBuilders,
  });

  @override
  State<TableCalendar> createState() => _TableCalendarState();
}

class _TableCalendarState extends State<TableCalendar> {
  late DateTime _focusedDay;
  late PageController _pageController;

  @override
  void initState() {
    super.initState();
    _focusedDay = widget.focusedDay;
    _pageController = PageController(
      initialPage: _getPageForDate(_focusedDay),
    );
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  int _getPageForDate(DateTime date) {
    return (date.year - widget.firstDay.year) * 12 +
        (date.month - widget.firstDay.month);
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 400,
      child: Column(
        children: [
          _buildHeader(),
          Expanded(
            child: _buildCalendarGrid(),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          IconButton(
            icon: const Icon(Icons.chevron_left),
            onPressed: () {
              _pageController.previousPage(
                duration: const Duration(milliseconds: 300),
                curve: Curves.easeOut,
              );
            },
          ),
          Text(
            DateFormat('MMMM yyyy').format(_focusedDay),
            style: GoogleFonts.montserrat(
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          IconButton(
            icon: const Icon(Icons.chevron_right),
            onPressed: () {
              _pageController.nextPage(
                duration: const Duration(milliseconds: 300),
                curve: Curves.easeOut,
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildCalendarGrid() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildWeekdayHeaders(),
          const SizedBox(height: 8),
          _buildCalendarDays(),
        ],
      ),
    );
  }

  Widget _buildWeekdayHeaders() {
    final weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    return Row(
      children: weekdays.map((day) {
        return Expanded(
          child: Center(
            child: Text(
              day,
              style: GoogleFonts.montserrat(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: Colors.grey[600],
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildCalendarDays() {
    final firstDayOfMonth = DateTime(_focusedDay.year, _focusedDay.month, 1);
    final lastDayOfMonth = DateTime(_focusedDay.year, _focusedDay.month + 1, 0);
    final firstWeekday = firstDayOfMonth.weekday == 7 ? 0 : firstDayOfMonth.weekday;
    final daysInMonth = lastDayOfMonth.day;

    return PageView.builder(
      controller: _pageController,
      onPageChanged: (page) {
        final newDate = DateTime(
          widget.firstDay.year,
          widget.firstDay.month + page,
        );
        setState(() {
          _focusedDay = newDate;
        });
        widget.onPageChanged?.call(newDate);
      },
      itemBuilder: (context, page) {
        final monthDate = DateTime(
          widget.firstDay.year,
          widget.firstDay.month + page,
        );
        return _buildMonthGrid(monthDate);
      },
    );
  }

  Widget _buildMonthGrid(DateTime month) {
    final firstDayOfMonth = DateTime(month.year, month.month, 1);
    final lastDayOfMonth = DateTime(month.year, month.month + 1, 0);
    final firstWeekday = firstDayOfMonth.weekday == 7 ? 0 : firstDayOfMonth.weekday;
    final daysInMonth = lastDayOfMonth.day;
    final today = DateTime.now();
    
    // Use firstWeekday and daysInMonth in the loop below

    return Column(
      children: [
        for (int week = 0; week < 6; week++)
          Row(
            children: [
              for (int day = 0; day < 7; day++)
                Expanded(
                  child: _buildDayCell(week, day, firstWeekday, daysInMonth, month, today),
                ),
            ],
          ),
      ],
    );
    // Note: firstWeekday and daysInMonth are used in _buildDayCell method
  }

  Widget _buildDayCell(int week, int day, int firstWeekday, int daysInMonth, DateTime month, DateTime today) {
    final dayNumber = week * 7 + day - firstWeekday + 1;
    if (dayNumber < 1 || dayNumber > daysInMonth) {
      return const SizedBox(height: 40);
    }

    final date = DateTime(month.year, month.month, dayNumber);
    final isToday = date.year == today.year &&
        date.month == today.month &&
        date.day == today.day;
    final isSelected = widget.selectedDayPredicate?.call(date) ?? false;
    final events = widget.eventLoader?.call(date) ?? [];

    return GestureDetector(
      onTap: () {
        widget.onDaySelected?.call(date, date);
      },
      child: Container(
        height: 40,
        margin: const EdgeInsets.all(2),
        decoration: BoxDecoration(
          color: isSelected
              ? (widget.calendarStyle?.selectedDecoration?.color ?? Colors.purple)
              : isToday
                  ? (widget.calendarStyle?.todayDecoration?.color ?? Colors.purple.withOpacity(0.3))
                  : Colors.transparent,
          shape: BoxShape.circle,
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            Text(
              dayNumber.toString(),
              style: GoogleFonts.montserrat(
                color: isSelected
                    ? Colors.white
                    : isToday
                        ? Colors.purple[700]
                        : Colors.grey[800],
                fontWeight: isSelected || isToday ? FontWeight.bold : FontWeight.normal,
              ),
            ),
            if (events.isNotEmpty)
              Positioned(
                bottom: 2,
                child: Container(
                  width: 6,
                  height: 6,
                  decoration: BoxDecoration(
                    color: widget.calendarStyle?.markerDecoration?.color ?? Colors.purple[700],
                    shape: BoxShape.circle,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class CalendarStyle {
  final BoxDecoration? todayDecoration;
  final BoxDecoration? selectedDecoration;
  final BoxDecoration? markerDecoration;
  final bool outsideDaysVisible;

  CalendarStyle({
    this.todayDecoration,
    this.selectedDecoration,
    this.markerDecoration,
    this.outsideDaysVisible = false,
  });
}

class HeaderStyle {
  final bool formatButtonVisible;
  final bool titleCentered;
  final TextStyle? titleTextStyle;

  HeaderStyle({
    this.formatButtonVisible = false,
    this.titleCentered = true,
    this.titleTextStyle,
  });
}

class CalendarBuilders {
  final Widget Function(BuildContext, DateTime, List<dynamic>)? markerBuilder;

  CalendarBuilders({
    this.markerBuilder,
  });
}

