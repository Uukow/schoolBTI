import 'package:flutter/material.dart';
import '../../data/repositories/academic_repository.dart';
import '../../data/models/academic_models.dart';

class AcademicProvider with ChangeNotifier {
  final AcademicRepository _repository = AcademicRepository();

  List<Subject> _subjects = [];
  List<ClassAssignment> _assignments = [];
  List<TimetablePeriod> _timetable = [];
  List<LessonPlan> _lessonPlans = [];
  List<Syllabus> _syllabus = [];
  List<AcademicCalendar> _calendarEvents = [];
  bool _isLoading = false;
  String? _error;

  List<Subject> get subjects => _subjects;
  List<ClassAssignment> get assignments => _assignments;
  List<TimetablePeriod> get timetable => _timetable;
  List<LessonPlan> get lessonPlans => _lessonPlans;
  List<Syllabus> get syllabus => _syllabus;
  List<AcademicCalendar> get calendarEvents => _calendarEvents;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Load all subjects
  Future<void> loadSubjects(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _subjects = await _repository.getSubjects(userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load assignments for a class
  Future<void> loadClassAssignments(int classId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _assignments = await _repository.getClassAssignments(classId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _assignments = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load timetable
  Future<void> loadTimetable({
    required int classId,
    required int sectionId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _timetable = await _repository.getTimetable(
        classId: classId,
        sectionId: sectionId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _timetable = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load lesson plans
  Future<void> loadLessonPlans({
    int? classId,
    int? subjectId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _lessonPlans = await _repository.getLessonPlans(
        classId: classId,
        subjectId: subjectId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _lessonPlans = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load syllabus
  Future<void> loadSyllabus({
    int? classId,
    int? subjectId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _syllabus = await _repository.getSyllabus(
        classId: classId,
        subjectId: subjectId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _syllabus = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load academic calendar
  Future<void> loadAcademicCalendar({
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _calendarEvents = await _repository.getAcademicCalendar(
        startDate: startDate,
        endDate: endDate,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _calendarEvents = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Create assignment
  Future<bool> createAssignment({
    required int classId,
    required int subjectId,
    required String title,
    String? description,
    required String dueDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _repository.createAssignment(
        classId: classId,
        subjectId: subjectId,
        title: title,
        description: description,
        dueDate: dueDate,
      );
      _error = null;
      // Reload assignments for the class
      await loadClassAssignments(classId);
      return true;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Create lesson plan
  Future<bool> createLessonPlan({
    required int classId,
    required int subjectId,
    required String topic,
    required String objectives,
    required String content,
    String? teachingMethods,
    String? assessment,
    required String date,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _repository.createLessonPlan(
        classId: classId,
        subjectId: subjectId,
        topic: topic,
        objectives: objectives,
        content: content,
        teachingMethods: teachingMethods,
        assessment: assessment,
        date: date,
      );
      _error = null;
      // Reload lesson plans
      await loadLessonPlans();
      return true;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Create syllabus
  Future<bool> createSyllabus({
    required int classId,
    required int subjectId,
    required String title,
    required String description,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _repository.createSyllabus(
        classId: classId,
        subjectId: subjectId,
        title: title,
        description: description,
      );
      _error = null;
      // Reload syllabus
      await loadSyllabus();
      return true;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Create calendar event
  Future<bool> createCalendarEvent({
    required String title,
    required String eventType,
    String? description,
    required String startDate,
    required String endDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      await _repository.createCalendarEvent(
        title: title,
        eventType: eventType,
        description: description,
        startDate: startDate,
        endDate: endDate,
      );
      _error = null;
      // Reload calendar events
      final now = DateTime.now();
      await loadAcademicCalendar(
        startDate: DateTime(now.year, 1, 1),
        endDate: DateTime(now.year, 12, 31),
      );
      return true;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}

