import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../core/constants.dart';
import '../models/academic_models.dart';

class AcademicRepository {
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  /// Get current user ID from storage
  Future<int?> _getCurrentUserId() async {
    try {
      final userDataString = await _storage.read(key: AppConstants.userDataKey);
      if (userDataString != null) {
        final userData = jsonDecode(userDataString);
        return userData['user_id'] ?? userData['id'];
      }
    } catch (e) {
      print('Error getting user ID: $e');
    }
    return null;
  }

  /// Get all subjects
  Future<List<Subject>> getSubjects(int userId) async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/academics/get-subjects.php').replace(
        queryParameters: {'user_id': userId.toString()},
      );

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final responseBody = response.body.trim();
        
        // Check if response is empty
        if (responseBody.isEmpty) {
          throw Exception('Server returned empty response');
        }
        
        // Check if response starts with HTML (error output)
        if (responseBody.startsWith('<')) {
          throw Exception('Server returned HTML instead of JSON. Check server errors.');
        }
        
        // Check for common invalid characters at start
        final firstChar = responseBody.isNotEmpty ? responseBody[0] : '';
        if (firstChar != '{' && firstChar != '[') {
          throw Exception('Invalid JSON format. First character: ${firstChar.codeUnits.join(",")}. Response preview: ${responseBody.substring(0, responseBody.length > 100 ? 100 : responseBody.length)}');
        }
        
        try {
          final data = jsonDecode(responseBody);
          if (data['success'] == true) {
            final List<dynamic> subjectsJson = data['data'] ?? [];
            return subjectsJson.map((json) => Subject.fromJson(json)).toList();
          } else {
            throw Exception(data['message'] ?? 'Failed to load subjects');
          }
        } catch (e) {
          if (e is FormatException) {
            throw Exception('JSON parse error: ${e.message}. Response preview: ${responseBody.substring(0, responseBody.length > 200 ? 200 : responseBody.length)}');
          }
          rethrow;
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load subjects: ${e.toString()}');
    }
  }

  /// Get assignments for a class
  Future<List<ClassAssignment>> getClassAssignments(int classId) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/academics/get-class-assignments.php').replace(
        queryParameters: {
          'class_id': classId.toString(),
          'user_id': userId.toString(),
        },
      );

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final List<dynamic> assignmentsJson = data['data'] ?? [];
          return assignmentsJson.map((json) => ClassAssignment.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load assignments');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load assignments: ${e.toString()}');
    }
  }

  /// Get timetable for a class and section
  Future<List<TimetablePeriod>> getTimetable({
    required int classId,
    required int sectionId,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/academics/get-timetable.php').replace(
        queryParameters: {
          'class_id': classId.toString(),
          'section_id': sectionId.toString(),
          'user_id': userId.toString(),
        },
      );

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final List<dynamic> timetableJson = data['data'] ?? [];
          return timetableJson.map((json) => TimetablePeriod.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load timetable');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load timetable: ${e.toString()}');
    }
  }

  /// Get lesson plans for a class
  Future<List<LessonPlan>> getLessonPlans({
    int? classId,
    int? subjectId,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();

      final uri = Uri.parse('$baseUrl/ajax/academics/get-lesson-plans.php').replace(
        queryParameters: queryParams,
      );

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final responseBody = response.body.trim();
        // Check if response starts with HTML (error output)
        if (responseBody.startsWith('<')) {
          throw Exception('Server returned HTML instead of JSON. Check server errors.');
        }
        
        final data = jsonDecode(responseBody);
        if (data['success'] == true) {
          final List<dynamic> plansJson = data['data'] ?? [];
          return plansJson.map((json) => LessonPlan.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load lesson plans');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load lesson plans: ${e.toString()}');
    }
  }

  /// Get syllabus for a class
  Future<List<Syllabus>> getSyllabus({
    int? classId,
    int? subjectId,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();

      final uri = Uri.parse('$baseUrl/ajax/academics/get-syllabus.php').replace(
        queryParameters: queryParams,
      );

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final responseBody = response.body.trim();
        // Check if response starts with HTML (error output)
        if (responseBody.startsWith('<')) {
          throw Exception('Server returned HTML instead of JSON. Check server errors.');
        }
        
        final data = jsonDecode(responseBody);
        if (data['success'] == true) {
          final List<dynamic> syllabusJson = data['data'] ?? [];
          return syllabusJson.map((json) => Syllabus.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load syllabus');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load syllabus: ${e.toString()}');
    }
  }

  /// Get academic calendar events
  Future<List<AcademicCalendar>> getAcademicCalendar({
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (startDate != null) {
        queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      }
      if (endDate != null) {
        queryParams['end_date'] = endDate.toIso8601String().split('T')[0];
      }

      final uri = Uri.parse('$baseUrl/ajax/academics/get-calendar-events.php').replace(
        queryParameters: queryParams,
      );

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final responseBody = response.body.trim();
        // Check if response starts with HTML (error output)
        if (responseBody.startsWith('<')) {
          throw Exception('Server returned HTML instead of JSON. Check server errors.');
        }
        
        final data = jsonDecode(responseBody);
        if (data['success'] == true) {
          final List<dynamic> eventsJson = data['data'] ?? [];
          return eventsJson.map((json) => AcademicCalendar.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load calendar events');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load calendar events: ${e.toString()}');
    }
  }

  /// Create assignment
  Future<Map<String, dynamic>> createAssignment({
    required int classId,
    required int subjectId,
    required String title,
    String? description,
    required String dueDate,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/academics/create-assignment.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'class_id': classId,
          'subject_id': subjectId,
          'title': title,
          'description': description ?? '',
          'due_date': dueDate,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        return data['data'] ?? {};
      } else {
        throw Exception(data['message'] ?? 'Failed to create assignment');
      }
    } catch (e) {
      throw Exception('Failed to create assignment: ${e.toString()}');
    }
  }

  /// Create lesson plan
  Future<Map<String, dynamic>> createLessonPlan({
    required int classId,
    required int subjectId,
    required String topic,
    required String objectives,
    required String content,
    String? teachingMethods,
    String? assessment,
    required String date,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/academics/create-lesson-plan.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'class_id': classId,
          'subject_id': subjectId,
          'topic': topic,
          'objectives': objectives,
          'content': content,
          'teaching_methods': teachingMethods ?? '',
          'assessment': assessment ?? '',
          'date': date,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        return data['data'] ?? {};
      } else {
        throw Exception(data['message'] ?? 'Failed to create lesson plan');
      }
    } catch (e) {
      throw Exception('Failed to create lesson plan: ${e.toString()}');
    }
  }

  /// Create/update syllabus
  Future<Map<String, dynamic>> createSyllabus({
    required int classId,
    required int subjectId,
    required String title,
    required String description,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/academics/create-syllabus.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'class_id': classId,
          'subject_id': subjectId,
          'title': title,
          'description': description,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        return data['data'] ?? {};
      } else {
        throw Exception(data['message'] ?? 'Failed to create syllabus');
      }
    } catch (e) {
      throw Exception('Failed to create syllabus: ${e.toString()}');
    }
  }

  /// Create calendar event
  Future<Map<String, dynamic>> createCalendarEvent({
    required String title,
    required String eventType,
    String? description,
    required String startDate,
    required String endDate,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/academics/create-calendar-event.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'title': title,
          'event_type': eventType,
          'description': description ?? '',
          'start_date': startDate,
          'end_date': endDate,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      if (data['success'] == true) {
        return data['data'] ?? {};
      } else {
        throw Exception(data['message'] ?? 'Failed to create calendar event');
      }
    } catch (e) {
      throw Exception('Failed to create calendar event: ${e.toString()}');
    }
  }
}

