/// Examination Models for TacliinHub
library;

class ExamType {
  final int id;
  final String examName;
  final String examCode;
  final String? description;

  ExamType({
    required this.id,
    required this.examName,
    required this.examCode,
    this.description,
  });

  factory ExamType.fromJson(Map<String, dynamic> json) {
    return ExamType(
      id: _parseInt(json['id']),
      examName: json['exam_name'] ?? '',
      examCode: json['exam_code'] ?? '',
      description: json['description'],
    );
  }
}

class Exam {
  final int id;
  final int examTypeId;
  final String examTypeName;
  final String examName;
  final int classId;
  final String className;
  final int sessionId;
  final DateTime startDate;
  final DateTime endDate;
  final String? description;
  final DateTime createdAt;

  Exam({
    required this.id,
    required this.examTypeId,
    required this.examTypeName,
    required this.examName,
    required this.classId,
    required this.className,
    required this.sessionId,
    required this.startDate,
    required this.endDate,
    this.description,
    required this.createdAt,
  });

  factory Exam.fromJson(Map<String, dynamic> json) {
    return Exam(
      id: _parseInt(json['id']),
      examTypeId: _parseInt(json['exam_type_id']),
      examTypeName: json['exam_type_name'] ?? json['exam_name'] ?? '',
      examName: json['exam_name'] ?? '',
      classId: _parseInt(json['class_id']),
      className: json['class_name'] ?? '',
      sessionId: _parseInt(json['session_id']),
      startDate: DateTime.parse(json['start_date']),
      endDate: DateTime.parse(json['end_date']),
      description: json['description'],
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
    );
  }
}

class ExamSchedule {
  final int id;
  final int examId;
  final String examName;
  final int subjectId;
  final String subjectName;
  final String? subjectCode;
  final int classId;
  final String? className;
  final DateTime examDate;
  final String startTime;
  final String endTime;
  final String? roomNo;
  final double totalMarks;
  final double passingMarks;

  ExamSchedule({
    required this.id,
    required this.examId,
    required this.examName,
    required this.subjectId,
    required this.subjectName,
    this.subjectCode,
    required this.classId,
    this.className,
    required this.examDate,
    required this.startTime,
    required this.endTime,
    this.roomNo,
    required this.totalMarks,
    required this.passingMarks,
  });

  factory ExamSchedule.fromJson(Map<String, dynamic> json) {
    return ExamSchedule(
      id: _parseInt(json['id']),
      examId: _parseInt(json['exam_id']),
      examName: json['exam_name'] ?? '',
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'],
      classId: _parseInt(json['class_id']),
      className: json['class_name'],
      examDate: DateTime.parse(json['exam_date']),
      startTime: json['start_time'] ?? '00:00:00',
      endTime: json['end_time'] ?? '00:00:00',
      roomNo: json['room_no'],
      totalMarks: double.tryParse(json['total_marks']?.toString() ?? '100') ?? 100.0,
      passingMarks: double.tryParse(json['passing_marks']?.toString() ?? '40') ?? 40.0,
    );
  }
}

class StudentMark {
  final int id;
  final int studentId;
  final String studentName;
  final String? studentIdNumber;
  final int examScheduleId;
  final double? marksObtained;
  final bool isAbsent;
  final String? remarks;
  final int? enteredBy;
  final String? enteredByName;
  final DateTime? enteredAt;
  final DateTime? updatedAt;

  StudentMark({
    required this.id,
    required this.studentId,
    required this.studentName,
    this.studentIdNumber,
    required this.examScheduleId,
    this.marksObtained,
    required this.isAbsent,
    this.remarks,
    this.enteredBy,
    this.enteredByName,
    this.enteredAt,
    this.updatedAt,
  });

  factory StudentMark.fromJson(Map<String, dynamic> json) {
    return StudentMark(
      id: _parseInt(json['id']),
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? 
                   '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim(),
      studentIdNumber: json['student_id_number'] ?? json['admission_no'],
      examScheduleId: _parseInt(json['exam_schedule_id']),
      marksObtained: json['marks_obtained'] != null 
          ? double.tryParse(json['marks_obtained'].toString()) 
          : null,
      isAbsent: (json['is_absent'] ?? 0) == 1,
      remarks: json['remarks'],
      enteredBy: json['entered_by'] != null ? _parseInt(json['entered_by']) : null,
      enteredByName: json['entered_by_name'],
      enteredAt: json['entered_at'] != null ? DateTime.tryParse(json['entered_at']) : null,
      updatedAt: json['updated_at'] != null ? DateTime.tryParse(json['updated_at']) : null,
    );
  }
}

class ExamResult {
  final int examId;
  final String examName;
  final int studentId;
  final String studentName;
  final int classId;
  final String className;
  final List<SubjectResult> subjects;
  final double totalMarks;
  final double obtainedMarks;
  final double percentage;
  final String grade;
  final int rank;

  ExamResult({
    required this.examId,
    required this.examName,
    required this.studentId,
    required this.studentName,
    required this.classId,
    required this.className,
    required this.subjects,
    required this.totalMarks,
    required this.obtainedMarks,
    required this.percentage,
    required this.grade,
    required this.rank,
  });

  factory ExamResult.fromJson(Map<String, dynamic> json) {
    return ExamResult(
      examId: _parseInt(json['exam_id']),
      examName: json['exam_name'] ?? '',
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? '',
      classId: _parseInt(json['class_id']),
      className: json['class_name'] ?? '',
      subjects: (json['subjects'] as List<dynamic>?)
          ?.map((s) => SubjectResult.fromJson(s))
          .toList() ?? [],
      totalMarks: double.tryParse(json['total_marks']?.toString() ?? '0') ?? 0.0,
      obtainedMarks: double.tryParse(json['obtained_marks']?.toString() ?? '0') ?? 0.0,
      percentage: double.tryParse(json['percentage']?.toString() ?? '0') ?? 0.0,
      grade: json['grade'] ?? 'N/A',
      rank: _parseInt(json['rank']),
    );
  }
}

class SubjectResult {
  final int subjectId;
  final String subjectName;
  final String? subjectCode;
  final double totalMarks;
  final double obtainedMarks;
  final double percentage;
  final String grade;
  final bool isPass;

  SubjectResult({
    required this.subjectId,
    required this.subjectName,
    this.subjectCode,
    required this.totalMarks,
    required this.obtainedMarks,
    required this.percentage,
    required this.grade,
    required this.isPass,
  });

  factory SubjectResult.fromJson(Map<String, dynamic> json) {
    return SubjectResult(
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'],
      totalMarks: double.tryParse(json['total_marks']?.toString() ?? '0') ?? 0.0,
      obtainedMarks: double.tryParse(json['obtained_marks']?.toString() ?? '0') ?? 0.0,
      percentage: double.tryParse(json['percentage']?.toString() ?? '0') ?? 0.0,
      grade: json['grade'] ?? 'N/A',
      isPass: json['is_pass'] == true || (json['is_pass'] == 1),
    );
  }
}

class ExamAnalytics {
  final int totalExams;
  final int completedExams;
  final int pendingExams;
  final double averageMarks;
  final double passRate;
  final List<ClassPerformance> classPerformance;
  final List<SubjectPerformance> subjectPerformance;

  ExamAnalytics({
    required this.totalExams,
    required this.completedExams,
    required this.pendingExams,
    required this.averageMarks,
    required this.passRate,
    required this.classPerformance,
    required this.subjectPerformance,
  });

  factory ExamAnalytics.fromJson(Map<String, dynamic> json) {
    return ExamAnalytics(
      totalExams: _parseInt(json['total_exams']),
      completedExams: _parseInt(json['completed_exams']),
      pendingExams: _parseInt(json['pending_exams']),
      averageMarks: double.tryParse(json['average_marks']?.toString() ?? '0') ?? 0.0,
      passRate: double.tryParse(json['pass_rate']?.toString() ?? '0') ?? 0.0,
      classPerformance: (json['class_performance'] as List<dynamic>?)
          ?.map((c) => ClassPerformance.fromJson(c))
          .toList() ?? [],
      subjectPerformance: (json['subject_performance'] as List<dynamic>?)
          ?.map((s) => SubjectPerformance.fromJson(s))
          .toList() ?? [],
    );
  }
}

class ClassPerformance {
  final int classId;
  final String className;
  final double averageMarks;
  final double passRate;
  final int totalStudents;
  final int passedStudents;

  ClassPerformance({
    required this.classId,
    required this.className,
    required this.averageMarks,
    required this.passRate,
    required this.totalStudents,
    required this.passedStudents,
  });

  factory ClassPerformance.fromJson(Map<String, dynamic> json) {
    return ClassPerformance(
      classId: _parseInt(json['class_id']),
      className: json['class_name'] ?? '',
      averageMarks: double.tryParse(json['average_marks']?.toString() ?? '0') ?? 0.0,
      passRate: double.tryParse(json['pass_rate']?.toString() ?? '0') ?? 0.0,
      totalStudents: _parseInt(json['total_students']),
      passedStudents: _parseInt(json['passed_students']),
    );
  }
}

class SubjectPerformance {
  final int subjectId;
  final String subjectName;
  final double averageMarks;
  final double passRate;
  final int totalStudents;
  final int passedStudents;

  SubjectPerformance({
    required this.subjectId,
    required this.subjectName,
    required this.averageMarks,
    required this.passRate,
    required this.totalStudents,
    required this.passedStudents,
  });

  factory SubjectPerformance.fromJson(Map<String, dynamic> json) {
    return SubjectPerformance(
      subjectId: _parseInt(json['subject_id']),
      subjectName: json['subject_name'] ?? '',
      averageMarks: double.tryParse(json['average_marks']?.toString() ?? '0') ?? 0.0,
      passRate: double.tryParse(json['pass_rate']?.toString() ?? '0') ?? 0.0,
      totalStudents: _parseInt(json['total_students']),
      passedStudents: _parseInt(json['passed_students']),
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

