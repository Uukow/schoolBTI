/// Attendance Models for TacliinHub
library;

class StudentAttendance {
  final int id;
  final int studentId;
  final String studentName;
  final String? studentIdNumber;
  final int classId;
  final String className;
  final int sectionId;
  final String sectionName;
  final int? subjectId;
  final String? subjectName;
  final DateTime attendanceDate;
  final String status; // Present, Absent, Late, Half Day, Leave
  final String? remarks;
  final int? markedBy;
  final String? markedByName;
  final DateTime createdAt;

  StudentAttendance({
    required this.id,
    required this.studentId,
    required this.studentName,
    this.studentIdNumber,
    required this.classId,
    required this.className,
    required this.sectionId,
    required this.sectionName,
    this.subjectId,
    this.subjectName,
    required this.attendanceDate,
    required this.status,
    this.remarks,
    this.markedBy,
    this.markedByName,
    required this.createdAt,
  });

  factory StudentAttendance.fromJson(Map<String, dynamic> json) {
    return StudentAttendance(
      id: _parseInt(json['id']),
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? 
                   '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim(),
      studentIdNumber: json['student_id_number'] ?? json['student_id'],
      classId: _parseInt(json['class_id']),
      className: json['class_name'] ?? '',
      sectionId: _parseInt(json['section_id']),
      sectionName: json['section_name'] ?? '',
      subjectId: json['subject_id'] != null ? _parseInt(json['subject_id']) : null,
      subjectName: json['subject_name'],
      attendanceDate: DateTime.parse(json['attendance_date']),
      status: json['status'] ?? 'Present',
      remarks: json['remarks'],
      markedBy: json['marked_by'] != null ? _parseInt(json['marked_by']) : null,
      markedByName: json['marked_by_name'],
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
    );
  }
}

class StaffAttendance {
  final int id;
  final int staffId;
  final String staffName;
  final String? staffIdNumber;
  final DateTime attendanceDate;
  final String? checkIn;
  final String? checkOut;
  final String status; // Present, Absent, Late, Half Day, Leave
  final String? remarks;
  final int? markedBy;
  final String? markedByName;
  final DateTime createdAt;

  StaffAttendance({
    required this.id,
    required this.staffId,
    required this.staffName,
    this.staffIdNumber,
    required this.attendanceDate,
    this.checkIn,
    this.checkOut,
    required this.status,
    this.remarks,
    this.markedBy,
    this.markedByName,
    required this.createdAt,
  });

  factory StaffAttendance.fromJson(Map<String, dynamic> json) {
    return StaffAttendance(
      id: _parseInt(json['id']),
      staffId: _parseInt(json['staff_id']),
      staffName: json['staff_name'] ?? 
                 '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim(),
      staffIdNumber: json['staff_id'] ?? json['staff_id_number'],
      attendanceDate: DateTime.parse(json['attendance_date']),
      checkIn: json['check_in'],
      checkOut: json['check_out'],
      status: json['status'] ?? 'Present',
      remarks: json['remarks'],
      markedBy: json['marked_by'] != null ? _parseInt(json['marked_by']) : null,
      markedByName: json['marked_by_name'],
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
    );
  }
}

class AttendanceStats {
  final int totalDays;
  final int presentDays;
  final int absentDays;
  final int lateDays;
  final int halfDays;
  final int leaveDays;
  final double attendanceRate;

  AttendanceStats({
    required this.totalDays,
    required this.presentDays,
    required this.absentDays,
    required this.lateDays,
    required this.halfDays,
    required this.leaveDays,
    required this.attendanceRate,
  });

  factory AttendanceStats.fromJson(Map<String, dynamic> json) {
    final total = json['total_days'] ?? 0;
    final present = json['present_days'] ?? 0;
    final rate = total > 0 ? (present / total) * 100 : 0.0;
    
    return AttendanceStats(
      totalDays: total,
      presentDays: json['present_days'] ?? 0,
      absentDays: json['absent_days'] ?? 0,
      lateDays: json['late_days'] ?? 0,
      halfDays: json['half_days'] ?? 0,
      leaveDays: json['leave_days'] ?? 0,
      attendanceRate: rate,
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

