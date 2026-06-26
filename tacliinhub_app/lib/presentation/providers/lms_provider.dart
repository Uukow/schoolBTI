import 'package:flutter/material.dart';
import '../../data/repositories/lms_repository.dart';
import '../../data/models/lms_models.dart';

class LmsProvider with ChangeNotifier {
  final LmsRepository _repository = LmsRepository();

  List<StudyMaterial> _studyMaterials = [];
  List<Assignment> _assignments = [];
  List<Quiz> _quizzes = [];
  List<QuizQuestion> _quizQuestions = [];
  List<QuizAttempt> _quizAttempts = [];
  bool _isLoading = false;
  String? _error;

  List<StudyMaterial> get studyMaterials => _studyMaterials;
  List<Assignment> get assignments => _assignments;
  List<Quiz> get quizzes => _quizzes;
  List<QuizQuestion> get quizQuestions => _quizQuestions;
  List<QuizAttempt> get quizAttempts => _quizAttempts;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // ========== STUDY MATERIALS ==========
  Future<void> loadStudyMaterials({int? userId, int? classId, int? subjectId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _studyMaterials = await _repository.getStudyMaterials(
        userId: userId,
        classId: classId,
        subjectId: subjectId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> addStudyMaterial({
    required String title,
    required String description,
    int? classId,
    int? subjectId,
    String? fileUrl,
    String? fileType,
    int? fileSize,
    String? tags,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addStudyMaterial(
        title: title,
        description: description,
        classId: classId,
        subjectId: subjectId,
        fileUrl: fileUrl,
        fileType: fileType,
        fileSize: fileSize,
        tags: tags,
        userId: userId,
      );

      if (success) {
        await loadStudyMaterials(userId: userId, classId: classId, subjectId: subjectId);
      }

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // ========== ASSIGNMENTS ==========
  Future<void> loadAssignments({int? userId, int? classId, int? subjectId, String? status}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _assignments = await _repository.getAssignments(
        userId: userId,
        classId: classId,
        subjectId: subjectId,
        status: status,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> addAssignment({
    required String title,
    required String description,
    required int classId,
    int? subjectId,
    required String dueDate,
    double? maxMarks,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addAssignment(
        title: title,
        description: description,
        classId: classId,
        subjectId: subjectId,
        dueDate: dueDate,
        maxMarks: maxMarks,
        userId: userId,
      );

      if (success) {
        await loadAssignments(userId: userId, classId: classId, subjectId: subjectId);
      }

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // ========== QUIZZES ==========
  Future<void> loadQuizzes({int? userId, int? classId, int? subjectId, String? status}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _quizzes = await _repository.getQuizzes(
        userId: userId,
        classId: classId,
        subjectId: subjectId,
        status: status,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadQuizQuestions(int quizId, {int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _quizQuestions = await _repository.getQuizQuestions(quizId, userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> addQuiz({
    required String title,
    required String description,
    required int classId,
    int? subjectId,
    required int durationMinutes,
    required double totalMarks,
    required String startDate,
    String? endDate,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addQuiz(
        title: title,
        description: description,
        classId: classId,
        subjectId: subjectId,
        durationMinutes: durationMinutes,
        totalMarks: totalMarks,
        startDate: startDate,
        endDate: endDate,
        userId: userId,
      );

      if (success) {
        await loadQuizzes(userId: userId, classId: classId, subjectId: subjectId);
      }

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> addQuizQuestion({
    required int quizId,
    required String question,
    required String questionType,
    required List<String> options,
    String? correctAnswer,
    required double marks,
    required int order,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addQuizQuestion(
        quizId: quizId,
        question: question,
        questionType: questionType,
        options: options,
        correctAnswer: correctAnswer,
        marks: marks,
        order: order,
        userId: userId,
      );

      if (success) {
        await loadQuizQuestions(quizId, userId: userId);
      }

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> startQuizAttempt({
    required int quizId,
    required int studentId,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.startQuizAttempt(
        quizId: quizId,
        studentId: studentId,
        userId: userId,
      );

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> submitQuizAttempt({
    required int attemptId,
    required Map<String, String> answers,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.submitQuizAttempt(
        attemptId: attemptId,
        answers: answers,
        userId: userId,
      );

      if (success) {
        await loadQuizAttempts(userId: userId);
      }

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> loadQuizAttempts({int? quizId, int? studentId, int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _quizAttempts = await _repository.getQuizAttempts(
        quizId: quizId,
        studentId: studentId,
        userId: userId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}

