import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../core/constants.dart';
import '../../core/branch_helper.dart';
import '../models/fees_models.dart';

class FeesRepository {
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

  /// Get fee types
  Future<List<FeeType>> getFeeTypes() async {
    try {
      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/fees/get-fee-types.php');

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
        return typesJson.map((json) => FeeType.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load fee types');
      }
    } catch (e) {
      throw Exception('Failed to load fee types: ${e.toString()}');
    }
  }

  /// Get fee structures
  Future<List<FeeStructure>> getFeeStructures({
    int? classId,
    int? sessionId,
    BuildContext? context, // Add context for branch filtering
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
      if (sessionId != null) queryParams['session_id'] = sessionId.toString();
      
      // Add branch filter if available
      final branchId = BranchHelper.getBranchId(context);
      if (branchId != null) queryParams['branch_id'] = branchId.toString();

      final uri = Uri.parse('$baseUrl/ajax/fees/get-structures.php')
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
        final List<dynamic> structuresJson = data['data'] ?? [];
        return structuresJson.map((json) => FeeStructure.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load fee structures');
      }
    } catch (e) {
      throw Exception('Failed to load fee structures: ${e.toString()}');
    }
  }

  /// Create fee structure
  Future<bool> createFeeStructure({
    required int classId,
    required int feeTypeId,
    required int sessionId,
    required double amount,
    required String frequency,
    DateTime? dueDate,
    required bool isMandatory,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/fees/add-structure.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'class_id': classId,
          'fee_type_id': feeTypeId,
          'session_id': sessionId,
          'amount': amount,
          'frequency': frequency,
          'due_date': dueDate?.toIso8601String().split('T')[0],
          'is_mandatory': isMandatory ? 1 : 0,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to create fee structure: ${e.toString()}');
    }
  }

  /// Get monthly fee assignments
  Future<List<MonthlyFeeAssignment>> getMonthlyFeeAssignments({
    int? studentId,
    int? classId,
    int? feeTypeId,
    String? month,
    int? sessionId,
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
      if (feeTypeId != null) queryParams['fee_type_id'] = feeTypeId.toString();
      if (month != null) queryParams['month'] = month;
      if (sessionId != null) queryParams['session_id'] = sessionId.toString();

      final uri = Uri.parse('$baseUrl/ajax/fees/get-monthly-assignments.php')
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
        final List<dynamic> assignmentsJson = data['data'] ?? [];
        return assignmentsJson.map((json) => MonthlyFeeAssignment.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load monthly assignments');
      }
    } catch (e) {
      throw Exception('Failed to load monthly assignments: ${e.toString()}');
    }
  }

  /// Assign monthly fees
  Future<bool> assignMonthlyFees({
    required String month,
    required int feeTypeId,
    int? classId,
    required int sessionId,
    DateTime? dueDate,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/fees/assign-monthly-fees.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'month': month,
          'fee_type_id': feeTypeId,
          'class_id': classId,
          'session_id': sessionId,
          'due_date': dueDate?.toIso8601String().split('T')[0],
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to assign monthly fees: ${e.toString()}');
    }
  }

  /// Record flexible payment
  Future<bool> recordFlexiblePayment({
    required int studentId,
    required double amount,
    required String paymentMethod,
    required DateTime paymentDate,
    String? transactionId,
    String? remarks,
    List<Map<String, dynamic>>? allocations,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/fees/record-flexible-payment.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'student_id': studentId,
          'amount': amount,
          'payment_method': paymentMethod,
          'payment_date': paymentDate.toIso8601String().split('T')[0],
          'transaction_id': transactionId,
          'remarks': remarks,
          'allocations': allocations,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to record flexible payment: ${e.toString()}');
    }
  }

  /// Get student fee ledger
  Future<List<StudentFeeLedger>> getStudentFeeLedger({
    required int studentId,
    int? sessionId,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final queryParams = <String, String>{
        'user_id': userId.toString(),
        'student_id': studentId.toString(),
      };
      if (sessionId != null) queryParams['session_id'] = sessionId.toString();

      final uri = Uri.parse('$baseUrl/ajax/fees/get-student-ledger.php')
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
        final List<dynamic> ledgerJson = data['data'] ?? [];
        return ledgerJson.map((json) => StudentFeeLedger.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load ledger');
      }
    } catch (e) {
      throw Exception('Failed to load ledger: ${e.toString()}');
    }
  }

  /// Get invoices
  Future<List<FeeInvoice>> getInvoices({
    int? studentId,
    int? classId,
    String? status,
    int? sessionId,
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
      if (status != null) queryParams['status'] = status;
      if (sessionId != null) queryParams['session_id'] = sessionId.toString();

      final uri = Uri.parse('$baseUrl/ajax/fees/get-invoices.php')
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
        final List<dynamic> invoicesJson = data['data'] ?? [];
        return invoicesJson.map((json) => FeeInvoice.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load invoices');
      }
    } catch (e) {
      throw Exception('Failed to load invoices: ${e.toString()}');
    }
  }

  /// Generate invoice
  Future<bool> generateInvoice({
    required int studentId,
    required int feeTypeId,
    required double amount,
    double discount = 0,
    DateTime? dueDate,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/fees/generate-invoice.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'student_id': studentId,
          'fee_type_id': feeTypeId,
          'amount': amount,
          'discount': discount,
          'due_date': dueDate?.toIso8601String().split('T')[0],
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to generate invoice: ${e.toString()}');
    }
  }

  /// Get payments
  Future<List<FeePayment>> getPayments({
    int? studentId,
    int? invoiceId,
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
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (invoiceId != null) queryParams['invoice_id'] = invoiceId.toString();
      if (startDate != null) queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      if (endDate != null) queryParams['end_date'] = endDate.toIso8601String().split('T')[0];

      final uri = Uri.parse('$baseUrl/ajax/fees/get-payments.php')
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
        final List<dynamic> paymentsJson = data['data'] ?? [];
        return paymentsJson.map((json) => FeePayment.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load payments');
      }
    } catch (e) {
      throw Exception('Failed to load payments: ${e.toString()}');
    }
  }

  /// Record payment
  Future<bool> recordPayment({
    required int invoiceId,
    required double amount,
    required String paymentMethod,
    required DateTime paymentDate,
    String? transactionId,
    String? remarks,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/fees/record-payment.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'invoice_id': invoiceId,
          'amount': amount,
          'payment_method': paymentMethod,
          'payment_date': paymentDate.toIso8601String().split('T')[0],
          'transaction_id': transactionId,
          'remarks': remarks,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to record payment: ${e.toString()}');
    }
  }

  /// Get defaulters
  Future<List<MonthlyFeeAssignment>> getDefaulters({
    int? classId,
    int? feeTypeId,
    int? sessionId,
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
      if (feeTypeId != null) queryParams['fee_type_id'] = feeTypeId.toString();
      if (sessionId != null) queryParams['session_id'] = sessionId.toString();

      final uri = Uri.parse('$baseUrl/ajax/fees/get-defaulters.php')
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
        final List<dynamic> defaultersJson = data['data'] ?? [];
        return defaultersJson.map((json) => MonthlyFeeAssignment.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load defaulters');
      }
    } catch (e) {
      throw Exception('Failed to load defaulters: ${e.toString()}');
    }
  }

  /// Get income
  Future<List<Income>> getIncome({
    int? branchId,
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
      if (branchId != null) queryParams['branch_id'] = branchId.toString();
      if (startDate != null) queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      if (endDate != null) queryParams['end_date'] = endDate.toIso8601String().split('T')[0];

      final uri = Uri.parse('$baseUrl/ajax/fees/get-income.php')
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
        final List<dynamic> incomeJson = data['data'] ?? [];
        return incomeJson.map((json) => Income.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load income');
      }
    } catch (e) {
      throw Exception('Failed to load income: ${e.toString()}');
    }
  }

  /// Add income
  Future<bool> addIncome({
    int? branchId,
    required String incomeCategory,
    required double amount,
    required DateTime incomeDate,
    String? description,
    String? paymentMethod,
    String? referenceNo,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/fees/add-income.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'branch_id': branchId,
          'income_category': incomeCategory,
          'amount': amount,
          'income_date': incomeDate.toIso8601String().split('T')[0],
          'description': description,
          'payment_method': paymentMethod,
          'reference_no': referenceNo,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to add income: ${e.toString()}');
    }
  }

  /// Get expenses
  Future<List<Expense>> getExpenses({
    int? branchId,
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
      if (branchId != null) queryParams['branch_id'] = branchId.toString();
      if (startDate != null) queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      if (endDate != null) queryParams['end_date'] = endDate.toIso8601String().split('T')[0];

      final uri = Uri.parse('$baseUrl/ajax/fees/get-expenses.php')
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
        final List<dynamic> expensesJson = data['data'] ?? [];
        return expensesJson.map((json) => Expense.fromJson(json)).toList();
      } else {
        throw Exception(data['message'] ?? 'Failed to load expenses');
      }
    } catch (e) {
      throw Exception('Failed to load expenses: ${e.toString()}');
    }
  }

  /// Add expense
  Future<bool> addExpense({
    int? branchId,
    required String expenseCategory,
    required double amount,
    required DateTime expenseDate,
    String? description,
    String? paymentMethod,
    String? referenceNo,
  }) async {
    try {
      final userId = await _getCurrentUserId();
      if (userId == null) {
        throw Exception('User not logged in');
      }

      final baseUrl = AppConstants.baseUrl.replaceAll('/api', '');
      final uri = Uri.parse('$baseUrl/ajax/fees/add-expense.php');

      final response = await http.post(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'branch_id': branchId,
          'expense_category': expenseCategory,
          'amount': amount,
          'expense_date': expenseDate.toIso8601String().split('T')[0],
          'description': description,
          'payment_method': paymentMethod,
          'reference_no': referenceNo,
        }),
      ).timeout(const Duration(seconds: 15));

      final responseBody = response.body.trim();
      if (responseBody.startsWith('<')) {
        throw Exception('Server returned HTML instead of JSON');
      }

      final data = jsonDecode(responseBody);
      return data['success'] == true;
    } catch (e) {
      throw Exception('Failed to add expense: ${e.toString()}');
    }
  }

  /// Get finance report
  Future<FinanceReport> getFinanceReport({
    DateTime? startDate,
    DateTime? endDate,
    int? branchId,
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
      if (startDate != null) queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      if (endDate != null) queryParams['end_date'] = endDate.toIso8601String().split('T')[0];
      if (branchId != null) queryParams['branch_id'] = branchId.toString();

      final uri = Uri.parse('$baseUrl/ajax/fees/get-finance-report.php')
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
        return FinanceReport.fromJson(data['data'] ?? {});
      } else {
        throw Exception(data['message'] ?? 'Failed to load finance report');
      }
    } catch (e) {
      throw Exception('Failed to load finance report: ${e.toString()}');
    }
  }
}

