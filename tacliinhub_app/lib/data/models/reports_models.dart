class StudentReport {
  final int studentId;
  final String studentName;
  final String admissionNumber;
  final String className;
  final String sectionName;
  final String? parentName;
  final String? parentPhone;
  final String? parentEmail;
  final String admissionDate;
  final String? dateOfBirth;
  final String? gender;
  final String? address;
  final String status;
  final Map<String, dynamic>? academicSummary;
  final Map<String, dynamic>? attendanceSummary;
  final Map<String, dynamic>? feeSummary;

  StudentReport({
    required this.studentId,
    required this.studentName,
    required this.admissionNumber,
    required this.className,
    required this.sectionName,
    this.parentName,
    this.parentPhone,
    this.parentEmail,
    required this.admissionDate,
    this.dateOfBirth,
    this.gender,
    this.address,
    required this.status,
    this.academicSummary,
    this.attendanceSummary,
    this.feeSummary,
  });

  factory StudentReport.fromJson(Map<String, dynamic> json) {
    return StudentReport(
      studentId: json['student_id'] ?? json['id'] ?? 0,
      studentName: json['student_name'] ?? json['name'] ?? '',
      admissionNumber: json['admission_number'] ?? json['admission_no'] ?? '',
      className: json['class_name'] ?? '',
      sectionName: json['section_name'] ?? '',
      parentName: json['parent_name'],
      parentPhone: json['parent_phone'],
      parentEmail: json['parent_email'],
      admissionDate: json['admission_date'] ?? '',
      dateOfBirth: json['date_of_birth'] ?? json['dob'],
      gender: json['gender'],
      address: json['address'],
      status: json['status'] ?? 'Active',
      academicSummary: json['academic_summary'],
      attendanceSummary: json['attendance_summary'],
      feeSummary: json['fee_summary'],
    );
  }
}

class AcademicReport {
  final String reportType; // 'Class Performance', 'Subject Performance', 'Exam Results', 'Grade Distribution'
  final String? className;
  final String? subjectName;
  final String? examType;
  final DateTime? reportDate;
  final Map<String, dynamic> summary;
  final List<Map<String, dynamic>> details;

  AcademicReport({
    required this.reportType,
    this.className,
    this.subjectName,
    this.examType,
    this.reportDate,
    required this.summary,
    required this.details,
  });

  factory AcademicReport.fromJson(Map<String, dynamic> json) {
    return AcademicReport(
      reportType: json['report_type'] ?? '',
      className: json['class_name'],
      subjectName: json['subject_name'],
      examType: json['exam_type'],
      reportDate: json['report_date'] != null
          ? DateTime.tryParse(json['report_date'])
          : null,
      summary: json['summary'] ?? {},
      details: json['details'] is List
          ? List<Map<String, dynamic>>.from(json['details'])
          : [],
    );
  }
}

class FinancialReport {
  final String reportType; // 'Fee Collection', 'Income', 'Expenses', 'Balance Sheet', 'Profit & Loss'
  final DateTime? startDate;
  final DateTime? endDate;
  final Map<String, dynamic> summary;
  final List<Map<String, dynamic>> transactions;
  final double totalIncome;
  final double totalExpenses;
  final double balance;

  FinancialReport({
    required this.reportType,
    this.startDate,
    this.endDate,
    required this.summary,
    required this.transactions,
    required this.totalIncome,
    required this.totalExpenses,
    required this.balance,
  });

  factory FinancialReport.fromJson(Map<String, dynamic> json) {
    return FinancialReport(
      reportType: json['report_type'] ?? '',
      startDate: json['start_date'] != null
          ? DateTime.tryParse(json['start_date'])
          : null,
      endDate: json['end_date'] != null
          ? DateTime.tryParse(json['end_date'])
          : null,
      summary: json['summary'] ?? {},
      transactions: json['transactions'] is List
          ? List<Map<String, dynamic>>.from(json['transactions'])
          : [],
      totalIncome: (json['total_income'] ?? 0).toDouble(),
      totalExpenses: (json['total_expenses'] ?? 0).toDouble(),
      balance: (json['balance'] ?? 0).toDouble(),
    );
  }
}

class AttendanceReport {
  final String reportType; // 'Student Attendance', 'Staff Attendance', 'Class Attendance', 'Daily Attendance'
  final String? className;
  final String? studentName;
  final String? staffName;
  final DateTime? startDate;
  final DateTime? endDate;
  final Map<String, dynamic> summary;
  final List<Map<String, dynamic>> details;

  AttendanceReport({
    required this.reportType,
    this.className,
    this.studentName,
    this.staffName,
    this.startDate,
    this.endDate,
    required this.summary,
    required this.details,
  });

  factory AttendanceReport.fromJson(Map<String, dynamic> json) {
    return AttendanceReport(
      reportType: json['report_type'] ?? '',
      className: json['class_name'],
      studentName: json['student_name'],
      staffName: json['staff_name'],
      startDate: json['start_date'] != null
          ? DateTime.tryParse(json['start_date'])
          : null,
      endDate: json['end_date'] != null
          ? DateTime.tryParse(json['end_date'])
          : null,
      summary: json['summary'] ?? {},
      details: json['details'] is List
          ? List<Map<String, dynamic>>.from(json['details'])
          : [],
    );
  }
}

class CustomReport {
  final int id;
  final String name;
  final String description;
  final String reportType;
  final Map<String, dynamic> parameters;
  final String? sqlQuery;
  final String createdBy;
  final String createdAt;
  final String? updatedAt;

  CustomReport({
    required this.id,
    required this.name,
    required this.description,
    required this.reportType,
    required this.parameters,
    this.sqlQuery,
    required this.createdBy,
    required this.createdAt,
    this.updatedAt,
  });

  factory CustomReport.fromJson(Map<String, dynamic> json) {
    return CustomReport(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      description: json['description'] ?? '',
      reportType: json['report_type'] ?? '',
      parameters: json['parameters'] is Map
          ? Map<String, dynamic>.from(json['parameters'])
          : {},
      sqlQuery: json['sql_query'],
      createdBy: json['created_by'] ?? '',
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }
}

