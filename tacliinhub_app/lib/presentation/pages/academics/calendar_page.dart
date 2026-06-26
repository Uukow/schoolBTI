import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/academic_provider.dart';
import 'add_calendar_event_page.dart';

class CalendarPage extends StatefulWidget {
  const CalendarPage({super.key});

  @override
  State<CalendarPage> createState() => _CalendarPageState();
}

class _CalendarPageState extends State<CalendarPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final now = DateTime.now();
      context.read<AcademicProvider>().loadAcademicCalendar(
        startDate: DateTime(now.year, 1, 1),
        endDate: DateTime(now.year, 12, 31),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Academic Calendar',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.red,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const AddCalendarEventPage()),
              );
              if (result == true) {
                final now = DateTime.now();
                context.read<AcademicProvider>().loadAcademicCalendar(
                  startDate: DateTime(now.year, 1, 1),
                  endDate: DateTime(now.year, 12, 31),
                );
              }
            },
            tooltip: 'Add Calendar Event',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const AddCalendarEventPage()),
          );
          if (result == true) {
            final now = DateTime.now();
            context.read<AcademicProvider>().loadAcademicCalendar(
              startDate: DateTime(now.year, 1, 1),
              endDate: DateTime(now.year, 12, 31),
            );
          }
        },
        backgroundColor: Colors.red,
        tooltip: 'Add Calendar Event',
        child: const Icon(Icons.add),
      ),
      body: Consumer<AcademicProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading calendar'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      final now = DateTime.now();
                      provider.loadAcademicCalendar(
                        startDate: DateTime(now.year, 1, 1),
                        endDate: DateTime(now.year, 12, 31),
                      );
                    },
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.calendarEvents.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.calendar_today, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No calendar events found',
                    style: GoogleFonts.montserrat(
                      fontSize: 18,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            );
          }

          // Group by month
          final eventsByMonth = <String, List>{};
          for (var event in provider.calendarEvents) {
            final monthKey = DateFormat('MMMM yyyy').format(event.startDate);
            if (!eventsByMonth.containsKey(monthKey)) {
              eventsByMonth[monthKey] = [];
            }
            eventsByMonth[monthKey]!.add(event);
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: eventsByMonth.length,
            itemBuilder: (context, index) {
              final month = eventsByMonth.keys.elementAt(index);
              final events = eventsByMonth[month]!;

              return Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Text(
                      month,
                      style: GoogleFonts.montserrat(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.red[700],
                      ),
                    ),
                  ),
                  ...events.map((event) {
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                        side: BorderSide(
                          color: event.isHoliday
                              ? Colors.red
                              : Colors.transparent,
                          width: 2,
                        ),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: event.isHoliday
                              ? Colors.red.withOpacity(0.1)
                              : Colors.red.withOpacity(0.1),
                          child: Icon(
                            event.isHoliday ? Icons.beach_access : Icons.event,
                            color: event.isHoliday
                                ? Colors.red[700]
                                : Colors.red[700],
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
                            Text(
                              '${DateFormat('MMM dd').format(event.startDate)} - ${DateFormat('MMM dd, yyyy').format(event.endDate)}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            if (event.description != null)
                              Text(
                                event.description!,
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            if (event.isHoliday)
                              Container(
                                margin: const EdgeInsets.only(top: 4),
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.red.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  'Holiday',
                                  style: GoogleFonts.montserrat(
                                    fontSize: 10,
                                    color: Colors.red[700],
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                          ],
                        ),
                      ),
                    );
                  }),
                  const SizedBox(height: 24),
                ],
              );
            },
          );
        },
      ),
    );
  }
}
