import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/lms_models.dart';

class LmsRepository {
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');

  // ========== STUDY MATERIALS ==========
  Future<List<StudyMaterial>> getStudyMaterials({
    int? userId,
    int? classId,
    int? subjectId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/get-study-materials.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();

      final fullUri = uri.replace(queryParameters: queryParams);
      print('LMS Repository: Loading study materials from $fullUri');

      final response = await http.get(fullUri);

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') || response.body.trim().startsWith('<html')) {
          throw Exception('Server returned HTML instead of JSON. Check PHP errors.');
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> materialsJson = data['data'] ?? [];
          return materialsJson.map((json) => StudyMaterial.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load study materials');
        }
      } else if (response.statusCode == 404) {
        throw Exception('API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/lms/get-study-materials.php');
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load study materials: $e');
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
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/add-study-material.php');
      final body = {
        'title': title,
        'description': description,
        if (classId != null) 'class_id': classId,
        if (subjectId != null) 'subject_id': subjectId,
        if (fileUrl != null) 'file_url': fileUrl,
        if (fileType != null) 'file_type': fileType,
        if (fileSize != null) 'file_size': fileSize,
        if (tags != null) 'tags': tags,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add study material: $e');
    }
  }

  // ========== ASSIGNMENTS ==========
  Future<List<Assignment>> getAssignments({
    int? userId,
    int? classId,
    int? subjectId,
    String? status,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/get-assignments.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();
      if (status != null) queryParams['status'] = status;

      final fullUri = uri.replace(queryParameters: queryParams);
      print('LMS Repository: Loading assignments from $fullUri');

      final response = await http.get(fullUri);

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') || response.body.trim().startsWith('<html')) {
          throw Exception('Server returned HTML instead of JSON. Check PHP errors.');
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> assignmentsJson = data['data'] ?? [];
          return assignmentsJson.map((json) => Assignment.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load assignments');
        }
      } else if (response.statusCode == 404) {
        throw Exception('API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/lms/get-assignments.php');
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load assignments: $e');
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
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/add-assignment.php');
      final body = {
        'title': title,
        'description': description,
        'class_id': classId,
        if (subjectId != null) 'subject_id': subjectId,
        'due_date': dueDate,
        if (maxMarks != null) 'max_marks': maxMarks,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add assignment: $e');
    }
  }

  // ========== QUIZZES ==========
  Future<List<Quiz>> getQuizzes({
    int? userId,
    int? classId,
    int? subjectId,
    String? status,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/get-quizzes.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();
      if (status != null) queryParams['status'] = status;

      final fullUri = uri.replace(queryParameters: queryParams);
      print('LMS Repository: Loading quizzes from $fullUri');

      final response = await http.get(fullUri);

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') || response.body.trim().startsWith('<html')) {
          throw Exception('Server returned HTML instead of JSON. Check PHP errors.');
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> quizzesJson = data['data'] ?? [];
          return quizzesJson.map((json) => Quiz.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load quizzes');
        }
      } else if (response.statusCode == 404) {
        throw Exception('API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/lms/get-quizzes.php');
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load quizzes: $e');
    }
  }

  Future<Quiz?> getQuiz(int quizId, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/get-quiz.php');
      final queryParams = <String, String>{'quiz_id': quizId.toString()};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return Quiz.fromJson(data['data']);
        }
        return null;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load quiz: $e');
    }
  }

  Future<List<QuizQuestion>> getQuizQuestions(int quizId, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/get-quiz-questions.php');
      final queryParams = <String, String>{'quiz_id': quizId.toString()};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> questionsJson = data['data'] ?? [];
          return questionsJson.map((json) => QuizQuestion.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load quiz questions');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load quiz questions: $e');
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
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/add-quiz.php');
      final body = {
        'title': title,
        'description': description,
        'class_id': classId,
        if (subjectId != null) 'subject_id': subjectId,
        'duration_minutes': durationMinutes,
        'total_marks': totalMarks,
        'start_date': startDate,
        if (endDate != null) 'end_date': endDate,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add quiz: $e');
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
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/add-quiz-question.php');
      final body = {
        'quiz_id': quizId,
        'question': question,
        'question_type': questionType,
        'options': options.join(','),
        if (correctAnswer != null) 'correct_answer': correctAnswer,
        'marks': marks,
        'order': order,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add quiz question: $e');
    }
  }

  Future<bool> startQuizAttempt({
    required int quizId,
    required int studentId,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/start-quiz-attempt.php');
      final body = {
        'quiz_id': quizId,
        'student_id': studentId,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to start quiz attempt: $e');
    }
  }

  Future<bool> submitQuizAttempt({
    required int attemptId,
    required Map<String, String> answers,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/submit-quiz-attempt.php');
      final body = {
        'attempt_id': attemptId,
        'answers': answers,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to submit quiz attempt: $e');
    }
  }

  Future<List<QuizAttempt>> getQuizAttempts({
    int? quizId,
    int? studentId,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/lms/get-quiz-attempts.php');
      final queryParams = <String, String>{};
      if (quizId != null) queryParams['quiz_id'] = quizId.toString();
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> attemptsJson = data['data'] ?? [];
          return attemptsJson.map((json) => QuizAttempt.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load quiz attempts');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load quiz attempts: $e');
    }
  }
}

