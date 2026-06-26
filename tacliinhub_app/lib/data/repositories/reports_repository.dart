import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/reports_models.dart';

class ReportsRepository {
  final String baseUrl = AppConstants.baseUrl.endsWith('/api')
      ? AppConstants.baseUrl.replaceAll('/api', '')
      : AppConstants.baseUrl;

  // ========== STUDENT REPORTS ==========
  Future<List<StudentReport>> getStudentReports({
    int? studentId,
    int? classId,
    int? sectionId,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/reports/get-student-reports.php');
      final queryParams = <String, String>{};
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (sectionId != null) queryParams['section_id'] = sectionId.toString();
      if (status != null) queryParams['status'] = status;
      if (startDate != null) {
        queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      }
      if (endDate != null) {
        queryParams['end_date'] = endDate.toIso8601String().split('T')[0];
      }
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(
        uri.replace(queryParameters: queryParams),
      );

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> reportsJson = data['data'] ?? [];
          return reportsJson
              .map((json) => StudentReport.fromJson(json))
              .toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load student reports');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/reports/get-student-reports.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load student reports: $e');
    }
  }

  // ========== ACADEMIC REPORTS ==========
  Future<AcademicReport> getAcademicReport({
    required String reportType,
    int? classId,
    int? subjectId,
    int? examId,
    DateTime? startDate,
    DateTime? endDate,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/reports/get-academic-report.php');
      final queryParams = <String, String>{'report_type': reportType};
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (subjectId != null) queryParams['subject_id'] = subjectId.toString();
      if (examId != null) queryParams['exam_id'] = examId.toString();
      if (startDate != null) {
        queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      }
      if (endDate != null) {
        queryParams['end_date'] = endDate.toIso8601String().split('T')[0];
      }
      if (userId != null) queryParams['user_id'] = userId.toString();

      final fullUri = uri.replace(queryParameters: queryParams);
      print('Reports Repository: Loading academic report from $fullUri');

      final response = await http.get(fullUri);
      print('Academic Report response status: ${response.statusCode}');
      print(
        'Academic Report response body preview: ${response.body.substring(0, response.body.length > 500 ? 500 : response.body.length)}...',
      );

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return AcademicReport.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load academic report');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/reports/get-academic-report.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load academic report: $e');
    }
  }

  // ========== FINANCIAL REPORTS ==========
  Future<FinancialReport> getFinancialReport({
    required String reportType,
    DateTime? startDate,
    DateTime? endDate,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/reports/get-financial-report.php');
      final queryParams = <String, String>{'report_type': reportType};
      if (startDate != null) {
        queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      }
      if (endDate != null) {
        queryParams['end_date'] = endDate.toIso8601String().split('T')[0];
      }
      if (userId != null) queryParams['user_id'] = userId.toString();

      final fullUri = uri.replace(queryParameters: queryParams);
      print('Reports Repository: Loading financial report from $fullUri');

      final response = await http.get(fullUri);
      print('Financial Report response status: ${response.statusCode}');
      print(
        'Financial Report response body preview: ${response.body.substring(0, response.body.length > 500 ? 500 : response.body.length)}...',
      );

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return FinancialReport.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load financial report');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/reports/get-financial-report.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load financial report: $e');
    }
  }

  // ========== ATTENDANCE REPORTS ==========
  Future<AttendanceReport> getAttendanceReport({
    required String reportType,
    int? classId,
    int? studentId,
    int? staffId,
    DateTime? startDate,
    DateTime? endDate,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/reports/get-attendance-report.php');
      final queryParams = <String, String>{'report_type': reportType};
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (staffId != null) queryParams['staff_id'] = staffId.toString();
      if (startDate != null) {
        queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      }
      if (endDate != null) {
        queryParams['end_date'] = endDate.toIso8601String().split('T')[0];
      }
      if (userId != null) queryParams['user_id'] = userId.toString();

      final fullUri = uri.replace(queryParameters: queryParams);
      print('Reports Repository: Loading attendance report from $fullUri');

      final response = await http.get(fullUri);
      print('Attendance Report response status: ${response.statusCode}');
      print(
        'Attendance Report response body preview: ${response.body.substring(0, response.body.length > 500 ? 500 : response.body.length)}...',
      );

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return AttendanceReport.fromJson(data['data']);
        } else {
          throw Exception(
            data['message'] ?? 'Failed to load attendance report',
          );
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/reports/get-attendance-report.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load attendance report: $e');
    }
  }

  // ========== CUSTOM REPORTS ==========
  Future<List<CustomReport>> getCustomReports({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/reports/get-custom-reports.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(
        uri.replace(queryParameters: queryParams),
      );

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> reportsJson = data['data'] ?? [];
          return reportsJson
              .map((json) => CustomReport.fromJson(json))
              .toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load custom reports');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/reports/get-custom-reports.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load custom reports: $e');
    }
  }

  Future<Map<String, dynamic>> executeCustomReport({
    required int reportId,
    Map<String, dynamic>? parameters,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/reports/execute-custom-report.php');
      final requestBody = {
        'report_id': reportId,
        if (parameters != null) 'parameters': parameters,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(requestBody),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          return data['data'] ?? {};
        } else {
          throw Exception(data['message'] ?? 'Failed to execute custom report');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to execute custom report: $e');
    }
  }
}
