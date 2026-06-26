import 'package:flutter/material.dart';
import '../../data/repositories/student_repository.dart';
import '../../data/models/student_models.dart';

class StudentPortalProvider with ChangeNotifier {
  final StudentRepository _repository = StudentRepository();

  // State
  StudentProfile? _profile;
  Map<String, dynamic> _dashboardStats = {};
  List<StudentClass> _classes = [];
  List<StudentTimetable> _timetable = [];
  List<StudentAttendance> _attendance = [];
  Map<String, dynamic>? _attendanceStats;
  List<StudentMark> _marks = [];
  List<StudentFee> _fees = [];
  Map<String, dynamic>? _financialStatement;

  bool _isLoading = false;
  String? _error;

  // Getters
  StudentProfile? get profile => _profile;
  Map<String, dynamic> get dashboardStats => _dashboardStats;
  List<StudentClass> get classes => _classes;
  List<StudentTimetable> get timetable => _timetable;
  List<StudentAttendance> get attendance => _attendance;
  Map<String, dynamic>? get attendanceStats => _attendanceStats;
  List<StudentMark> get marks => _marks;
  List<StudentFee> get fees => _fees;
  Map<String, dynamic>? get financialStatement => _financialStatement;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Load student profile
  Future<void> loadProfile({required int userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _profile = await _repository.getStudentProfile(userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _profile = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load dashboard stats
  Future<void> loadDashboardStats({required int userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _dashboardStats = await _repository.getDashboardStats(userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _dashboardStats = {};
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load classes
  Future<void> loadClasses({required int userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _classes = await _repository.getStudentClasses(userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _classes = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load timetable
  Future<void> loadTimetable({required int userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _timetable = await _repository.getStudentTimetable(userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _timetable = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load attendance
  Future<void> loadAttendance({
    required int userId,
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final attendanceData = await _repository.getStudentAttendance(
        userId: userId,
        startDate: startDate,
        endDate: endDate,
      );
      
      // Parse records
      if (attendanceData['records'] != null) {
        final List<dynamic> recordsJson = attendanceData['records'];
        _attendance = recordsJson.map((json) => StudentAttendance.fromJson(json)).toList();
      } else {
        _attendance = [];
      }
      
      // Store statistics
      _attendanceStats = attendanceData;
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _attendance = [];
      _attendanceStats = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load marks
  Future<void> loadMarks({required int userId, int? examId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _marks = await _repository.getStudentMarks(userId: userId, examId: examId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _marks = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load fees
  Future<void> loadFees({required int userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _fees = await _repository.getStudentFees(userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _fees = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Load financial statement
  Future<void> loadFinancialStatement({
    required int userId,
    int? sessionId,
    DateTime? dateFrom,
    DateTime? dateTo,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _financialStatement = await _repository.getFinancialStatement(
        userId: userId,
        sessionId: sessionId,
        dateFrom: dateFrom,
        dateTo: dateTo,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _financialStatement = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Update profile
  Future<bool> updateProfile({
    required int userId,
    String? email,
    String? phone,
    String? address,
    String? city,
    String? state,
    String? postalCode,
    String? photoPath,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _profile = await _repository.updateProfile(
        userId: userId,
        email: email,
        phone: phone,
        address: address,
        city: city,
        state: state,
        postalCode: postalCode,
        photoPath: photoPath,
      );
      _error = null;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // Clear all data
  void clear() {
    _profile = null;
    _dashboardStats = {};
    _classes = [];
    _timetable = [];
    _attendance = [];
    _attendanceStats = null;
    _marks = [];
    _fees = [];
    _financialStatement = null;
    _error = null;
    notifyListeners();
  }
}

