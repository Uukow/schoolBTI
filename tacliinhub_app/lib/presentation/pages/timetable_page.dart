import 'package:flutter/material.dart';
import '../../core/constants.dart';

class TimetablePage extends StatefulWidget {
  const TimetablePage({super.key});

  @override
  State<TimetablePage> createState() => _TimetablePageState();
}

class _TimetablePageState extends State<TimetablePage> {
  int _selectedDay = DateTime.now().weekday - 1;

  final List<String> _days = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ];

  final Map<String, List<Map<String, String>>> _timetable = {
    'Monday': [
      {
        'subject': 'Mathematics',
        'teacher': 'Dr. Ahmed Hassan',
        'time': '8:00 - 9:30',
        'room': 'Room 201',
      },
      {
        'subject': 'English Literature',
        'teacher': 'Ms. Sarah Johnson',
        'time': '9:45 - 11:15',
        'room': 'Room 105',
      },
      {
        'subject': 'Physics',
        'teacher': 'Prof. Mohamed Ali',
        'time': '11:30 - 1:00',
        'room': 'Lab 302',
      },
      {
        'subject': 'Lunch Break',
        'teacher': '',
        'time': '1:00 - 2:00',
        'room': '',
      },
      {
        'subject': 'Computer Science',
        'teacher': 'Mr. Omar Farah',
        'time': '2:00 - 3:30',
        'room': 'Computer Lab',
      },
    ],
    'Tuesday': [
      {
        'subject': 'Chemistry',
        'teacher': 'Dr. Fatima Osman',
        'time': '8:00 - 9:30',
        'room': 'Lab 301',
      },
      {
        'subject': 'Arabic',
        'teacher': 'Prof. Abdullah',
        'time': '9:45 - 11:15',
        'room': 'Room 103',
      },
      {
        'subject': 'Biology',
        'teacher': 'Dr. Amina Said',
        'time': '11:30 - 1:00',
        'room': 'Lab 303',
      },
    ],
    // Add more days as needed
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Class Timetable'), elevation: 0),
      body: Column(
        children: [
          // Day Selector
          Container(
            height: 60,
            color: AppConstants.primaryColor.withOpacity(0.05),
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              itemCount: _days.length,
              itemBuilder: (context, index) {
                final isSelected = _selectedDay == index;
                return GestureDetector(
                  onTap: () {
                    setState(() {
                      _selectedDay = index;
                    });
                  },
                  child: Container(
                    margin: const EdgeInsets.symmetric(
                      horizontal: 4,
                      vertical: 8,
                    ),
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppConstants.primaryColor
                          : Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: isSelected
                          ? [
                              BoxShadow(
                                color: AppConstants.primaryColor.withOpacity(
                                  0.3,
                                ),
                                blurRadius: 8,
                                offset: const Offset(0, 4),
                              ),
                            ]
                          : null,
                    ),
                    child: Center(
                      child: Text(
                        _days[index].substring(0, 3),
                        style: TextStyle(
                          color: isSelected ? Colors.white : Colors.grey[700],
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),

          // Timetable Content
          Expanded(child: _buildTimetableContent()),
        ],
      ),
    );
  }

  Widget _buildTimetableContent() {
    final selectedDayName = _days[_selectedDay];
    final classes = _timetable[selectedDayName] ?? [];

    if (classes.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.event_busy, size: 80, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'No classes scheduled',
              style: TextStyle(
                fontSize: 18,
                color: Colors.grey[600],
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Enjoy your day off!',
              style: TextStyle(color: Colors.grey[500], fontSize: 14),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(20),
      itemCount: classes.length,
      itemBuilder: (context, index) {
        final classInfo = classes[index];
        final isBreak = classInfo['subject']!.contains('Break');

        return _buildClassCard(
          classInfo['subject']!,
          classInfo['teacher']!,
          classInfo['time']!,
          classInfo['room']!,
          isBreak,
        );
      },
    );
  }

  Widget _buildClassCard(
    String subject,
    String teacher,
    String time,
    String room,
    bool isBreak,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      child: Material(
        color: isBreak
            ? AppConstants.secondaryColor.withOpacity(0.05)
            : Colors.white,
        borderRadius: BorderRadius.circular(16),
        elevation: isBreak ? 0 : 2,
        shadowColor: Colors.black12,
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: isBreak
                ? Border.all(
                    color: AppConstants.secondaryColor.withOpacity(0.3),
                    width: 2,
                  )
                : null,
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color:
                      (isBreak
                              ? AppConstants.secondaryColor
                              : AppConstants.primaryColor)
                          .withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  isBreak ? Icons.restaurant : Icons.book,
                  color: isBreak
                      ? AppConstants.secondaryColor
                      : AppConstants.primaryColor,
                  size: 28,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      subject,
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: isBreak ? AppConstants.secondaryColor : null,
                      ),
                    ),
                    if (!isBreak && teacher.isNotEmpty) ...[
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          Icon(Icons.person, size: 14, color: Colors.grey[600]),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              teacher,
                              style: TextStyle(
                                color: Colors.grey[600],
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Icon(
                          Icons.access_time,
                          size: 14,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Text(
                          time,
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        if (!isBreak && room.isNotEmpty) ...[
                          const Text(
                            ' • ',
                            style: TextStyle(color: Colors.grey),
                          ),
                          Icon(Icons.room, size: 14, color: Colors.grey[600]),
                          const SizedBox(width: 4),
                          Text(
                            room,
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}












