import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/teacher_models.dart';

class TeacherRepository {
  Future<TeacherStats> getTeacherStats(int userId) async {
    try {
      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/get-dashboard.php?user_id=$userId');
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          if (data['data'] != null && data['data']['stats'] != null) {
            return TeacherStats.fromJson(data['data']['stats']);
          } else if (data['stats'] != null) {
            // Fallback: check if stats is directly in response
            return TeacherStats.fromJson(data['stats']);
          } else {
            throw Exception('Stats data not found in response');
          }
        } else {
          throw Exception(data['message'] ?? 'Failed to load teacher stats');
        }
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load teacher stats: $e');
    }
  }

  Future<List<TeacherClass>> getMyClasses(int userId) async {
    try {
      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/get-classes.php?user_id=$userId');
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          // Handle empty classes list (not an error)
          if (data['data'] != null && data['data']['classes'] != null) {
            final List<dynamic> classesJson = data['data']['classes'];
            return classesJson.map((json) => TeacherClass.fromJson(json)).toList();
          } else if (data['classes'] != null) {
            // Fallback: check if classes is directly in response
            final List<dynamic> classesJson = data['classes'];
            return classesJson.map((json) => TeacherClass.fromJson(json)).toList();
          } else {
            // Empty list is valid - teacher has no classes assigned
            return [];
          }
        } else {
          throw Exception(data['message'] ?? 'Failed to load classes');
        }
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load classes: $e');
    }
  }

  Future<List<TeacherStudent>> getMyStudents({
    required int userId,
    int? classId,
    int? subjectId,
  }) async {
    try {
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();

      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/get-students.php')
          .replace(queryParameters: queryParams);
      
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          if (data['data'] != null && data['data']['students'] != null) {
            final List<dynamic> studentsJson = data['data']['students'];
            return studentsJson.map((json) => TeacherStudent.fromJson(json)).toList();
          } else if (data['students'] != null) {
            final List<dynamic> studentsJson = data['students'];
            return studentsJson.map((json) => TeacherStudent.fromJson(json)).toList();
          } else {
            // Empty list is valid
            return [];
          }
        } else {
          throw Exception(data['message'] ?? 'Failed to load students');
        }
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load students: $e');
    }
  }

  Future<List<TeacherTimetable>> getMyTimetable(int userId) async {
    try {
      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/get-timetable.php?user_id=$userId');
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          if (data['data'] != null && data['data']['timetable'] != null) {
            final List<dynamic> timetableJson = data['data']['timetable'];
            return timetableJson.map((json) => TeacherTimetable.fromJson(json)).toList();
          } else if (data['timetable'] != null) {
            final List<dynamic> timetableJson = data['timetable'];
            return timetableJson.map((json) => TeacherTimetable.fromJson(json)).toList();
          } else {
            return [];
          }
        } else {
          throw Exception(data['message'] ?? 'Failed to load timetable');
        }
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load timetable: $e');
    }
  }

  Future<List<TeacherLessonPlan>> getMyLessonPlans({
    required int userId,
    int? classId,
    int? subjectId,
  }) async {
    try {
      final queryParams = <String, String>{
        'user_id': userId.toString(),
      };
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();

      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/get-lesson-plans.php')
          .replace(queryParameters: queryParams);
      
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          if (data['data'] != null && data['data']['lesson_plans'] != null) {
            final List<dynamic> plansJson = data['data']['lesson_plans'];
            return plansJson.map((json) => TeacherLessonPlan.fromJson(json)).toList();
          } else if (data['lesson_plans'] != null) {
            final List<dynamic> plansJson = data['lesson_plans'];
            return plansJson.map((json) => TeacherLessonPlan.fromJson(json)).toList();
          } else {
            return [];
          }
        } else {
          throw Exception(data['message'] ?? 'Failed to load lesson plans');
        }
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load lesson plans: $e');
    }
  }

  Future<bool> saveAttendance({
    required int userId,
    required String date,
    required int classId,
    required int subjectId,
    required Map<int, String> attendance,
  }) async {
    try {
      // Convert Map<int, String> to format expected by backend: {studentId: {status: 'Present', remarks: ''}}
      final attendanceData = <String, Map<String, String>>{};
      attendance.forEach((studentId, status) {
        attendanceData[studentId.toString()] = {
          'status': status,
          'remarks': '',
        };
      });

      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/save-attendance.php');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: {
          'user_id': userId.toString(),
          'date': date,
          'class_id': classId.toString(),
          'subject_id': subjectId.toString(),
          'attendance': json.encode(attendanceData),
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to save attendance: $e');
    }
  }

  Future<bool> saveMarks({
    required int userId,
    required int examScheduleId,
    required Map<int, double> marks,
  }) async {
    try {
      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/save-marks.php');
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: {
          'exam_schedule_id': examScheduleId.toString(),
          'marks': json.encode(marks),
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to save marks: $e');
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
    try {
      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/teacher/save-lesson-plan.php');
      final body = <String, String>{
        'user_id': userId.toString(),
        'title': title,
        'date': date,
        'class_id': classId.toString(),
        'subject_id': subjectId.toString(),
      };

      if (id != null) body['id'] = id.toString();
      if (objectives != null) body['objectives'] = objectives;
      if (activities != null) body['activities'] = activities;
      if (materials != null) body['materials'] = materials;
      if (homework != null) body['homework'] = homework;
      if (notes != null) body['notes'] = notes;

      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: body,
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to save lesson plan: $e');
    }
  }
}

