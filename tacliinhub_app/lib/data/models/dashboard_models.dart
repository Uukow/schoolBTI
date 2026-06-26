/// Dashboard Data Models
///
/// Comprehensive models for TacliinHub dashboard data
/// Supporting Students, Teachers, and Admin roles
library;

class DashboardData {
  final String role;
  final StudentDashboard? studentDashboard;
  final AdminDashboard? adminDashboard;
  final TeacherDashboard? teacherDashboard;

  DashboardData({
    required this.role,
    this.studentDashboard,
    this.adminDashboard,
    this.teacherDashboard,
  });

  factory DashboardData.fromJson(Map<String, dynamic> json, String role) {
    return DashboardData(
      role: role,
      studentDashboard: role == 'Student'
          ? StudentDashboard.fromJson(json)
          : null,
      adminDashboard: role != 'Student' && json['stats'] != null
          ? AdminDashboard.fromJson(json)
          : null,
      teacherDashboard: role == 'Teacher'
          ? TeacherDashboard.fromJson(json)
          : null,
    );
  }
}

/// Student Dashboard Model
class StudentDashboard {
  final AttendanceStats attendance;
  final String todayStatus;
  final int upcomingAssignments;
  final int upcomingExams;
  final double outstandingFees;
  final List<Announcement> announcements;
  final List<TimetableEntry> timetable;

  StudentDashboard({
    required this.attendance,
    required this.todayStatus,
    required this.upcomingAssignments,
    required this.upcomingExams,
    required this.outstandingFees,
    required this.announcements,
    required this.timetable,
  });

  factory StudentDashboard.fromJson(Map<String, dynamic> json) {
    return StudentDashboard(
      attendance: AttendanceStats.fromJson(json['attendance'] ?? {}),
      todayStatus: json['today_status'] ?? 'Not Marked',
      upcomingAssignments: _parseInt(json['upcoming_assignments']),
      upcomingExams: _parseInt(json['upcoming_exams']),
      outstandingFees:
          double.tryParse(json['outstanding_fees']?.toString() ?? '0') ?? 0.0,
      announcements:
          (json['announcements'] as List<dynamic>?)
              ?.map((a) => Announcement.fromJson(a))
              .toList() ??
          [],
      timetable:
          (json['timetable'] as List<dynamic>?)
              ?.map((t) => TimetableEntry.fromJson(t))
              .toList() ??
          [],
    );
  }
}

/// Admin Dashboard Model
class AdminDashboard {
  final StudentMetrics students;
  final StaffMetrics staff;
  final ClassMetrics classes;
  final AttendanceMetrics attendanceToday;
  final AttendanceMetrics attendanceMonth;
  final double revenueMonth;
  final double outstandingFees;
  final FinancialMetrics fees;
  final double discountsMonth;
  final int activeClasses;
  final int subjectsToday;
  final AttendanceCompletion attendanceCompletion;
  final ExamMetrics exams;
  final AssignmentMetrics assignments;
  final int pendingAdmissions;
  final int incompleteProfiles;
  final int overdueInvoices;
  final int openTickets;
  final PayrollMetrics payroll;
  final List<ActivityLog> activities;
  final List<TopClass> topClasses;

  AdminDashboard({
    required this.students,
    required this.staff,
    required this.classes,
    required this.attendanceToday,
    required this.attendanceMonth,
    required this.revenueMonth,
    required this.outstandingFees,
    required this.fees,
    required this.discountsMonth,
    required this.activeClasses,
    required this.subjectsToday,
    required this.attendanceCompletion,
    required this.exams,
    required this.assignments,
    required this.pendingAdmissions,
    required this.incompleteProfiles,
    required this.overdueInvoices,
    required this.openTickets,
    required this.payroll,
    required this.activities,
    required this.topClasses,
  });

  factory AdminDashboard.fromJson(Map<String, dynamic> json) {
    final stats = json['stats'] ?? {};
    return AdminDashboard(
      students: StudentMetrics.fromJson(stats['students'] ?? {}),
      staff: StaffMetrics.fromJson(stats['staff'] ?? {}),
      classes: ClassMetrics.fromJson(stats['classes'] ?? {}),
      attendanceToday: AttendanceMetrics.fromJson(
        stats['attendance_today'] ?? {},
      ),
      attendanceMonth: AttendanceMetrics.fromJson(
        stats['attendance_month'] ?? {},
      ),
      revenueMonth:
          double.tryParse(stats['revenue_month']?.toString() ?? '0') ?? 0.0,
      outstandingFees:
          double.tryParse(stats['outstanding_fees']?.toString() ?? '0') ?? 0.0,
      fees: FinancialMetrics.fromJson(stats['fees'] ?? {}),
      discountsMonth:
          double.tryParse(stats['discounts_month']?.toString() ?? '0') ?? 0.0,
      activeClasses: _parseInt(stats['active_classes']),
      subjectsToday: _parseInt(stats['subjects_today']),
      attendanceCompletion: AttendanceCompletion.fromJson(
        stats['attendance_completion'] ?? {},
      ),
      exams: ExamMetrics.fromJson(stats['exams'] ?? {}),
      assignments: AssignmentMetrics.fromJson(stats['assignments'] ?? {}),
      pendingAdmissions: _parseInt(stats['pending_admissions']),
      incompleteProfiles: _parseInt(stats['incomplete_profiles']),
      overdueInvoices: _parseInt(stats['overdue_invoices']),
      openTickets: _parseInt(stats['open_tickets']),
      payroll: PayrollMetrics.fromJson(stats['payroll'] ?? {}),
      activities:
          (json['activities'] as List<dynamic>?)
              ?.map((a) => ActivityLog.fromJson(a))
              .toList() ??
          [],
      topClasses:
          (json['top_classes'] as List<dynamic>?)
              ?.map((c) => TopClass.fromJson(c))
              .toList() ??
          [],
    );
  }
}

/// Teacher Dashboard Model (Placeholder for future enhancement)
class TeacherDashboard {
  final String message;

  TeacherDashboard({required this.message});

  factory TeacherDashboard.fromJson(Map<String, dynamic> json) {
    return TeacherDashboard(message: json['message'] ?? 'Teacher dashboard');
  }
}

/// Supporting Models

class AttendanceStats {
  final double percentage;
  final int present;
  final int absent;
  final int total;

  AttendanceStats({
    required this.percentage,
    required this.present,
    required this.absent,
    required this.total,
  });

  factory AttendanceStats.fromJson(Map<String, dynamic> json) {
    return AttendanceStats(
      percentage: double.tryParse(json['percentage']?.toString() ?? '0') ?? 0.0,
      present: _parseInt(json['present']),
      absent: _parseInt(json['absent']),
      total: _parseInt(json['total']),
    );
  }
}

class StudentMetrics {
  final int total;
  final int active;
  final int graduated;
  final int inactive;

  StudentMetrics({
    required this.total,
    required this.active,
    required this.graduated,
    required this.inactive,
  });

  factory StudentMetrics.fromJson(Map<String, dynamic> json) {
    return StudentMetrics(
      total: _parseInt(json['total']),
      active: _parseInt(json['active']),
      graduated: _parseInt(json['graduated']),
      inactive: _parseInt(json['inactive']),
    );
  }
}

/// Helper function to parse int from dynamic (handles both String and int)
int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}

class StaffMetrics {
  final int total;
  final int active;
  final int teachers;
  final int staff;

  StaffMetrics({
    required this.total,
    required this.active,
    required this.teachers,
    required this.staff,
  });

  factory StaffMetrics.fromJson(Map<String, dynamic> json) {
    return StaffMetrics(
      total: _parseInt(json['total']),
      active: _parseInt(json['active']),
      teachers: _parseInt(json['teachers']),
      staff: _parseInt(json['staff']),
    );
  }
}

class ClassMetrics {
  final int total;
  final int active;
  final int graduated;

  ClassMetrics({
    required this.total,
    required this.active,
    required this.graduated,
  });

  factory ClassMetrics.fromJson(Map<String, dynamic> json) {
    return ClassMetrics(
      total: _parseInt(json['total']),
      active: _parseInt(json['active']),
      graduated: _parseInt(json['graduated']),
    );
  }
}

class AttendanceMetrics {
  final int total;
  final int present;
  final int? absent;
  final int? late;
  final double percentage;

  AttendanceMetrics({
    required this.total,
    required this.present,
    this.absent,
    this.late,
    required this.percentage,
  });

  factory AttendanceMetrics.fromJson(Map<String, dynamic> json) {
    return AttendanceMetrics(
      total: _parseInt(
        json['total'] ?? json['total_students'] ?? json['total_records'],
      ),
      present: _parseInt(json['present']),
      absent: json['absent'] != null ? _parseInt(json['absent']) : null,
      late: json['late'] != null ? _parseInt(json['late']) : null,
      percentage: double.tryParse(json['percentage']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class FinancialMetrics {
  final double paid;
  final double unpaid;
  final double total;

  FinancialMetrics({
    required this.paid,
    required this.unpaid,
    required this.total,
  });

  factory FinancialMetrics.fromJson(Map<String, dynamic> json) {
    return FinancialMetrics(
      paid: double.tryParse(json['paid']?.toString() ?? '0') ?? 0.0,
      unpaid: double.tryParse(json['unpaid']?.toString() ?? '0') ?? 0.0,
      total: double.tryParse(json['total']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class AttendanceCompletion {
  final int totalClasses;
  final int completed;
  final int pending;
  final double percentage;

  AttendanceCompletion({
    required this.totalClasses,
    required this.completed,
    required this.pending,
    required this.percentage,
  });

  factory AttendanceCompletion.fromJson(Map<String, dynamic> json) {
    return AttendanceCompletion(
      totalClasses: _parseInt(json['total_classes']),
      completed: _parseInt(json['completed']),
      pending: _parseInt(json['pending']),
      percentage: double.tryParse(json['percentage']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class ExamMetrics {
  final int total;
  final int completed;
  final int ongoing;
  final int upcoming;

  ExamMetrics({
    required this.total,
    required this.completed,
    required this.ongoing,
    required this.upcoming,
  });

  factory ExamMetrics.fromJson(Map<String, dynamic> json) {
    return ExamMetrics(
      total: _parseInt(json['total'] ?? json['total_exams']),
      completed: _parseInt(json['completed']),
      ongoing: _parseInt(json['ongoing']),
      upcoming: _parseInt(json['upcoming']),
    );
  }
}

class AssignmentMetrics {
  final int total;
  final int overdue;
  final int pending;

  AssignmentMetrics({
    required this.total,
    required this.overdue,
    required this.pending,
  });

  factory AssignmentMetrics.fromJson(Map<String, dynamic> json) {
    return AssignmentMetrics(
      total: _parseInt(json['total']),
      overdue: _parseInt(json['overdue']),
      pending: _parseInt(json['pending']),
    );
  }
}

class PayrollMetrics {
  final int totalStaff;
  final int paid;
  final int pending;

  PayrollMetrics({
    required this.totalStaff,
    required this.paid,
    required this.pending,
  });

  factory PayrollMetrics.fromJson(Map<String, dynamic> json) {
    return PayrollMetrics(
      totalStaff: _parseInt(json['total_staff']),
      paid: _parseInt(json['paid']),
      pending: _parseInt(json['pending']),
    );
  }
}

class Announcement {
  final String title;
  final String content;
  final String createdAt;

  Announcement({
    required this.title,
    required this.content,
    required this.createdAt,
  });

  factory Announcement.fromJson(Map<String, dynamic> json) {
    return Announcement(
      title: json['title'] ?? '',
      content: json['content'] ?? '',
      createdAt: json['created_at'] ?? '',
    );
  }
}

class TimetableEntry {
  final String startTime;
  final String endTime;
  final String subjectName;
  final String? subjectCode;
  final String? roomNo;

  TimetableEntry({
    required this.startTime,
    required this.endTime,
    required this.subjectName,
    this.subjectCode,
    this.roomNo,
  });

  factory TimetableEntry.fromJson(Map<String, dynamic> json) {
    return TimetableEntry(
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'],
      roomNo: json['room_no'],
    );
  }
}

class ActivityLog {
  final int id;
  final String? username;
  final String? action;
  final String? description;
  final String? createdAt;

  ActivityLog({
    required this.id,
    this.username,
    this.action,
    this.description,
    this.createdAt,
  });

  factory ActivityLog.fromJson(Map<String, dynamic> json) {
    return ActivityLog(
      id: _parseInt(json['id']),
      username: json['username'],
      action: json['action'],
      description: json['description'],
      createdAt: json['created_at'],
    );
  }
}

class TopClass {
  final int id;
  final String className;
  final String? classCode;
  final int studentsCount;
  final double avgPercentage;

  TopClass({
    required this.id,
    required this.className,
    this.classCode,
    required this.studentsCount,
    required this.avgPercentage,
  });

  factory TopClass.fromJson(Map<String, dynamic> json) {
    return TopClass(
      id: _parseInt(json['id']),
      className: json['class_name'] ?? '',
      classCode: json['class_code'],
      studentsCount: _parseInt(json['students_count']),
      avgPercentage:
          double.tryParse(json['avg_percentage']?.toString() ?? '0') ?? 0.0,
    );
  }
}
