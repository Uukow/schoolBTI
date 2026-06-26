class TeacherClass {
  final int id;
  final int classId;
  final String className;
  final int subjectId;
  final String subjectName;
  final String subjectCode;
  final int studentCount;
  final String? sectionName;

  TeacherClass({
    required this.id,
    required this.classId,
    required this.className,
    required this.subjectId,
    required this.subjectName,
    required this.subjectCode,
    required this.studentCount,
    this.sectionName,
  });

  factory TeacherClass.fromJson(Map<String, dynamic> json) {
    return TeacherClass(
      id: json['id'] ?? 0,
      classId: json['class_id'] ?? 0,
      className: json['class_name'] ?? '',
      subjectId: json['subject_id'] ?? 0,
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'] ?? '',
      studentCount: _parseInt(json['student_count'] ?? 0),
      sectionName: json['section_name'],
    );
  }

  static int _parseInt(dynamic value) {
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }
}

class TeacherStudent {
  final int id;
  final String studentId;
  final String fullName;
  final String? email;
  final String? phone;
  final String? photo;
  final int classId;
  final String className;
  final String? sectionName;
  final String status;

  TeacherStudent({
    required this.id,
    required this.studentId,
    required this.fullName,
    this.email,
    this.phone,
    this.photo,
    required this.classId,
    required this.className,
    this.sectionName,
    required this.status,
  });

  factory TeacherStudent.fromJson(Map<String, dynamic> json) {
    return TeacherStudent(
      id: json['id'] ?? 0,
      studentId: json['student_id'] ?? json['admission_number'] ?? '',
      fullName: json['full_name'] ?? 
                '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim(),
      email: json['email'],
      phone: json['phone'],
      photo: json['photo'],
      classId: json['class_id'] ?? json['current_class_id'] ?? 0,
      className: json['class_name'] ?? '',
      sectionName: json['section_name'],
      status: json['status'] ?? 'Active',
    );
  }
}

class TeacherTimetable {
  final int id;
  final String day;
  final String startTime;
  final String endTime;
  final int classId;
  final String className;
  final int subjectId;
  final String subjectName;
  final String? room;
  final String? sectionName;

  TeacherTimetable({
    required this.id,
    required this.day,
    required this.startTime,
    required this.endTime,
    required this.classId,
    required this.className,
    required this.subjectId,
    required this.subjectName,
    this.room,
    this.sectionName,
  });

  factory TeacherTimetable.fromJson(Map<String, dynamic> json) {
    return TeacherTimetable(
      id: json['id'] ?? 0,
      day: json['day'] ?? '',
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      classId: json['class_id'] ?? 0,
      className: json['class_name'] ?? '',
      subjectId: json['subject_id'] ?? 0,
      subjectName: json['subject_name'] ?? '',
      room: json['room'],
      sectionName: json['section_name'],
    );
  }
}

class TeacherStats {
  final int totalClasses;
  final int totalStudents;
  final int totalSubjects;
  final int todayClasses;
  final int pendingAttendance;
  final int pendingMarks;
  final int completedLessonPlans;
  final double attendanceRate;

  TeacherStats({
    required this.totalClasses,
    required this.totalStudents,
    required this.totalSubjects,
    required this.todayClasses,
    required this.pendingAttendance,
    required this.pendingMarks,
    required this.completedLessonPlans,
    required this.attendanceRate,
  });

  factory TeacherStats.fromJson(Map<String, dynamic> json) {
    return TeacherStats(
      totalClasses: _parseInt(json['total_classes'] ?? 0),
      totalStudents: _parseInt(json['total_students'] ?? 0),
      totalSubjects: _parseInt(json['total_subjects'] ?? 0),
      todayClasses: _parseInt(json['today_classes'] ?? 0),
      pendingAttendance: _parseInt(json['pending_attendance'] ?? 0),
      pendingMarks: _parseInt(json['pending_marks'] ?? 0),
      completedLessonPlans: _parseInt(json['completed_lesson_plans'] ?? 0),
      attendanceRate: _parseDouble(json['attendance_rate'] ?? 0),
    );
  }

  static int _parseInt(dynamic value) {
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

class TeacherLessonPlan {
  final int id;
  final String title;
  final String date;
  final int classId;
  final String className;
  final int subjectId;
  final String subjectName;
  final String? objectives;
  final String? activities;
  final String? materials;
  final String? homework;
  final String? notes;

  TeacherLessonPlan({
    required this.id,
    required this.title,
    required this.date,
    required this.classId,
    required this.className,
    required this.subjectId,
    required this.subjectName,
    this.objectives,
    this.activities,
    this.materials,
    this.homework,
    this.notes,
  });

  factory TeacherLessonPlan.fromJson(Map<String, dynamic> json) {
    return TeacherLessonPlan(
      id: json['id'] ?? 0,
      title: json['title'] ?? json['lesson_title'] ?? '',
      date: json['date'] ?? json['lesson_date'] ?? '',
      classId: json['class_id'] ?? 0,
      className: json['class_name'] ?? '',
      subjectId: json['subject_id'] ?? 0,
      subjectName: json['subject_name'] ?? '',
      objectives: json['objectives'],
      activities: json['activities'],
      materials: json['materials'],
      homework: json['homework'],
      notes: json['notes'],
    );
  }
}

