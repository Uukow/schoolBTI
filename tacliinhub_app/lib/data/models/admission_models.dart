/// Admission Models for TacliinHub
library;

class Admission {
  final int id;
  final String applicationNumber;
  final String firstName;
  final String lastName;
  final String? middleName;
  final String gender;
  final String dateOfBirth;
  final String? email;
  final String? phone;
  final String address;
  final String? city;
  final String? state;
  final int? classAppliedFor;
  final String? classAppliedName;
  final int branchId;
  final String? branchName;
  final String status; // Pending, Approved, Rejected, Enrolled
  final String? guardianName;
  final String? guardianPhone;
  final String? guardianEmail;
  final String? previousSchool;
  final String applicationDate;
  final String? reviewedDate;
  final int? reviewedBy;
  final String? reviewerName;
  final String? remarks;
  final double applicationFee;
  final String paymentStatus; // Paid, Unpaid, Partial

  Admission({
    required this.id,
    required this.applicationNumber,
    required this.firstName,
    required this.lastName,
    this.middleName,
    required this.gender,
    required this.dateOfBirth,
    this.email,
    this.phone,
    required this.address,
    this.city,
    this.state,
    this.classAppliedFor,
    this.classAppliedName,
    required this.branchId,
    this.branchName,
    required this.status,
    this.guardianName,
    this.guardianPhone,
    this.guardianEmail,
    this.previousSchool,
    required this.applicationDate,
    this.reviewedDate,
    this.reviewedBy,
    this.reviewerName,
    this.remarks,
    required this.applicationFee,
    required this.paymentStatus,
  });

  String get fullName => '$firstName ${middleName ?? ''} $lastName'.trim();
  bool get isPending => status.toLowerCase() == 'pending';
  bool get isApproved => status.toLowerCase() == 'approved';
  bool get isRejected => status.toLowerCase() == 'rejected';
  bool get isPaid => paymentStatus.toLowerCase() == 'paid';

  factory Admission.fromJson(Map<String, dynamic> json) {
    return Admission(
      id: _parseInt(json['id']),
      applicationNumber: json['application_number'] ?? '',
      firstName: json['first_name'] ?? '',
      lastName: json['last_name'] ?? '',
      middleName: json['middle_name'],
      gender: json['gender'] ?? '',
      dateOfBirth: json['date_of_birth'] ?? '',
      email: json['email'],
      phone: json['phone'],
      address: json['address'] ?? '',
      city: json['city'],
      state: json['state'],
      classAppliedFor: json['class_applied_for'] != null
          ? _parseInt(json['class_applied_for'])
          : null,
      classAppliedName: json['class_applied_name'],
      branchId: _parseInt(json['branch_id']),
      branchName: json['branch_name'],
      status: json['status'] ?? 'Pending',
      guardianName: json['guardian_name'],
      guardianPhone: json['guardian_phone'],
      guardianEmail: json['guardian_email'],
      previousSchool: json['previous_school'],
      applicationDate: json['application_date'] ?? '',
      reviewedDate: json['reviewed_date'],
      reviewedBy: json['reviewed_by'] != null
          ? _parseInt(json['reviewed_by'])
          : null,
      reviewerName: json['reviewer_name'],
      remarks: json['remarks'],
      applicationFee:
          double.tryParse(json['application_fee']?.toString() ?? '0') ?? 0.0,
      paymentStatus: json['payment_status'] ?? 'Unpaid',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'application_number': applicationNumber,
      'first_name': firstName,
      'last_name': lastName,
      'middle_name': middleName,
      'gender': gender,
      'date_of_birth': dateOfBirth,
      'email': email,
      'phone': phone,
      'address': address,
      'city': city,
      'state': state,
      'class_applied_for': classAppliedFor,
      'branch_id': branchId,
      'status': status,
      'guardian_name': guardianName,
      'guardian_phone': guardianPhone,
      'guardian_email': guardianEmail,
      'previous_school': previousSchool,
      'application_date': applicationDate,
      'application_fee': applicationFee,
      'payment_status': paymentStatus,
    };
  }
}

class AdmissionStats {
  final int totalApplications;
  final int pendingReview;
  final int approved;
  final int rejected;
  final int enrolled;
  final int thisMonth;
  final int thisWeek;

  AdmissionStats({
    required this.totalApplications,
    required this.pendingReview,
    required this.approved,
    required this.rejected,
    required this.enrolled,
    required this.thisMonth,
    required this.thisWeek,
  });

  factory AdmissionStats.fromJson(Map<String, dynamic> json) {
    return AdmissionStats(
      totalApplications: _parseInt(json['total_applications']),
      pendingReview: _parseInt(json['pending_review']),
      approved: _parseInt(json['approved']),
      rejected: _parseInt(json['rejected']),
      enrolled: _parseInt(json['enrolled']),
      thisMonth: _parseInt(json['this_month']),
      thisWeek: _parseInt(json['this_week']),
    );
  }
}

/// Helper function to parse int
int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}












