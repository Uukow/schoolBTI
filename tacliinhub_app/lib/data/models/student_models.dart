/// Student Portal Models
library;

class Student {
  final int id;
  final String studentId;
  final String admissionNo;
  final String firstName;
  final String lastName;
  final String? middleName;
  final String? email;
  final String? phone;
  final int? currentClassId;
  final String? className;
  final int? currentSectionId;
  final String? sectionName;
  final String status;
  final String? photo;

  Student({
    required this.id,
    required this.studentId,
    required this.admissionNo,
    required this.firstName,
    required this.lastName,
    this.middleName,
    this.email,
    this.phone,
    this.currentClassId,
    this.className,
    this.currentSectionId,
    this.sectionName,
    required this.status,
    this.photo,
  });

  String get fullName => middleName != null
      ? '$firstName $middleName $lastName'
      : '$firstName $lastName';

  factory Student.fromJson(Map<String, dynamic> json) {
    return Student(
      id: _parseInt(json['id']),
      studentId: json['student_id'] ?? '',
      admissionNo: json['admission_no'] ?? '',
      firstName: json['first_name'] ?? '',
      lastName: json['last_name'] ?? '',
      middleName: json['middle_name'],
      email: json['email'],
      phone: json['phone'],
      currentClassId: json['current_class_id'] != null
          ? _parseInt(json['current_class_id'])
          : null,
      className: json['class_name'],
      currentSectionId: json['current_section_id'] != null
          ? _parseInt(json['current_section_id'])
          : null,
      sectionName: json['section_name'],
      status: json['status'] ?? 'Active',
      photo: json['photo'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'student_id': studentId,
      'admission_no': admissionNo,
      'first_name': firstName,
      'last_name': lastName,
      'middle_name': middleName,
      'email': email,
      'phone': phone,
      'current_class_id': currentClassId,
      'class_name': className,
      'current_section_id': currentSectionId,
      'section_name': sectionName,
      'status': status,
      'photo': photo,
    };
  }
}

class StudentStats {
  final int total;
  final int active;
  final int inactive;
  final int graduated;

  StudentStats({
    required this.total,
    required this.active,
    required this.inactive,
    required this.graduated,
  });

  factory StudentStats.fromJson(Map<String, dynamic> json) {
    return StudentStats(
      total: _parseInt(json['total'] ?? 0),
      active: _parseInt(json['active'] ?? 0),
      inactive: _parseInt(json['inactive'] ?? 0),
      graduated: _parseInt(json['graduated'] ?? 0),
    );
  }
}

class StudentProfile {
  final int id;
  final int userId;
  final String studentId;
  final String admissionNo;
  final String firstName;
  final String lastName;
  final String? middleName;
  final String gender;
  final DateTime dateOfBirth;
  final String? email;
  final String? phone;
  final String? address;
  final String? city;
  final String? state;
  final String? postalCode;
  final String? photo;
  final DateTime admissionDate;
  final int? currentClassId;
  final String? className;
  final int? currentSectionId;
  final String? sectionName;
  final String status;
  final String? branchName;

  StudentProfile({
    required this.id,
    required this.userId,
    required this.studentId,
    required this.admissionNo,
    required this.firstName,
    required this.lastName,
    this.middleName,
    required this.gender,
    required this.dateOfBirth,
    this.email,
    this.phone,
    this.address,
    this.city,
    this.state,
    this.postalCode,
    this.photo,
    required this.admissionDate,
    this.currentClassId,
    this.className,
    this.currentSectionId,
    this.sectionName,
    required this.status,
    this.branchName,
  });

  String get fullName => middleName != null
      ? '$firstName $middleName $lastName'
      : '$firstName $lastName';

  factory StudentProfile.fromJson(Map<String, dynamic> json) {
    return StudentProfile(
      id: _parseInt(json['id']),
      userId: _parseInt(json['user_id'] ?? 0),
      studentId: json['student_id'] ?? '',
      admissionNo: json['admission_no'] ?? '',
      firstName: json['first_name'] ?? '',
      lastName: json['last_name'] ?? '',
      middleName: json['middle_name'],
      gender: json['gender'] ?? '',
      dateOfBirth: _parseDateTime(json['date_of_birth']) ?? DateTime.now(),
      email: json['email'],
      phone: json['phone'],
      address: json['address'],
      city: json['city'],
      state: json['state'],
      postalCode: json['postal_code'],
      photo: json['photo'],
      admissionDate: _parseDateTime(json['admission_date']) ?? DateTime.now(),
      currentClassId: json['current_class_id'] != null
          ? _parseInt(json['current_class_id'])
          : null,
      className: json['class_name'],
      currentSectionId: json['current_section_id'] != null
          ? _parseInt(json['current_section_id'])
          : null,
      sectionName: json['section_name'],
      status: json['status'] ?? 'Active',
      branchName: json['branch_name'],
    );
  }
}

class StudentClass {
  final int classId;
  final String className;
  final int subjectId;
  final String subjectName;
  final String? subjectCode;
  final String? teacherName;

  StudentClass({
    required this.classId,
    required this.className,
    required this.subjectId,
    required this.subjectName,
    this.subjectCode,
    this.teacherName,
  });

  factory StudentClass.fromJson(Map<String, dynamic> json) {
    return StudentClass(
      classId: _parseInt(json['class_id']),
      className: json['class_name'] ?? '',
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'],
      teacherName: json['teacher_name'],
    );
  }
}

class StudentTimetable {
  final int id;
  final String day;
  final String startTime;
  final String endTime;
  final String subjectName;
  final String? teacherName;
  final String? roomNo;

  StudentTimetable({
    required this.id,
    required this.day,
    required this.startTime,
    required this.endTime,
    required this.subjectName,
    this.teacherName,
    this.roomNo,
  });

  factory StudentTimetable.fromJson(Map<String, dynamic> json) {
    return StudentTimetable(
      id: _parseInt(json['id']),
      day: json['day'] ?? '',
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      subjectName: json['subject_name'] ?? '',
      teacherName: json['teacher_name'],
      roomNo: json['room_no'] ?? json['room'],
    );
  }
}

class StudentAttendance {
  final DateTime date;
  final String status; // Present, Absent, Late, Leave
  final String? remarks;
  final String subjectName;
  final String? subjectCode;

  StudentAttendance({
    required this.date,
    required this.status,
    this.remarks,
    required this.subjectName,
    this.subjectCode,
  });

  factory StudentAttendance.fromJson(Map<String, dynamic> json) {
    return StudentAttendance(
      date: _parseDateTime(json['date']) ?? DateTime.now(),
      status: json['status'] ?? 'Absent',
      remarks: json['remarks'],
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'],
    );
  }
}

class AttendanceStats {
  final int totalDays;
  final int present;
  final int absent;
  final int late;
  final int leave;
  final double attendancePercentage;
  final double absencePercentage;

  AttendanceStats({
    required this.totalDays,
    required this.present,
    required this.absent,
    required this.late,
    required this.leave,
    required this.attendancePercentage,
    required this.absencePercentage,
  });

  factory AttendanceStats.fromJson(Map<String, dynamic> json) {
    return AttendanceStats(
      totalDays: _parseInt(json['total_days']),
      present: _parseInt(json['present']),
      absent: _parseInt(json['absent']),
      late: _parseInt(json['late']),
      leave: _parseInt(json['leave']),
      attendancePercentage: (json['attendance_percentage'] is double)
          ? json['attendance_percentage']
          : double.tryParse(json['attendance_percentage']?.toString() ?? '0') ?? 0.0,
      absencePercentage: (json['absence_percentage'] is double)
          ? json['absence_percentage']
          : double.tryParse(json['absence_percentage']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class TimePeriodStats {
  final int totalDays;
  final int absent;
  final double absencePercentage;

  TimePeriodStats({
    required this.totalDays,
    required this.absent,
    required this.absencePercentage,
  });

  factory TimePeriodStats.fromJson(Map<String, dynamic> json) {
    return TimePeriodStats(
      totalDays: _parseInt(json['total_days']),
      absent: _parseInt(json['absent']),
      absencePercentage: (json['absence_percentage'] is double)
          ? json['absence_percentage']
          : double.tryParse(json['absence_percentage']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class SubjectAttendanceStats {
  final String subjectName;
  final String subjectCode;
  final int totalDays;
  final int present;
  final int absent;
  final int late;
  final int leave;
  final double attendancePercentage;

  SubjectAttendanceStats({
    required this.subjectName,
    required this.subjectCode,
    required this.totalDays,
    required this.present,
    required this.absent,
    required this.late,
    required this.leave,
    required this.attendancePercentage,
  });

  factory SubjectAttendanceStats.fromJson(Map<String, dynamic> json) {
    return SubjectAttendanceStats(
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'] ?? '',
      totalDays: _parseInt(json['total_days']),
      present: _parseInt(json['present']),
      absent: _parseInt(json['absent']),
      late: _parseInt(json['late']),
      leave: _parseInt(json['leave']),
      attendancePercentage: (json['attendance_percentage'] is double)
          ? json['attendance_percentage']
          : double.tryParse(json['attendance_percentage']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class StudentMark {
  final int examId;
  final String examName;
  final int subjectId;
  final String subjectName;
  final double? marksObtained;
  final double totalMarks;
  final String? grade;
  final int? rank;

  StudentMark({
    required this.examId,
    required this.examName,
    required this.subjectId,
    required this.subjectName,
    this.marksObtained,
    required this.totalMarks,
    this.grade,
    this.rank,
  });

  factory StudentMark.fromJson(Map<String, dynamic> json) {
    return StudentMark(
      examId: _parseInt(json['exam_id']),
      examName: json['exam_name'] ?? '',
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      marksObtained: json['marks_obtained'] != null
          ? (json['marks_obtained'] is double
              ? json['marks_obtained']
              : double.tryParse(json['marks_obtained'].toString()))
          : null,
      totalMarks: json['total_marks'] != null
          ? (json['total_marks'] is double
              ? json['total_marks']
              : double.tryParse(json['total_marks'].toString()) ?? 100.0)
          : 100.0,
      grade: json['grade'],
      rank: json['rank'] != null ? _parseInt(json['rank']) : null,
    );
  }
}

class StudentFee {
  final int id;
  final String feeType;
  final double amount;
  final double? paidAmount;
  final double? discountAmount;
  final DateTime dueDate;
  final String status; // Paid, Pending, Overdue
  final DateTime? paidDate;

  StudentFee({
    required this.id,
    required this.feeType,
    required this.amount,
    this.paidAmount,
    this.discountAmount,
    required this.dueDate,
    required this.status,
    this.paidDate,
  });

  double get remainingAmount => amount - (paidAmount ?? 0) - (discountAmount ?? 0);

  factory StudentFee.fromJson(Map<String, dynamic> json) {
    return StudentFee(
      id: _parseInt(json['id']),
      feeType: json['fee_type'] ?? '',
      amount: json['amount'] != null
          ? (json['amount'] is double
              ? json['amount']
              : double.tryParse(json['amount'].toString()) ?? 0.0)
          : 0.0,
      paidAmount: json['paid_amount'] != null
          ? (json['paid_amount'] is double
              ? json['paid_amount']
              : double.tryParse(json['paid_amount'].toString()))
          : null,
      discountAmount: json['discount_amount'] != null
          ? (json['discount_amount'] is double
              ? json['discount_amount']
              : double.tryParse(json['discount_amount'].toString()))
          : null,
      dueDate: _parseDateTime(json['due_date']) ?? DateTime.now(),
      status: json['status'] ?? 'Pending',
      paidDate: json['paid_date'] != null ? _parseDateTime(json['paid_date']) : null,
    );
  }
}

class AcademicSession {
  final int id;
  final String sessionName;
  final DateTime startDate;
  final DateTime endDate;
  final bool isActive;

  AcademicSession({
    required this.id,
    required this.sessionName,
    required this.startDate,
    required this.endDate,
    required this.isActive,
  });

  factory AcademicSession.fromJson(Map<String, dynamic> json) {
    return AcademicSession(
      id: _parseInt(json['id']),
      sessionName: json['session_name'] ?? '',
      startDate: _parseDateTime(json['start_date']) ?? DateTime.now(),
      endDate: _parseDateTime(json['end_date']) ?? DateTime.now(),
      isActive: json['is_active'] == true || json['is_active'] == 1,
    );
  }
}

class OutstandingFee {
  final int id;
  final String invoiceNo;
  final String feeTypes;
  final double totalAmount;
  final double paidAmount;
  final double discount;
  final double dueAmount;
  final DateTime dueDate;
  final String status;
  final DateTime createdAt;
  final String sessionName;

  OutstandingFee({
    required this.id,
    required this.invoiceNo,
    required this.feeTypes,
    required this.totalAmount,
    required this.paidAmount,
    required this.discount,
    required this.dueAmount,
    required this.dueDate,
    required this.status,
    required this.createdAt,
    required this.sessionName,
  });

  factory OutstandingFee.fromJson(Map<String, dynamic> json) {
    return OutstandingFee(
      id: _parseInt(json['id']),
      invoiceNo: json['invoice_no'] ?? '',
      feeTypes: json['fee_types'] ?? '',
      totalAmount: (json['total_amount'] is double)
          ? json['total_amount']
          : double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0.0,
      paidAmount: (json['paid_amount'] is double)
          ? json['paid_amount']
          : double.tryParse(json['paid_amount']?.toString() ?? '0') ?? 0.0,
      discount: (json['discount'] is double)
          ? json['discount']
          : double.tryParse(json['discount']?.toString() ?? '0') ?? 0.0,
      dueAmount: (json['due_amount'] is double)
          ? json['due_amount']
          : double.tryParse(json['due_amount']?.toString() ?? '0') ?? 0.0,
      dueDate: _parseDateTime(json['due_date']) ?? DateTime.now(),
      status: json['status'] ?? 'Unpaid',
      createdAt: _parseDateTime(json['created_at']) ?? DateTime.now(),
      sessionName: json['session_name'] ?? '',
    );
  }
}

class FinancialSummary {
  final double openingBalance;
  final double totalCharges;
  final double totalReceipts;
  final double closingBalance;

  FinancialSummary({
    required this.openingBalance,
    required this.totalCharges,
    required this.totalReceipts,
    required this.closingBalance,
  });

  factory FinancialSummary.fromJson(Map<String, dynamic> json) {
    return FinancialSummary(
      openingBalance: (json['opening_balance'] is double)
          ? json['opening_balance']
          : double.tryParse(json['opening_balance']?.toString() ?? '0') ?? 0.0,
      totalCharges: (json['total_charges'] is double)
          ? json['total_charges']
          : double.tryParse(json['total_charges']?.toString() ?? '0') ?? 0.0,
      totalReceipts: (json['total_receipts'] is double)
          ? json['total_receipts']
          : double.tryParse(json['total_receipts']?.toString() ?? '0') ?? 0.0,
      closingBalance: (json['closing_balance'] is double)
          ? json['closing_balance']
          : double.tryParse(json['closing_balance']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class FinancialStatementEntry {
  final int id;
  final DateTime transactionDate;
  final String transactionType;
  final double charge;
  final double receipt;
  final double? balance;
  final String description;
  final String? referenceType;
  final int? referenceId;
  final String? sessionName;

  FinancialStatementEntry({
    required this.id,
    required this.transactionDate,
    required this.transactionType,
    required this.charge,
    required this.receipt,
    this.balance,
    required this.description,
    this.referenceType,
    this.referenceId,
    this.sessionName,
  });

  factory FinancialStatementEntry.fromJson(Map<String, dynamic> json) {
    return FinancialStatementEntry(
      id: _parseInt(json['id']),
      transactionDate: _parseDateTime(json['transaction_date']) ?? DateTime.now(),
      transactionType: json['transaction_type'] ?? '',
      charge: (json['charge'] is double)
          ? json['charge']
          : double.tryParse(json['charge']?.toString() ?? '0') ?? 0.0,
      receipt: (json['receipt'] is double)
          ? json['receipt']
          : double.tryParse(json['receipt']?.toString() ?? '0') ?? 0.0,
      balance: json['balance'] != null
          ? ((json['balance'] is double)
              ? json['balance']
              : double.tryParse(json['balance']?.toString() ?? '0') ?? 0.0)
          : null,
      description: json['description'] ?? '',
      referenceType: json['reference_type'],
      referenceId: json['reference_id'] != null ? _parseInt(json['reference_id']) : null,
      sessionName: json['session_name'],
    );
  }
}

int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}

DateTime? _parseDateTime(dynamic value) {
  if (value == null) return null;
  if (value is DateTime) return value;
  if (value is String) {
    try {
      return DateTime.parse(value);
    } catch (e) {
      // Try alternative date formats
      try {
        // Try Y-m-d format
        if (value.contains('-') && value.length == 10) {
          return DateTime.parse('$value 00:00:00');
        }
      } catch (e2) {
        return null;
      }
      return null;
    }
  }
  return null;
}
