import 'package:flutter/material.dart';
import '../../data/repositories/examination_repository.dart';
import '../../data/models/examination_models.dart';

class ExaminationProvider with ChangeNotifier {
  final ExaminationRepository _repository = ExaminationRepository();
  int? _currentUserId;

  List<ExamType> _examTypes = [];
  List<Exam> _exams = [];
  List<ExamSchedule> _examSchedules = [];
  List<StudentMark> _studentMarks = [];
  List<ExamResult> _examResults = [];
  ExamAnalytics? _analytics;
  bool _isLoading = false;
  String? _error;

  List<ExamType> get examTypes => _examTypes;
  List<Exam> get exams => _exams;
  List<ExamSchedule> get examSchedules => _examSchedules;
  List<StudentMark> get studentMarks => _studentMarks;
  List<ExamResult> get examResults => _examResults;
  ExamAnalytics? get analytics => _analytics;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Load exam types
  Future<void> loadExamTypes() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _examTypes = await _repository.getExamTypes();
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _examTypes = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load exams
  Future<void> loadExams({
    required int userId,
    int? classId,
    int? sessionId,
  }) async {
    _isLoading = true;
    _error = null;
    _currentUserId = userId;
    notifyListeners();

    try {
      _exams = await _repository.getExams(
        userId: userId,
        classId: classId,
        sessionId: sessionId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _exams = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load exam schedules
  Future<void> loadExamSchedules({
    required int userId,
    int? examId,
    int? classId,
    int? subjectId,
  }) async {
    _isLoading = true;
    _error = null;
    _currentUserId = userId;
    notifyListeners();

    try {
      _examSchedules = await _repository.getExamSchedules(
        userId: userId,
        examId: examId,
        classId: classId,
        subjectId: subjectId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _examSchedules = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load student marks
  Future<void> loadStudentMarks({
    required int userId,
    required int examScheduleId,
  }) async {
    _isLoading = true;
    _error = null;
    _currentUserId = userId;
    notifyListeners();

    try {
      _studentMarks = await _repository.getStudentMarks(
        userId: userId,
        examScheduleId: examScheduleId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _studentMarks = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Save marks
  Future<bool> saveMarks({
    required int userId,
    required int examScheduleId,
    required List<Map<String, dynamic>> students,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.saveMarks(
        userId: userId,
        examScheduleId: examScheduleId,
        students: students,
      );
      if (success && _currentUserId != null) {
        await loadStudentMarks(userId: userId, examScheduleId: examScheduleId);
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

  /// Load exam results
  Future<void> loadExamResults({
    required int userId,
    int? examId,
    int? studentId,
    int? classId,
  }) async {
    _isLoading = true;
    _error = null;
    _currentUserId = userId;
    notifyListeners();

    try {
      _examResults = await _repository.getExamResults(
        userId: userId,
        examId: examId,
        studentId: studentId,
        classId: classId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _examResults = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load analytics
  Future<void> loadAnalytics({
    required int userId,
    int? examId,
    int? classId,
  }) async {
    _isLoading = true;
    _error = null;
    _currentUserId = userId;
    notifyListeners();

    try {
      _analytics = await _repository.getExamAnalytics(
        userId: userId,
        examId: examId,
        classId: classId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _analytics = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Create exam
  Future<bool> createExam({
    required int userId,
    required int examTypeId,
    required String examName,
    required int classId,
    required DateTime startDate,
    required DateTime endDate,
    String? description,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.createExam(
        userId: userId,
        examTypeId: examTypeId,
        examName: examName,
        classId: classId,
        startDate: startDate,
        endDate: endDate,
        description: description,
      );
      if (success) {
        await loadExams(userId: userId);
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

  /// Create exam schedule
  Future<bool> createExamSchedule({
    required int userId,
    required int examId,
    required int subjectId,
    required DateTime examDate,
    required String startTime,
    required String endTime,
    String? roomNo,
    required double totalMarks,
    required double passingMarks,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.createExamSchedule(
        userId: userId,
        examId: examId,
        subjectId: subjectId,
        examDate: examDate,
        startTime: startTime,
        endTime: endTime,
        roomNo: roomNo,
        totalMarks: totalMarks,
        passingMarks: passingMarks,
      );
      if (success) {
        await loadExamSchedules(userId: userId, examId: examId);
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

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

