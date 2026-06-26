import 'package:flutter/material.dart';
import '../../data/repositories/attendance_repository.dart';
import '../../data/models/attendance_models.dart';

class AttendanceProvider with ChangeNotifier {
  final AttendanceRepository _repository = AttendanceRepository();

  List<StudentAttendance> _studentAttendance = [];
  List<StaffAttendance> _staffAttendance = [];
  List<StudentAttendance> _attendanceHistory = [];
  AttendanceStats? _stats;
  bool _isLoading = false;
  String? _error;

  List<StudentAttendance> get studentAttendance => _studentAttendance;
  List<StaffAttendance> get staffAttendance => _staffAttendance;
  List<StudentAttendance> get attendanceHistory => _attendanceHistory;
  AttendanceStats? get stats => _stats;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Load student attendance for a class and date
  Future<void> loadStudentAttendance({
    required int classId,
    required int sectionId,
    required String date,
    int? subjectId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _studentAttendance = await _repository.getStudentAttendance(
        classId: classId,
        sectionId: sectionId,
        date: date,
        subjectId: subjectId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _studentAttendance = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load staff attendance for a date
  Future<void> loadStaffAttendance({
    required String date,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _staffAttendance = await _repository.getStaffAttendance(date: date);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _staffAttendance = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load attendance statistics
  Future<void> loadStats({
    int? studentId,
    int? staffId,
    String? startDate,
    String? endDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _stats = await _repository.getAttendanceStats(
        studentId: studentId,
        staffId: staffId,
        startDate: startDate,
        endDate: endDate,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _stats = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Save student attendance
  Future<bool> saveStudentAttendance({
    required int classId,
    required int sectionId,
    required String date,
    required List<Map<String, dynamic>> students,
    int? subjectId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.saveStudentAttendance(
        classId: classId,
        sectionId: sectionId,
        date: date,
        students: students,
        subjectId: subjectId,
      );
      if (success) {
        // Reload attendance after saving
        await loadStudentAttendance(
          classId: classId,
          sectionId: sectionId,
          date: date,
          subjectId: subjectId,
        );
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Save staff attendance
  Future<bool> saveStaffAttendance({
    required String date,
    required List<Map<String, dynamic>> staff,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.saveStaffAttendance(
        date: date,
        staff: staff,
      );
      if (success) {
        // Reload attendance after saving
        await loadStaffAttendance(date: date);
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load attendance history
  Future<void> loadAttendanceHistory({
    int? studentId,
    int? classId,
    String? startDate,
    String? endDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _attendanceHistory = await _repository.getAttendanceHistory(
        studentId: studentId,
        classId: classId,
        startDate: startDate,
        endDate: endDate,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _attendanceHistory = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

