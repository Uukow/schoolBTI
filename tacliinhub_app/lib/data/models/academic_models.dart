/// Academic Models for TacliinHub
library;

class Subject {
  final int id;
  final String subjectName;
  final String subjectCode;
  final String? subjectType;
  final String? description;
  final bool isActive;
  final int? branchId;
  final String? branchName;

  Subject({
    required this.id,
    required this.subjectName,
    required this.subjectCode,
    this.subjectType,
    this.description,
    required this.isActive,
    this.branchId,
    this.branchName,
  });

  factory Subject.fromJson(Map<String, dynamic> json) {
    return Subject(
      id: _parseInt(json['id']),
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'] ?? '',
      subjectType: json['subject_type'],
      description: json['description'],
      isActive: json['is_active'] == true || json['is_active'] == 1,
      branchId: json['branch_id'] != null ? _parseInt(json['branch_id']) : null,
      branchName: json['branch_name'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'subject_name': subjectName,
      'subject_code': subjectCode,
      'subject_type': subjectType,
      'description': description,
      'is_active': isActive,
      'branch_id': branchId,
      'branch_name': branchName,
    };
  }
}

class ClassAssignment {
  final int id;
  final int classId;
  final String? className;
  final int subjectId;
  final String subjectName;
  final String? subjectCode;
  final int? teacherId;
  final String? teacherName;
  final int sessionId;
  final String? sessionName;

  ClassAssignment({
    required this.id,
    required this.classId,
    this.className,
    required this.subjectId,
    required this.subjectName,
    this.subjectCode,
    this.teacherId,
    this.teacherName,
    required this.sessionId,
    this.sessionName,
  });

  factory ClassAssignment.fromJson(Map<String, dynamic> json) {
    return ClassAssignment(
      id: _parseInt(json['id']),
      classId: _parseInt(json['class_id']),
      className: json['class_name'],
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'],
      teacherId: json['teacher_id'] != null ? _parseInt(json['teacher_id']) : null,
      teacherName: json['teacher_first_name'] != null && json['teacher_last_name'] != null
          ? '${json['teacher_first_name']} ${json['teacher_last_name']}'
          : json['teacher_name'],
      sessionId: _parseInt(json['session_id']),
      sessionName: json['session_name'],
    );
  }
}

class TimetablePeriod {
  final int id;
  final int classId;
  final String? className;
  final int sectionId;
  final String? sectionName;
  final int subjectId;
  final String subjectName;
  final int? teacherId;
  final String? teacherName;
  final String dayOfWeek;
  final String startTime;
  final String endTime;
  final String? roomNo;
  final int sessionId;

  TimetablePeriod({
    required this.id,
    required this.classId,
    this.className,
    required this.sectionId,
    this.sectionName,
    required this.subjectId,
    required this.subjectName,
    this.teacherId,
    this.teacherName,
    required this.dayOfWeek,
    required this.startTime,
    required this.endTime,
    this.roomNo,
    required this.sessionId,
  });

  factory TimetablePeriod.fromJson(Map<String, dynamic> json) {
    return TimetablePeriod(
      id: _parseInt(json['id']),
      classId: _parseInt(json['class_id']),
      className: json['class_name'],
      sectionId: _parseInt(json['section_id']),
      sectionName: json['section_name'],
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      teacherId: json['teacher_id'] != null ? _parseInt(json['teacher_id']) : null,
      teacherName: json['first_name'] != null && json['last_name'] != null
          ? '${json['first_name']} ${json['last_name']}'
          : json['teacher_name'],
      dayOfWeek: json['day_of_week'] ?? '',
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      roomNo: json['room_no'],
      sessionId: _parseInt(json['session_id']),
    );
  }
}

class LessonPlan {
  final int id;
  final int classId;
  final String? className;
  final int subjectId;
  final String subjectName;
  final String title;
  final String? description;
  final String? objectives;
  final String? materials;
  final String? activities;
  final String? homework;
  final DateTime date;
  final int? teacherId;
  final String? teacherName;
  final int sessionId;

  LessonPlan({
    required this.id,
    required this.classId,
    this.className,
    required this.subjectId,
    required this.subjectName,
    required this.title,
    this.description,
    this.objectives,
    this.materials,
    this.activities,
    this.homework,
    required this.date,
    this.teacherId,
    this.teacherName,
    required this.sessionId,
  });

  factory LessonPlan.fromJson(Map<String, dynamic> json) {
    // Handle both 'title' (from API alias) and 'lesson_title' (from DB)
    final title = json['title'] ?? json['lesson_title'] ?? '';
    // Handle both 'date' (from API alias) and 'lesson_date' (from DB)
    final dateStr = json['date'] ?? json['lesson_date'] ?? DateTime.now().toString();
    
    return LessonPlan(
      id: _parseInt(json['id']),
      classId: _parseInt(json['class_id']),
      className: json['class_name'],
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      title: title,
      description: json['description'] ?? json['content'],
      objectives: json['objectives'],
      materials: json['materials'] ?? json['resources'],
      activities: json['activities'] ?? json['methodology'],
      homework: json['homework'] ?? json['assessment'],
      date: DateTime.parse(dateStr),
      teacherId: json['teacher_id'] != null ? _parseInt(json['teacher_id']) : null,
      teacherName: json['teacher_first_name'] != null && json['teacher_last_name'] != null
          ? '${json['teacher_first_name']} ${json['teacher_last_name']}'
          : json['teacher_name'],
      sessionId: _parseInt(json['session_id']),
    );
  }
}

class Syllabus {
  final int id;
  final int classId;
  final String? className;
  final int subjectId;
  final String subjectName;
  final String title;
  final String? description;
  final String? filePath;
  final String? fileName;
  final DateTime? uploadedAt;
  final int? uploadedBy;
  final String? uploadedByName;

  Syllabus({
    required this.id,
    required this.classId,
    this.className,
    required this.subjectId,
    required this.subjectName,
    required this.title,
    this.description,
    this.filePath,
    this.fileName,
    this.uploadedAt,
    this.uploadedBy,
    this.uploadedByName,
  });

  factory Syllabus.fromJson(Map<String, dynamic> json) {
    // Handle date parsing for uploaded_at
    DateTime? uploadedAt;
    if (json['uploaded_at'] != null) {
      try {
        uploadedAt = DateTime.parse(json['uploaded_at']);
      } catch (e) {
        uploadedAt = null;
      }
    }
    
    return Syllabus(
      id: _parseInt(json['id']),
      classId: _parseInt(json['class_id']),
      className: json['class_name'],
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      title: json['title'] ?? 'Syllabus',
      description: json['description'] ?? json['syllabus'],
      filePath: json['file_path'],
      fileName: json['file_name'],
      uploadedAt: uploadedAt,
      uploadedBy: json['uploaded_by'] != null ? _parseInt(json['uploaded_by']) : null,
      uploadedByName: json['uploaded_by_name'],
    );
  }
}

class AcademicCalendar {
  final int id;
  final String title;
  final String? description;
  final DateTime startDate;
  final DateTime endDate;
  final String eventType;
  final String? color;
  final bool isHoliday;
  final int? branchId;
  final String? branchName;
  final int sessionId;

  AcademicCalendar({
    required this.id,
    required this.title,
    this.description,
    required this.startDate,
    required this.endDate,
    required this.eventType,
    this.color,
    required this.isHoliday,
    this.branchId,
    this.branchName,
    required this.sessionId,
  });

  factory AcademicCalendar.fromJson(Map<String, dynamic> json) {
    // Handle both 'title' (from API alias) and 'event_title' (from DB)
    final title = json['title'] ?? json['event_title'] ?? '';
    // Handle date parsing
    final startDateStr = json['start_date'] ?? json['event_date'] ?? DateTime.now().toString();
    final endDateStr = json['end_date'] ?? json['event_date'] ?? DateTime.now().toString();
    
    return AcademicCalendar(
      id: _parseInt(json['id']),
      title: title,
      description: json['description'] ?? json['event_description'],
      startDate: DateTime.parse(startDateStr),
      endDate: DateTime.parse(endDateStr),
      eventType: json['event_type'] ?? json['type'] ?? 'Event',
      color: json['color'],
      isHoliday: json['is_holiday'] == true || json['is_holiday'] == 1 || (json['event_type'] ?? '') == 'Holiday',
      branchId: json['branch_id'] != null ? _parseInt(json['branch_id']) : null,
      branchName: json['branch_name'],
      sessionId: _parseInt(json['session_id']),
    );
  }
}

/// Helper function to parse int
int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}

