import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/examination_models.dart';

class ExaminationRepository {

  /// Get all exam types
  Future<List<ExamType>> getExamTypes() async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/exams/get-exam-types.php');

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        final List<dynamic> typesJson = data['data'] ?? [];
        return typesJson.map((json) => ExamType.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load exam types');
      }
    } catch (e) {
      throw Exception('Failed to load exam types: ${e.toString()}');
    }
  }

  /// Get exams
  Future<List<Exam>> getExams({
    required int userId,
    int? classId,
    int? sessionId,
  }) async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (sessionId != null) queryParams['session_id'] = sessionId.toString();

      final uri = Uri.parse('$baseUrl/ajax/exams/get-exams.php')
          .replace(queryParameters: queryParams);

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        final List<dynamic> examsJson = data['data'] ?? [];
        return examsJson.map((json) => Exam.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load exams');
      }
    } catch (e) {
      throw Exception('Failed to load exams: ${e.toString()}');
    }
  }

  /// Get exam schedules
  Future<List<ExamSchedule>> getExamSchedules({
    required int userId,
    int? examId,
    int? classId,
    int? subjectId,
  }) async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (examId != null) queryParams['exam_id'] = examId.toString();
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();

      final uri = Uri.parse('$baseUrl/ajax/exams/get-schedules.php')
          .replace(queryParameters: queryParams);

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        final List<dynamic> schedulesJson = data['data'] ?? [];
        return schedulesJson.map((json) => ExamSchedule.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load exam schedules');
      }
    } catch (e) {
      throw Exception('Failed to load exam schedules: ${e.toString()}');
    }
  }

  /// Get student marks for an exam schedule
  Future<List<StudentMark>> getStudentMarks({
    required int userId,
    required int examScheduleId,
  }) async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/exams/get-marks.php')
          .replace(queryParameters: {
        'user_id': userId.toString(),
        'exam_schedule_id': examScheduleId.toString(),
      });

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        final List<dynamic> marksJson = data['data'] ?? [];
        return marksJson.map((json) => StudentMark.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load marks');
      }
    } catch (e) {
      throw Exception('Failed to load marks: ${e.toString()}');
    }
  }

  /// Save student marks
  Future<bool> saveMarks({
    required int userId,
    required int examScheduleId,
    required List<Map<String, dynamic>> students,
  }) async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/exams/save-marks.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'schedule_id': examScheduleId,
          'students': students,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to save marks: ${e.toString()}');
    }
  }

  /// Get exam results
  Future<List<ExamResult>> getExamResults({
    required int userId,
    int? examId,
    int? studentId,
    int? classId,
  }) async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (examId != null) queryParams['exam_id'] = examId.toString();
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (classId != null) queryParams['class_id'] = classId.toString();

      final uri = Uri.parse('$baseUrl/ajax/exams/get-results.php')
          .replace(queryParameters: queryParams);

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        final List<dynamic> resultsJson = data['data'] ?? [];
        return resultsJson.map((json) => ExamResult.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load results');
      }
    } catch (e) {
      throw Exception('Failed to load results: ${e.toString()}');
    }
  }

  /// Get exam analytics
  Future<ExamAnalytics> getExamAnalytics({
    required int userId,
    int? examId,
    int? classId,
  }) async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (examId != null) queryParams['exam_id'] = examId.toString();
      if (classId != null) queryParams['class_id'] = classId.toString();

      final uri = Uri.parse('$baseUrl/ajax/exams/get-analytics.php')
          .replace(queryParameters: queryParams);

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        return ExamAnalytics.fromJson(data['data'] ?? {});
      } else {
        throw Exception(data['message'] ?? 'Failed to load analytics');
      }
    } catch (e) {
      throw Exception('Failed to load analytics: ${e.toString()}');
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
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/exams/add-exam.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'exam_type_id': examTypeId,
          'exam_name': examName,
          'class_id': classId,
          'start_date': startDate.toIso8601String().split('T')[0],
          'end_date': endDate.toIso8601String().split('T')[0],
          'description': description,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to create exam: ${e.toString()}');
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
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/exams/add-schedule.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'exam_id': examId,
          'subject_id': subjectId,
          'exam_date': examDate.toIso8601String().split('T')[0],
          'start_time': startTime,
          'end_time': endTime,
          'room_no': roomNo,
          'total_marks': totalMarks,
          'passing_marks': passingMarks,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to create exam schedule: ${e.toString()}');
    }
  }
}

