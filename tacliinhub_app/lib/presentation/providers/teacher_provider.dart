import 'package:flutter/foundation.dart';
import '../../data/models/teacher_models.dart';
import '../../data/repositories/teacher_repository.dart';

class TeacherProvider with ChangeNotifier {
  final TeacherRepository _repository = TeacherRepository();

  bool _isLoading = false;
  String? _error;
  TeacherStats? _stats;
  List<TeacherClass> _classes = [];
  List<TeacherStudent> _students = [];
  List<TeacherTimetable> _timetable = [];
  List<TeacherLessonPlan> _lessonPlans = [];

  bool get isLoading => _isLoading;
  String? get error => _error;
  TeacherStats? get stats => _stats;
  List<TeacherClass> get classes => _classes;
  List<TeacherStudent> get students => _students;
  List<TeacherTimetable> get timetable => _timetable;
  List<TeacherLessonPlan> get lessonPlans => _lessonPlans;

  Future<void> loadStats(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _stats = await _repository.getTeacherStats(userId);
      _error = null;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error loading teacher stats: $e');
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadClasses(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _classes = await _repository.getMyClasses(userId);
      _error = null;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error loading classes: $e');
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadStudents(int userId, {int? classId, int? subjectId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _students = await _repository.getMyStudents(
        userId: userId,
        classId: classId,
        subjectId: subjectId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error loading students: $e');
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadTimetable(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _timetable = await _repository.getMyTimetable(userId);
      _error = null;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error loading timetable: $e');
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadLessonPlans(int userId, {int? classId, int? subjectId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _lessonPlans = await _repository.getMyLessonPlans(
        userId: userId,
        classId: classId,
        subjectId: subjectId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error loading lesson plans: $e');
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> saveAttendance({
    required int userId,
    required String date,
    required int classId,
    required int subjectId,
    required Map<int, String> attendance,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.saveAttendance(
        userId: userId,
        date: date,
        classId: classId,
        subjectId: subjectId,
        attendance: attendance,
      );
      if (success) {
        await loadStats(userId); // Refresh stats
      }
      return success;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error saving attendance: $e');
      }
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> saveMarks({
    required int userId,
    required int examScheduleId,
    required Map<int, double> marks,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.saveMarks(
        userId: userId,
        examScheduleId: examScheduleId,
        marks: marks,
      );
      if (success) {
        await loadStats(userId); // Refresh stats
      }
      return success;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error saving marks: $e');
      }
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> saveLessonPlan({
    required int userId,
    required String title,
    required String date,
    required int classId,
    required int subjectId,
    String? objectives,
    String? activities,
    String? materials,
    String? homework,
    String? notes,
    int? id,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.saveLessonPlan(
        userId: userId,
        title: title,
        date: date,
        classId: classId,
        subjectId: subjectId,
        objectives: objectives,
        activities: activities,
        materials: materials,
        homework: homework,
        notes: notes,
        id: id,
      );
      if (success) {
        await loadLessonPlans(userId); // Refresh lesson plans
        await loadStats(userId); // Refresh stats
      }
      return success;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error saving lesson plan: $e');
      }
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

