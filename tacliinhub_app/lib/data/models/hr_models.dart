/// HR & Payroll Models for TacliinHub
library;

class Staff {
  final int id;
  final String staffId;
  final String firstName;
  final String lastName;
  final String gender;
  final String dateOfBirth;
  final String? email;
  final String phone;
  final String? address;
  final String? city;
  final String? state;
  final String? postalCode;
  final String? photo;
  final String designation;
  final String? department;
  final String? qualification;
  final int? experienceYears;
  final String joiningDate;
  final String? leavingDate;
  final String employmentType;
  final String status;
  final String? bankAccountNo;
  final String? bankName;
  final String? emergencyContact;
  final String? emergencyPhone;
  final int? branchId;
  final String? branchName;
  final String createdAt;

  Staff({
    required this.id,
    required this.staffId,
    required this.firstName,
    required this.lastName,
    required this.gender,
    required this.dateOfBirth,
    this.email,
    required this.phone,
    this.address,
    this.city,
    this.state,
    this.postalCode,
    this.photo,
    required this.designation,
    this.department,
    this.qualification,
    this.experienceYears,
    required this.joiningDate,
    this.leavingDate,
    required this.employmentType,
    required this.status,
    this.bankAccountNo,
    this.bankName,
    this.emergencyContact,
    this.emergencyPhone,
    this.branchId,
    this.branchName,
    required this.createdAt,
  });

  factory Staff.fromJson(Map<String, dynamic> json) {
    return Staff(
      id: _parseInt(json['id']),
      staffId: json['staff_id'] ?? '',
      firstName: json['first_name'] ?? '',
      lastName: json['last_name'] ?? '',
      gender: json['gender'] ?? 'Male',
      dateOfBirth: json['date_of_birth'] ?? '',
      email: json['email'],
      phone: json['phone'] ?? '',
      address: json['address'],
      city: json['city'],
      state: json['state'],
      postalCode: json['postal_code'],
      photo: json['photo'],
      designation: json['designation'] ?? '',
      department: json['department'],
      qualification: json['qualification'],
      experienceYears: json['experience_years'] != null
          ? int.tryParse(json['experience_years'].toString())
          : null,
      joiningDate: json['joining_date'] ?? '',
      leavingDate: json['leaving_date'],
      employmentType: json['employment_type'] ?? 'Full Time',
      status: json['status'] ?? 'Active',
      bankAccountNo: json['bank_account_no'],
      bankName: json['bank_name'],
      emergencyContact: json['emergency_contact'],
      emergencyPhone: json['emergency_phone'],
      branchId: json['branch_id'] != null
          ? int.tryParse(json['branch_id'].toString())
          : null,
      branchName: json['branch_name'],
      createdAt: json['created_at'] ?? '',
    );
  }

  String get fullName => '$firstName $lastName';
}

class PayrollStructure {
  final int id;
  final int staffId;
  final String staffName;
  final double basicSalary;
  final double houseAllowance;
  final double transportAllowance;
  final double medicalAllowance;
  final double otherAllowances;
  final double taxDeduction;
  final double otherDeductions;
  final String effectiveFrom;
  final String createdAt;

  PayrollStructure({
    required this.id,
    required this.staffId,
    required this.staffName,
    required this.basicSalary,
    required this.houseAllowance,
    required this.transportAllowance,
    required this.medicalAllowance,
    required this.otherAllowances,
    required this.taxDeduction,
    required this.otherDeductions,
    required this.effectiveFrom,
    required this.createdAt,
  });

  factory PayrollStructure.fromJson(Map<String, dynamic> json) {
    return PayrollStructure(
      id: _parseInt(json['id']),
      staffId: _parseInt(json['staff_id']),
      staffName: json['staff_name'] ?? '',
      basicSalary: _parseDouble(json['basic_salary'] ?? 0),
      houseAllowance: _parseDouble(json['house_allowance'] ?? 0),
      transportAllowance: _parseDouble(json['transport_allowance'] ?? 0),
      medicalAllowance: _parseDouble(json['medical_allowance'] ?? 0),
      otherAllowances: _parseDouble(json['other_allowances'] ?? 0),
      taxDeduction: _parseDouble(json['tax_deduction'] ?? 0),
      otherDeductions: _parseDouble(json['other_deductions'] ?? 0),
      effectiveFrom: json['effective_from'] ?? '',
      createdAt: json['created_at'] ?? '',
    );
  }

  double get totalAllowances =>
      houseAllowance + transportAllowance + medicalAllowance + otherAllowances;

  double get totalDeductions => taxDeduction + otherDeductions;

  double get grossSalary => basicSalary + totalAllowances;

  double get netSalary => grossSalary - totalDeductions;
}

class SalaryPayment {
  final int id;
  final int staffId;
  final String staffName;
  final String paymentMonth;
  final double basicSalary;
  final double allowances;
  final double deductions;
  final double netSalary;
  final String? paymentDate;
  final String? paymentMethod;
  final String? remarks;
  final String? payslipPath;
  final String createdAt;

  SalaryPayment({
    required this.id,
    required this.staffId,
    required this.staffName,
    required this.paymentMonth,
    required this.basicSalary,
    required this.allowances,
    required this.deductions,
    required this.netSalary,
    this.paymentDate,
    this.paymentMethod,
    this.remarks,
    this.payslipPath,
    required this.createdAt,
  });

  factory SalaryPayment.fromJson(Map<String, dynamic> json) {
    return SalaryPayment(
      id: _parseInt(json['id']),
      staffId: _parseInt(json['staff_id']),
      staffName: json['staff_name'] ?? '',
      paymentMonth: json['payment_month'] ?? '',
      basicSalary: _parseDouble(json['basic_salary'] ?? 0),
      allowances: _parseDouble(json['allowances'] ?? 0),
      deductions: _parseDouble(json['deductions'] ?? 0),
      netSalary: _parseDouble(json['net_salary'] ?? 0),
      paymentDate: json['payment_date'],
      paymentMethod: json['payment_method'],
      remarks: json['remarks'],
      payslipPath: json['payslip_path'],
      createdAt: json['created_at'] ?? '',
    );
  }
}

class LeaveType {
  final int id;
  final String leaveName;
  final String leaveCode;
  final int? daysAllowed;

  LeaveType({
    required this.id,
    required this.leaveName,
    required this.leaveCode,
    this.daysAllowed,
  });

  factory LeaveType.fromJson(Map<String, dynamic> json) {
    return LeaveType(
      id: _parseInt(json['id']),
      leaveName: json['leave_name'] ?? '',
      leaveCode: json['leave_code'] ?? '',
      daysAllowed: json['days_allowed'] != null
          ? int.tryParse(json['days_allowed'].toString())
          : null,
    );
  }
}

class LeaveApplication {
  final int id;
  final int staffId;
  final String staffName;
  final int leaveTypeId;
  final String leaveTypeName;
  final String leaveCode;
  final String startDate;
  final String endDate;
  final int totalDays;
  final String reason;
  final String status;
  final String? approvedByName;
  final String? approvalDate;
  final String? rejectionReason;
  final String appliedAt;

  LeaveApplication({
    required this.id,
    required this.staffId,
    required this.staffName,
    required this.leaveTypeId,
    required this.leaveTypeName,
    required this.leaveCode,
    required this.startDate,
    required this.endDate,
    required this.totalDays,
    required this.reason,
    required this.status,
    this.approvedByName,
    this.approvalDate,
    this.rejectionReason,
    required this.appliedAt,
  });

  factory LeaveApplication.fromJson(Map<String, dynamic> json) {
    return LeaveApplication(
      id: _parseInt(json['id']),
      staffId: _parseInt(json['staff_id']),
      staffName: json['staff_name'] ?? '',
      leaveTypeId: _parseInt(json['leave_type_id']),
      leaveTypeName: json['leave_type_name'] ?? '',
      leaveCode: json['leave_code'] ?? '',
      startDate: json['start_date'] ?? '',
      endDate: json['end_date'] ?? '',
      totalDays: _parseInt(json['total_days'] ?? 0),
      reason: json['reason'] ?? '',
      status: json['status'] ?? 'Pending',
      approvedByName: json['approved_by_name'],
      approvalDate: json['approval_date'],
      rejectionReason: json['rejection_reason'],
      appliedAt: json['applied_at'] ?? '',
    );
  }
}

class StaffAttendance {
  final int id;
  final int staffId;
  final String staffName;
  final String designation;
  final String attendanceDate;
  final String? checkIn;
  final String? checkOut;
  final String status;
  final String? remarks;
  final String createdAt;

  StaffAttendance({
    required this.id,
    required this.staffId,
    required this.staffName,
    required this.designation,
    required this.attendanceDate,
    this.checkIn,
    this.checkOut,
    required this.status,
    this.remarks,
    required this.createdAt,
  });

  factory StaffAttendance.fromJson(Map<String, dynamic> json) {
    return StaffAttendance(
      id: _parseInt(json['id']),
      staffId: _parseInt(json['staff_id']),
      staffName: json['staff_name'] ?? '',
      designation: json['designation'] ?? '',
      attendanceDate: json['attendance_date'] ?? '',
      checkIn: json['check_in'],
      checkOut: json['check_out'],
      status: json['status'] ?? 'Present',
      remarks: json['remarks'],
      createdAt: json['created_at'] ?? '',
    );
  }
}

// Helper functions
int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}

double _parseDouble(dynamic value) {
  if (value == null) return 0.0;
  if (value is double) return value;
  if (value is int) return value.toDouble();
  if (value is String) return double.tryParse(value) ?? 0.0;
  return 0.0;
}
