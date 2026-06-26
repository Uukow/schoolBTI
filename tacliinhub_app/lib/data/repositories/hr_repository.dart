import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/hr_models.dart';

class HrRepository {
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');

  // ========== STAFF ==========
  Future<List<Staff>> getStaff({int? userId, String? status, int? branchId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/get-staff.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (status != null) queryParams['status'] = status;
      if (branchId != null) queryParams['branch_id'] = branchId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> staffJson = data['data'] ?? [];
          return staffJson.map((json) => Staff.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load staff');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load staff: $e');
    }
  }

  Future<bool> addStaff({
    String? staffId,
    required String firstName,
    required String lastName,
    required String gender,
    required String dateOfBirth,
    String? email,
    required String phone,
    String? address,
    String? city,
    String? state,
    String? postalCode,
    required String designation,
    String? department,
    String? qualification,
    int? experienceYears,
    required String joiningDate,
    required String employmentType,
    String? bankAccountNo,
    String? bankName,
    String? emergencyContact,
    String? emergencyPhone,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/add-staff.php');
      final body = {
        if (staffId != null && staffId.isNotEmpty) 'staff_id': staffId,
        'first_name': firstName,
        'last_name': lastName,
        'gender': gender,
        'date_of_birth': dateOfBirth,
        if (email != null && email.isNotEmpty) 'email': email,
        'phone': phone,
        if (address != null && address.isNotEmpty) 'address': address,
        if (city != null && city.isNotEmpty) 'city': city,
        if (state != null && state.isNotEmpty) 'state': state,
        if (postalCode != null && postalCode.isNotEmpty) 'postal_code': postalCode,
        'designation': designation,
        if (department != null && department.isNotEmpty) 'department': department,
        if (qualification != null && qualification.isNotEmpty) 'qualification': qualification,
        if (experienceYears != null) 'experience_years': experienceYears,
        'joining_date': joiningDate,
        'employment_type': employmentType,
        if (bankAccountNo != null && bankAccountNo.isNotEmpty) 'bank_account_no': bankAccountNo,
        if (bankName != null && bankName.isNotEmpty) 'bank_name': bankName,
        if (emergencyContact != null && emergencyContact.isNotEmpty) 'emergency_contact': emergencyContact,
        if (emergencyPhone != null && emergencyPhone.isNotEmpty) 'emergency_phone': emergencyPhone,
        if (userId != null) 'user_id': userId,
      };

      print('HR Repository: Adding staff to $uri');
      print('HR Repository: Request body: ${json.encode(body)}');

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      print('HR Repository: Response status: ${response.statusCode}');
      print('HR Repository: Response body: ${response.body}');

      if (response.statusCode == 200) {
        // Check if response is HTML (PHP error)
        if (response.body.trim().startsWith('<!DOCTYPE') || response.body.trim().startsWith('<html')) {
          throw Exception('Server returned HTML instead of JSON. Check PHP errors on server.');
        }

        try {
          final data = json.decode(response.body);
          if (data['success'] == true) {
            return true;
          } else {
            final errorMsg = data['message'] ?? 'Failed to add staff';
            throw Exception(errorMsg);
          }
        } catch (e) {
          if (e is Exception) rethrow;
          throw Exception('Failed to parse server response: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}\nResponse: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}');
      }
    } catch (e) {
      print('HR Repository Error: $e');
      if (e is Exception) {
        rethrow;
      }
      throw Exception('Failed to add staff: $e');
    }
  }

  // ========== PAYROLL STRUCTURES ==========
  Future<List<PayrollStructure>> getPayrollStructures({int? userId, int? staffId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/get-payroll-structures.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (staffId != null) queryParams['staff_id'] = staffId.toString();

      final fullUri = uri.replace(queryParameters: queryParams);
      print('HR Repository: Loading payroll structures from $fullUri');
      
      final response = await http.get(fullUri);
      
      print('HR Repository: Response status: ${response.statusCode}');
      print('HR Repository: Response body preview: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}');

      if (response.statusCode == 200) {
        // Check if response is HTML (PHP error)
        if (response.body.trim().startsWith('<!DOCTYPE') || response.body.trim().startsWith('<html')) {
          throw Exception('Server returned HTML instead of JSON. Check PHP errors.');
        }
        
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> structuresJson = data['data'] ?? [];
          return structuresJson.map((json) => PayrollStructure.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load payroll structures');
        }
      } else if (response.statusCode == 404) {
        throw Exception('API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/hr/get-payroll-structures.php\n3. URL: $fullUri');
      } else {
        throw Exception('Server error: ${response.statusCode}\nResponse: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}');
      }
    } catch (e) {
      print('HR Repository Error: $e');
      throw Exception('Failed to load payroll structures: $e');
    }
  }

  Future<bool> addPayrollStructure({
    required int staffId,
    required double basicSalary,
    double houseAllowance = 0,
    double transportAllowance = 0,
    double medicalAllowance = 0,
    double otherAllowances = 0,
    double taxDeduction = 0,
    double otherDeductions = 0,
    required String effectiveFrom,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/add-payroll-structure.php');
      final body = {
        'staff_id': staffId,
        'basic_salary': basicSalary,
        'house_allowance': houseAllowance,
        'transport_allowance': transportAllowance,
        'medical_allowance': medicalAllowance,
        'other_allowances': otherAllowances,
        'tax_deduction': taxDeduction,
        'other_deductions': otherDeductions,
        'effective_from': effectiveFrom,
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
      throw Exception('Failed to add payroll structure: $e');
    }
  }

  // ========== SALARY PAYMENTS ==========
  Future<List<SalaryPayment>> getSalaryPayments({
    int? userId,
    int? staffId,
    String? month,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/get-salary-payments.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (staffId != null) queryParams['staff_id'] = staffId.toString();
      if (month != null) queryParams['month'] = month;

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> paymentsJson = data['data'] ?? [];
          return paymentsJson.map((json) => SalaryPayment.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load salary payments');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load salary payments: $e');
    }
  }

  Future<bool> processSalaryPayment({
    required int staffId,
    required String paymentMonth,
    required double basicSalary,
    required double allowances,
    required double deductions,
    required double netSalary,
    String? paymentDate,
    String? paymentMethod,
    String? remarks,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/process-salary-payment.php');
      final body = {
        'staff_id': staffId,
        'payment_month': paymentMonth,
        'basic_salary': basicSalary,
        'allowances': allowances,
        'deductions': deductions,
        'net_salary': netSalary,
        if (paymentDate != null) 'payment_date': paymentDate,
        if (paymentMethod != null) 'payment_method': paymentMethod,
        if (remarks != null) 'remarks': remarks,
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
      throw Exception('Failed to process salary payment: $e');
    }
  }

  // ========== LEAVE TYPES ==========
  Future<List<LeaveType>> getLeaveTypes({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/get-leave-types.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> typesJson = data['data'] ?? [];
          return typesJson.map((json) => LeaveType.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load leave types');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load leave types: $e');
    }
  }

  // ========== LEAVE APPLICATIONS ==========
  Future<List<LeaveApplication>> getLeaveApplications({
    int? userId,
    int? staffId,
    String? status,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/get-leave-applications.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (staffId != null) queryParams['staff_id'] = staffId.toString();
      if (status != null) queryParams['status'] = status;

      final fullUri = uri.replace(queryParameters: queryParams);
      print('HR Repository: Loading leave applications from $fullUri');
      
      final response = await http.get(fullUri);
      
      print('HR Repository: Response status: ${response.statusCode}');
      print('HR Repository: Response body preview: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}');

      if (response.statusCode == 200) {
        // Check if response is HTML (PHP error)
        if (response.body.trim().startsWith('<!DOCTYPE') || response.body.trim().startsWith('<html')) {
          throw Exception('Server returned HTML instead of JSON. Check PHP errors.');
        }
        
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> applicationsJson = data['data'] ?? [];
          return applicationsJson.map((json) => LeaveApplication.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load leave applications');
        }
      } else if (response.statusCode == 404) {
        throw Exception('API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/hr/get-leave-applications.php\n3. URL: $fullUri');
      } else {
        throw Exception('Server error: ${response.statusCode}\nResponse: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}');
      }
    } catch (e) {
      print('HR Repository Error: $e');
      throw Exception('Failed to load leave applications: $e');
    }
  }

  Future<bool> applyLeave({
    required int staffId,
    required int leaveTypeId,
    required String startDate,
    required String endDate,
    required int totalDays,
    required String reason,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/apply-leave.php');
      final body = {
        'staff_id': staffId,
        'leave_type_id': leaveTypeId,
        'start_date': startDate,
        'end_date': endDate,
        'total_days': totalDays,
        'reason': reason,
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
      throw Exception('Failed to apply leave: $e');
    }
  }

  Future<bool> updateLeaveStatus({
    required int applicationId,
    required String status,
    String? rejectionReason,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/update-leave-status.php');
      final body = {
        'application_id': applicationId,
        'status': status,
        if (rejectionReason != null) 'rejection_reason': rejectionReason,
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
      throw Exception('Failed to update leave status: $e');
    }
  }

  // ========== STAFF ATTENDANCE ==========
  Future<List<StaffAttendance>> getStaffAttendance({
    int? userId,
    int? staffId,
    String? date,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/get-staff-attendance.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (staffId != null) queryParams['staff_id'] = staffId.toString();
      if (date != null) queryParams['date'] = date;

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> attendanceJson = data['data'] ?? [];
          return attendanceJson.map((json) => StaffAttendance.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load staff attendance');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load staff attendance: $e');
    }
  }

  Future<bool> saveStaffAttendance({
    required int staffId,
    required String attendanceDate,
    String? checkIn,
    String? checkOut,
    required String status,
    String? remarks,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/hr/save-staff-attendance.php');
      final body = {
        'staff_id': staffId,
        'attendance_date': attendanceDate,
        'check_in': checkIn, // Send null if not set, backend will handle it
        'check_out': checkOut, // Send null if not set, backend will handle it
        'status': status,
        'remarks': remarks ?? '',
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
      throw Exception('Failed to save staff attendance: $e');
    }
  }

  // ========== REST API v1 (api/hr/*) ==========
  String get _apiV1 => '${AppConstants.baseUrl}/hr';

  Future<Map<String, dynamic>> getHrDashboard({required int userId, int? branchId}) async {
    final params = <String, String>{'user_id': userId.toString()};
    if (branchId != null) params['branch_id'] = branchId.toString();
    final response = await http.get(Uri.parse('$_apiV1/dashboard.php').replace(queryParameters: params));
    final data = json.decode(response.body);
    if (data['success'] == true) return data['data'] as Map<String, dynamic>;
    throw Exception(data['message'] ?? 'Failed to load HR dashboard');
  }

  Future<List<dynamic>> getHrStaffApi({required int userId, String? status, int? branchId}) async {
    final params = <String, String>{'user_id': userId.toString()};
    if (status != null) params['status'] = status;
    if (branchId != null) params['branch_id'] = branchId.toString();
    final response = await http.get(Uri.parse('$_apiV1/staff.php').replace(queryParameters: params));
    final data = json.decode(response.body);
    if (data['success'] == true) return data['data'] as List<dynamic>;
    throw Exception(data['message'] ?? 'Failed to load staff');
  }

  Future<List<dynamic>> getHrAttendanceApi({
    required int userId,
    int? staffId,
    String? date,
    String? month,
  }) async {
    final params = <String, String>{'user_id': userId.toString()};
    if (staffId != null) params['staff_id'] = staffId.toString();
    if (date != null) params['date'] = date;
    if (month != null) params['month'] = month;
    final response = await http.get(Uri.parse('$_apiV1/attendance.php').replace(queryParameters: params));
    final data = json.decode(response.body);
    if (data['success'] == true) return data['data'] as List<dynamic>;
    throw Exception(data['message'] ?? 'Failed to load attendance');
  }

  Future<List<dynamic>> getHrLeaveApi({
    required int userId,
    int? staffId,
    String? status,
  }) async {
    final params = <String, String>{'user_id': userId.toString()};
    if (staffId != null) params['staff_id'] = staffId.toString();
    if (status != null) params['status'] = status;
    final response = await http.get(Uri.parse('$_apiV1/leave.php').replace(queryParameters: params));
    final data = json.decode(response.body);
    if (data['success'] == true) return data['data'] as List<dynamic>;
    throw Exception(data['message'] ?? 'Failed to load leave data');
  }

  Future<List<dynamic>> getHrPayrollApi({
    required int userId,
    int? staffId,
    String? month,
  }) async {
    final params = <String, String>{'user_id': userId.toString()};
    if (staffId != null) params['staff_id'] = staffId.toString();
    if (month != null) params['month'] = month;
    final response = await http.get(Uri.parse('$_apiV1/payroll.php').replace(queryParameters: params));
    final data = json.decode(response.body);
    if (data['success'] == true) return data['data'] as List<dynamic>;
    throw Exception(data['message'] ?? 'Failed to load payroll');
  }

  Future<List<dynamic>> getRecruitmentApi({
    required int userId,
    String resource = 'vacancies',
  }) async {
    final params = <String, String>{'user_id': userId.toString(), 'resource': resource};
    final response = await http.get(Uri.parse('$_apiV1/recruitment.php').replace(queryParameters: params));
    final data = json.decode(response.body);
    if (data['success'] == true) return data['data'] as List<dynamic>;
    throw Exception(data['message'] ?? 'Failed to load recruitment data');
  }

  Future<Map<String, dynamic>> recruitmentAction({
    required int userId,
    required String action,
    required Map<String, dynamic> payload,
  }) async {
    final body = Map<String, dynamic>.from(payload);
    body['action'] = action;
    body['user_id'] = userId;
    final response = await http.post(
      Uri.parse('$_apiV1/recruitment.php?user_id=$userId'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode(body),
    );
    final data = json.decode(response.body);
    if (data['success'] == true) {
      return {'message': data['message'], 'data': data['data']};
    }
    throw Exception(data['message'] ?? 'Recruitment action failed');
  }
}

