import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../core/constants.dart';
import '../models/student_models.dart';
import '../models/class_models.dart';

class StudentRepository {
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
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

  // Helper method to safely parse JSON response
  Map<String, dynamic> _parseJsonResponse(String body) {
    final trimmedBody = body.trim();
    if (trimmedBody.isEmpty) {
      throw Exception('Empty response from server');
    }
    // Find JSON start (in case there's any leading content)
    final jsonStart = trimmedBody.indexOf('{');
    final jsonEnd = trimmedBody.lastIndexOf('}');
    if (jsonStart == -1 || jsonEnd == -1 || jsonEnd <= jsonStart) {
      throw Exception('Invalid JSON response format');
    }
    final jsonBody = trimmedBody.substring(jsonStart, jsonEnd + 1);
    return json.decode(jsonBody);
  }

  // Get student profile
  Future<StudentProfile?> getStudentProfile({required int userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/get-profile.php');
      final response = await http.get(
        uri.replace(queryParameters: {'user_id': userId.toString()}),
      );

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true && data['data'] != null) {
          return StudentProfile.fromJson(data['data']);
        }
        return null;
      }
      throw Exception('Failed to load profile: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load student profile: $e');
    }
  }

  // Update student profile
  Future<StudentProfile> updateProfile({
    required int userId,
    String? email,
    String? phone,
    String? address,
    String? city,
    String? state,
    String? postalCode,
    String? photoPath,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/update-profile.php');
      
      final request = http.MultipartRequest('POST', uri);
      request.fields['user_id'] = userId.toString();
      
      if (email != null) request.fields['email'] = email;
      if (phone != null) request.fields['phone'] = phone;
      if (address != null) request.fields['address'] = address;
      if (city != null) request.fields['city'] = city;
      if (state != null) request.fields['state'] = state;
      if (postalCode != null) request.fields['postal_code'] = postalCode;
      
      // Handle photo upload if provided
      if (photoPath != null && photoPath.isNotEmpty) {
        final file = await http.MultipartFile.fromPath('photo', photoPath);
        request.files.add(file);
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true && data['data'] != null) {
          return StudentProfile.fromJson(data['data']);
        }
        throw Exception(data['message'] ?? 'Failed to update profile');
      }
      throw Exception('Failed to update profile: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to update student profile: $e');
    }
  }

  // Get student dashboard stats
  Future<Map<String, dynamic>> getDashboardStats({required int userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/get-dashboard.php');
      final response = await http.get(
        uri.replace(queryParameters: {'user_id': userId.toString()}),
      );

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true) {
          return data['data'] ?? {};
        }
        throw Exception(data['message'] ?? 'Failed to load dashboard stats');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load dashboard stats: $e');
    }
  }

  // Get student classes and subjects
  Future<List<StudentClass>> getStudentClasses({required int userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/get-classes.php');
      final response = await http.get(
        uri.replace(queryParameters: {'user_id': userId.toString()}),
      );

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true) {
          final List<dynamic> classesJson = data['data'] ?? [];
          return classesJson.map((json) => StudentClass.fromJson(json)).toList();
        }
        throw Exception(data['message'] ?? 'Failed to load classes');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load student classes: $e');
    }
  }

  // Get student timetable
  Future<List<StudentTimetable>> getStudentTimetable({required int userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/get-timetable.php');
      final response = await http.get(
        uri.replace(queryParameters: {'user_id': userId.toString()}),
      );

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true) {
          final List<dynamic> timetableJson = data['data'] ?? [];
          return timetableJson.map((json) => StudentTimetable.fromJson(json)).toList();
        }
        throw Exception(data['message'] ?? 'Failed to load timetable');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load student timetable: $e');
    }
  }

  // Get student attendance with statistics
  Future<Map<String, dynamic>> getStudentAttendance({
    required int userId,
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/get-attendance.php');
      final queryParams = {'user_id': userId.toString()};
      if (startDate != null) {
        queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      }
      if (endDate != null) {
        queryParams['end_date'] = endDate.toIso8601String().split('T')[0];
      }

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true && data['data'] != null) {
          return data['data'];
        }
        throw Exception(data['message'] ?? 'Failed to load attendance');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load student attendance: $e');
    }
  }

  // Get student marks
  Future<List<StudentMark>> getStudentMarks({
    required int userId,
    int? examId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/get-marks.php');
      final queryParams = {'user_id': userId.toString()};
      if (examId != null) {
        queryParams['exam_id'] = examId.toString();
      }

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true) {
          final List<dynamic> marksJson = data['data'] ?? [];
          return marksJson.map((json) => StudentMark.fromJson(json)).toList();
        }
        throw Exception(data['message'] ?? 'Failed to load marks');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load student marks: $e');
    }
  }

  // Get student fees
  Future<List<StudentFee>> getStudentFees({required int userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/get-fees.php');
      final response = await http.get(
        uri.replace(queryParameters: {'user_id': userId.toString()}),
      );

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true) {
          final List<dynamic> feesJson = data['data'] ?? [];
          return feesJson.map((json) => StudentFee.fromJson(json)).toList();
        }
        throw Exception(data['message'] ?? 'Failed to load fees');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load student fees: $e');
    }
  }

  // Get financial statement
  Future<Map<String, dynamic>> getFinancialStatement({
    required int userId,
    int? sessionId,
    DateTime? dateFrom,
    DateTime? dateTo,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/student/get-financial-statement.php');
      final queryParams = {'user_id': userId.toString()};
      if (sessionId != null) {
        queryParams['session_id'] = sessionId.toString();
      }
      if (dateFrom != null) {
        queryParams['date_from'] = dateFrom.toIso8601String().split('T')[0];
      }
      if (dateTo != null) {
        queryParams['date_to'] = dateTo.toIso8601String().split('T')[0];
      }

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = _parseJsonResponse(response.body);
        if (data['success'] == true && data['data'] != null) {
          return data['data'];
        }
        throw Exception(data['message'] ?? 'Failed to load financial statement');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load financial statement: $e');
    }
  }

  // Get all students (for admin/staff use)
  Future<List<Student>> getAllStudents({
    String? status,
    int? classId,
    int? sectionId,
    String? search,
    int? branchId, // Add branch filter support
  }) async {
    try {
      // Get user ID for authentication
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final uri = Uri.parse('$baseUrl/ajax/students/get-all.php');
      final queryParams = <String, String>{
        'user_id': userId.toString(), // Required for authentication
      };
      if (status != null) queryParams['status'] = status;
      if (classId != null) queryParams['class_id'] = classId.toString();
      if (sectionId != null) queryParams['section_id'] = sectionId.toString();
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (branchId != null) queryParams['branch_id'] = branchId.toString();

      final fullUri = uri.replace(queryParameters: queryParams);
      print('📡 Loading students from: $fullUri');
      print('🔑 User ID: $userId, Branch ID: $branchId');

      final response = await http.get(
        fullUri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      print('📨 Response status: ${response.statusCode}');
      print('📦 Response body preview: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}');

      final responseBody = response.body.trim();
      
      if (response.statusCode == 404) {
        final errorMsg = 'Endpoint not found (404).\n'
            'URL called: $fullUri\n'
            'Please ensure ajax/students/get-all.php exists on the server at:\n'
            'https://tacliinhub.uukowtech.com/ajax/students/get-all.php';
        print('❌ $errorMsg');
        throw Exception(errorMsg);
      }
      
      if (responseBody.startsWith('<')) {
        print('❌ Server returned HTML instead of JSON');
        throw Exception('Server returned HTML instead of JSON. Check server errors.');
      }

      if (response.statusCode == 200) {
        final data = jsonDecode(responseBody);
        if (data['success'] == true) {
          final List<dynamic> studentsJson = data['data'] ?? [];
          print('✅ Successfully loaded ${studentsJson.length} students');
          return studentsJson.map((json) => Student.fromJson(json)).toList();
        }
        throw Exception(data['message'] ?? 'Failed to load students');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      print('❌ Error loading students: $e');
      throw Exception('Failed to load students: ${e.toString()}');
    }
  }

  // Get student by ID
  Future<Student?> getStudentById(int studentId) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/students/get-by-id.php');
      final response = await http.get(
        uri.replace(queryParameters: {'id': studentId.toString()}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return Student.fromJson(data['data']);
        }
        return null;
      }
      throw Exception('Failed to load student: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load student: $e');
    }
  }

  // Get student statistics
  Future<StudentStats> getStudentStats() async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/students/get-stats.php');
      final response = await http.get(uri);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return StudentStats.fromJson(data['data']);
        }
        throw Exception(data['message'] ?? 'Failed to load stats');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load student stats: $e');
    }
  }

  // Add student
  Future<bool> addStudent(Map<String, dynamic> studentData) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/students/add.php');
      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(studentData),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to add student: $e');
    }
  }

  // Assign sections
  Future<bool> assignSections(List<Map<String, dynamic>> assignments) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/students/assign-sections.php');
      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'assignments': assignments}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to assign sections: $e');
    }
  }

  // Promote students
  Future<bool> promoteStudents(Map<String, dynamic> promotionData) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/students/promote.php');
      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(promotionData),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to promote students: $e');
    }
  }

  // Generate report
  Future<Map<String, dynamic>?> generateReport(
      String reportType, Map<String, dynamic> params) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/reports/generate-student-report.php');
      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'report_type': reportType, ...params}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          return data['data'];
        }
        throw Exception(data['message'] ?? 'Failed to generate report');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to generate report: $e');
    }
  }

  // Get classes
  Future<List<SchoolClass>> getClasses() async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/classes/get-all.php');
      final response = await http.get(uri);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> classesJson = data['data'] ?? [];
          return classesJson.map((json) => SchoolClass.fromJson(json)).toList();
        }
        throw Exception(data['message'] ?? 'Failed to load classes');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load classes: $e');
    }
  }

  // Get sections by class
  Future<List<Section>> getSectionsByClass(int classId) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/classes/get-sections.php');
      final response = await http.get(
        uri.replace(queryParameters: {'class_id': classId.toString()}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> sectionsJson = data['data'] ?? [];
          return sectionsJson.map((json) => Section.fromJson(json)).toList();
        }
        throw Exception(data['message'] ?? 'Failed to load sections');
      }
      throw Exception('Server error: ${response.statusCode}');
    } catch (e) {
      throw Exception('Failed to load sections: $e');
    }
  }
}
