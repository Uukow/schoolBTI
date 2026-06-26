class Event {
  final int id;
  final String title;
  final String description;
  final DateTime startDate;
  final DateTime endDate;
  final String? startTime;
  final String? endTime;
  final String
  eventType; // 'Academic', 'Holiday', 'Exam', 'Activity', 'Meeting', 'Other'
  final String? location;
  final String? color; // Hex color code
  final bool isAllDay;
  final bool isRecurring;
  final String? recurrencePattern; // 'Daily', 'Weekly', 'Monthly', 'Yearly'
  final int? recurrenceInterval;
  final DateTime? recurrenceEndDate;
  final String?
  targetAudience; // 'All', 'Students', 'Teachers', 'Parents', 'Staff'
  final int? classId;
  final String? className;
  final String status; // 'Scheduled', 'Ongoing', 'Completed', 'Cancelled'
  final String createdBy;
  final String createdAt;
  final String? updatedAt;

  Event({
    required this.id,
    required this.title,
    required this.description,
    required this.startDate,
    required this.endDate,
    this.startTime,
    this.endTime,
    required this.eventType,
    this.location,
    this.color,
    required this.isAllDay,
    required this.isRecurring,
    this.recurrencePattern,
    this.recurrenceInterval,
    this.recurrenceEndDate,
    this.targetAudience,
    this.classId,
    this.className,
    required this.status,
    required this.createdBy,
    required this.createdAt,
    this.updatedAt,
  });

  factory Event.fromJson(Map<String, dynamic> json) {
    return Event(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      description: json['description'] ?? json['content'] ?? '',
      startDate: DateTime.parse(
        json['start_date'] ??
            json['startDate'] ??
            json['start'] ??
            DateTime.now().toIso8601String(),
      ),
      endDate: DateTime.parse(
        json['end_date'] ??
            json['endDate'] ??
            json['end'] ??
            DateTime.now().toIso8601String(),
      ),
      startTime: json['start_time'] ?? json['startTime'],
      endTime: json['end_time'] ?? json['endTime'],
      eventType: json['event_type'] ?? json['eventType'] ?? 'Other',
      location: json['location'],
      color: json['color'],
      isAllDay:
          json['is_all_day'] == 1 ||
          json['is_all_day'] == true ||
          json['isAllDay'] == true,
      isRecurring:
          json['is_recurring'] == 1 ||
          json['is_recurring'] == true ||
          json['isRecurring'] == true,
      recurrencePattern:
          json['recurrence_pattern'] ?? json['recurrencePattern'],
      recurrenceInterval: json['recurrence_interval'] != null
          ? int.tryParse(json['recurrence_interval'].toString())
          : json['recurrenceInterval'],
      recurrenceEndDate: json['recurrence_end_date'] != null
          ? DateTime.tryParse(json['recurrence_end_date'])
          : (json['recurrenceEndDate'] != null
                ? DateTime.tryParse(json['recurrenceEndDate'])
                : null),
      targetAudience: json['target_audience'] ?? json['targetAudience'],
      classId: json['class_id'] ?? json['classId'],
      className: json['class_name'] ?? json['className'],
      status: json['status'] ?? 'Scheduled',
      createdBy: json['created_by'] ?? json['createdBy'] ?? '',
      createdAt: json['created_at'] ?? json['createdAt'] ?? '',
      updatedAt: json['updated_at'] ?? json['updatedAt'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'start_date': startDate.toIso8601String(),
      'end_date': endDate.toIso8601String(),
      'start_time': startTime,
      'end_time': endTime,
      'event_type': eventType,
      'location': location,
      'color': color,
      'is_all_day': isAllDay ? 1 : 0,
      'is_recurring': isRecurring ? 1 : 0,
      'recurrence_pattern': recurrencePattern,
      'recurrence_interval': recurrenceInterval,
      'recurrence_end_date': recurrenceEndDate?.toIso8601String(),
      'target_audience': targetAudience,
      'class_id': classId,
      'class_name': className,
      'status': status,
      'created_by': createdBy,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  String get displayDate {
    if (isAllDay) {
      if (startDate.year == endDate.year &&
          startDate.month == endDate.month &&
          startDate.day == endDate.day) {
        return '${startDate.day}/${startDate.month}/${startDate.year}';
      }
      return '${startDate.day}/${startDate.month}/${startDate.year} - ${endDate.day}/${endDate.month}/${endDate.year}';
    }
    if (startTime != null && endTime != null) {
      return '${startDate.day}/${startDate.month}/${startDate.year} $startTime - $endTime';
    }
    return '${startDate.day}/${startDate.month}/${startDate.year}';
  }
}

class CalendarEvent {
  final DateTime date;
  final List<Event> events;
  final int eventCount;

  CalendarEvent({required this.date, required this.events})
    : eventCount = events.length;

  bool get hasEvents => events.isNotEmpty;
}
