import 'package:flutter/material.dart';
import '../../data/repositories/reports_repository.dart';
import '../../data/models/reports_models.dart';

class ReportsProvider with ChangeNotifier {
  final ReportsRepository _repository = ReportsRepository();

  List<StudentReport> _studentReports = [];
  AcademicReport? _academicReport;
  FinancialReport? _financialReport;
  AttendanceReport? _attendanceReport;
  List<CustomReport> _customReports = [];
  Map<String, dynamic>? _customReportResult;

  bool _isLoading = false;
  String? _error;

  List<StudentReport> get studentReports => _studentReports;
  AcademicReport? get academicReport => _academicReport;
  FinancialReport? get financialReport => _financialReport;
  AttendanceReport? get attendanceReport => _attendanceReport;
  List<CustomReport> get customReports => _customReports;
  Map<String, dynamic>? get customReportResult => _customReportResult;
  bool get isLoading => _isLoading;
  String? get error => _error;

  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _setError(String? message) {
    _error = message;
    notifyListeners();
  }

  // Student Reports
  Future<void> loadStudentReports({
    int? studentId,
    int? classId,
    int? sectionId,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
    int? userId,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      _studentReports = await _repository.getStudentReports(
        studentId: studentId,
        classId: classId,
        sectionId: sectionId,
        status: status,
        startDate: startDate,
        endDate: endDate,
        userId: userId,
      );
    } catch (e) {
      _setError(e.toString().replaceAll('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  // Academic Reports
  Future<void> loadAcademicReport({
    required String reportType,
    int? classId,
    int? subjectId,
    int? examId,
    DateTime? startDate,
    DateTime? endDate,
    int? userId,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      _academicReport = await _repository.getAcademicReport(
        reportType: reportType,
        classId: classId,
        subjectId: subjectId,
        examId: examId,
        startDate: startDate,
        endDate: endDate,
        userId: userId,
      );
    } catch (e) {
      _setError(e.toString().replaceAll('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  // Financial Reports
  Future<void> loadFinancialReport({
    required String reportType,
    DateTime? startDate,
    DateTime? endDate,
    int? userId,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      _financialReport = await _repository.getFinancialReport(
        reportType: reportType,
        startDate: startDate,
        endDate: endDate,
        userId: userId,
      );
    } catch (e) {
      _setError(e.toString().replaceAll('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  // Attendance Reports
  Future<void> loadAttendanceReport({
    required String reportType,
    int? classId,
    int? studentId,
    int? staffId,
    DateTime? startDate,
    DateTime? endDate,
    int? userId,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      _attendanceReport = await _repository.getAttendanceReport(
        reportType: reportType,
        classId: classId,
        studentId: studentId,
        staffId: staffId,
        startDate: startDate,
        endDate: endDate,
        userId: userId,
      );
    } catch (e) {
      _setError(e.toString().replaceAll('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  // Custom Reports
  Future<void> loadCustomReports({int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      _customReports = await _repository.getCustomReports(userId: userId);
    } catch (e) {
      _setError(e.toString().replaceAll('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  Future<void> executeCustomReport({
    required int reportId,
    Map<String, dynamic>? parameters,
    int? userId,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      _customReportResult = await _repository.executeCustomReport(
        reportId: reportId,
        parameters: parameters,
        userId: userId,
      );
    } catch (e) {
      _setError(e.toString().replaceAll('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  void clearReports() {
    _studentReports = [];
    _academicReport = null;
    _financialReport = null;
    _attendanceReport = null;
    _customReportResult = null;
    notifyListeners();
  }
}

