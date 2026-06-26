import 'package:flutter/foundation.dart';
import '../../data/repositories/hr_repository.dart';
import '../../data/models/hr_models.dart';

class HrProvider with ChangeNotifier {
  final HrRepository _repository = HrRepository();

  // Staff
  List<Staff> _staff = [];
  List<PayrollStructure> _payrollStructures = [];
  List<SalaryPayment> _salaryPayments = [];
  List<LeaveType> _leaveTypes = [];
  List<LeaveApplication> _leaveApplications = [];
  List<StaffAttendance> _staffAttendance = [];

  bool _isLoading = false;
  String? _error;

  // Getters
  List<Staff> get staff => _staff;
  List<PayrollStructure> get payrollStructures => _payrollStructures;
  List<SalaryPayment> get salaryPayments => _salaryPayments;
  List<LeaveType> get leaveTypes => _leaveTypes;
  List<LeaveApplication> get leaveApplications => _leaveApplications;
  List<StaffAttendance> get staffAttendance => _staffAttendance;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // ========== STAFF ==========
  Future<void> loadStaff({int? userId, String? status, int? branchId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _staff = await _repository.getStaff(
        userId: userId,
        status: status,
        branchId: branchId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _staff = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addStaff(
        staffId: staffId,
        firstName: firstName,
        lastName: lastName,
        gender: gender,
        dateOfBirth: dateOfBirth,
        email: email,
        phone: phone,
        address: address,
        city: city,
        state: state,
        postalCode: postalCode,
        designation: designation,
        department: department,
        qualification: qualification,
        experienceYears: experienceYears,
        joiningDate: joiningDate,
        employmentType: employmentType,
        bankAccountNo: bankAccountNo,
        bankName: bankName,
        emergencyContact: emergencyContact,
        emergencyPhone: emergencyPhone,
        userId: userId,
      );

      if (success) {
        await loadStaff(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== PAYROLL STRUCTURES ==========
  Future<void> loadPayrollStructures({int? userId, int? staffId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _payrollStructures = await _repository.getPayrollStructures(
        userId: userId,
        staffId: staffId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _payrollStructures = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addPayrollStructure(
        staffId: staffId,
        basicSalary: basicSalary,
        houseAllowance: houseAllowance,
        transportAllowance: transportAllowance,
        medicalAllowance: medicalAllowance,
        otherAllowances: otherAllowances,
        taxDeduction: taxDeduction,
        otherDeductions: otherDeductions,
        effectiveFrom: effectiveFrom,
        userId: userId,
      );

      if (success) {
        await loadPayrollStructures(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== SALARY PAYMENTS ==========
  Future<void> loadSalaryPayments({
    int? userId,
    int? staffId,
    String? month,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _salaryPayments = await _repository.getSalaryPayments(
        userId: userId,
        staffId: staffId,
        month: month,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _salaryPayments = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.processSalaryPayment(
        staffId: staffId,
        paymentMonth: paymentMonth,
        basicSalary: basicSalary,
        allowances: allowances,
        deductions: deductions,
        netSalary: netSalary,
        paymentDate: paymentDate,
        paymentMethod: paymentMethod,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadSalaryPayments(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== LEAVE TYPES ==========
  Future<void> loadLeaveTypes({int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _leaveTypes = await _repository.getLeaveTypes(userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString();
      _leaveTypes = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== LEAVE APPLICATIONS ==========
  Future<void> loadLeaveApplications({
    int? userId,
    int? staffId,
    String? status,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _leaveApplications = await _repository.getLeaveApplications(
        userId: userId,
        staffId: staffId,
        status: status,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _leaveApplications = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.applyLeave(
        staffId: staffId,
        leaveTypeId: leaveTypeId,
        startDate: startDate,
        endDate: endDate,
        totalDays: totalDays,
        reason: reason,
        userId: userId,
      );

      if (success) {
        await loadLeaveApplications(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> updateLeaveStatus({
    required int applicationId,
    required String status,
    String? rejectionReason,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.updateLeaveStatus(
        applicationId: applicationId,
        status: status,
        rejectionReason: rejectionReason,
        userId: userId,
      );

      if (success) {
        await loadLeaveApplications(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== STAFF ATTENDANCE ==========
  Future<void> loadStaffAttendance({
    int? userId,
    int? staffId,
    String? date,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _staffAttendance = await _repository.getStaffAttendance(
        userId: userId,
        staffId: staffId,
        date: date,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _staffAttendance = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.saveStaffAttendance(
        staffId: staffId,
        attendanceDate: attendanceDate,
        checkIn: checkIn,
        checkOut: checkOut,
        status: status,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadStaffAttendance(userId: userId, date: attendanceDate);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}

