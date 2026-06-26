/// Branch Models for TacliinHub
library;

class Branch {
  final int id;
  final String branchName;
  final String branchCode;
  final String address;
  final String? city;
  final String? state;
  final String? country;
  final String? phone;
  final String? email;
  final String? principalName;
  final int totalStudents;
  final int totalStaff;
  final bool isActive;
  final String? logo;
  final String createdAt;

  Branch({
    required this.id,
    required this.branchName,
    required this.branchCode,
    required this.address,
    this.city,
    this.state,
    this.country,
    this.phone,
    this.email,
    this.principalName,
    required this.totalStudents,
    required this.totalStaff,
    required this.isActive,
    this.logo,
    required this.createdAt,
  });

  factory Branch.fromJson(Map<String, dynamic> json) {
    return Branch(
      id: json['id'] ?? 0,
      branchName: json['branch_name'] ?? '',
      branchCode: json['branch_code'] ?? '',
      address: json['address'] ?? '',
      city: json['city'],
      state: json['state'],
      country: json['country'],
      phone: json['phone'],
      email: json['email'],
      principalName: json['principal_name'],
      totalStudents: _parseInt(json['total_students']),
      totalStaff: _parseInt(json['total_staff']),
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      logo: json['logo'],
      createdAt: json['created_at'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'branch_name': branchName,
      'branch_code': branchCode,
      'address': address,
      'city': city,
      'state': state,
      'country': country,
      'phone': phone,
      'email': email,
      'principal_name': principalName,
      'total_students': totalStudents,
      'total_staff': totalStaff,
      'is_active': isActive ? 1 : 0,
      'logo': logo,
      'created_at': createdAt,
    };
  }
}

/// Helper function to parse int from dynamic
int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}












