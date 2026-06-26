import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../core/constants.dart';
import '../models/attendance_models.dart';

class AttendanceRepository {
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

  /// Get student attendance for a class and date
  Future<List<StudentAttendance>> getStudentAttendance({
    required int classId,
    required int sectionId,
    required String date,
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
        'class_id': classId.toString(),
        'section_id': sectionId.toString(),
        'date': date,
      };
      if (subjectId != null) {
        queryParams['subject_id'] = subjectId.toString();
      }

      final uri = Uri.parse('$baseUrl/ajax/attendance/get-student-attendance.php')
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
        final List<dynamic> attendanceJson = data['data'] ?? [];
        return attendanceJson.map((json) => StudentAttendance.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load attendance');
      }
    } catch (e) {
      throw Exception('Failed to load student attendance: ${e.toString()}');
    }
  }

  /// Get staff attendance for a date
  Future<List<StaffAttendance>> getStaffAttendance({
    required String date,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/attendance/get-staff-attendance.php')
          .replace(queryParameters: {
        'user_id': userId.toString(),
        'date': date,
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
        final List<dynamic> attendanceJson = data['data'] ?? [];
        return attendanceJson.map((json) => StaffAttendance.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load staff attendance');
      }
    } catch (e) {
      throw Exception('Failed to load staff attendance: ${e.toString()}');
    }
  }

  /// Get attendance statistics
  Future<AttendanceStats> getAttendanceStats({
    int? studentId,
    int? staffId,
    String? startDate,
    String? endDate,
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
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (staffId != null) queryParams['staff_id'] = staffId.toString();
      if (startDate != null) queryParams['start_date'] = startDate;
      if (endDate != null) queryParams['end_date'] = endDate;

      final uri = Uri.parse('$baseUrl/ajax/attendance/get-stats.php')
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
        return AttendanceStats.fromJson(data['data'] ?? {});
      } else {
        throw Exception(data['message'] ?? 'Failed to load statistics');
      }
    } catch (e) {
      throw Exception('Failed to load attendance statistics: ${e.toString()}');
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
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/attendance/save-student-attendance.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'class_id': classId,
          'section_id': sectionId,
          'date': date,
          'subject_id': subjectId,
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
      throw Exception('Failed to save attendance: ${e.toString()}');
    }
  }

  /// Save staff attendance
  Future<bool> saveStaffAttendance({
    required String date,
    required List<Map<String, dynamic>> staff,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/attendance/save-staff-attendance.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'date': date,
          'staff': staff,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to save staff attendance: ${e.toString()}');
    }
  }

  /// Get attendance history
  Future<List<StudentAttendance>> getAttendanceHistory({
    int? studentId,
    int? classId,
    String? startDate,
    String? endDate,
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
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (startDate != null) queryParams['start_date'] = startDate;
      if (endDate != null) queryParams['end_date'] = endDate;

      final uri = Uri.parse('$baseUrl/ajax/attendance/get-history.php')
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
        final List<dynamic> historyJson = data['data'] ?? [];
        return historyJson.map((json) => StudentAttendance.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load history');
      }
    } catch (e) {
      throw Exception('Failed to load attendance history: ${e.toString()}');
    }
  }
}

