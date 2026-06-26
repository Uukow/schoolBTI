class User {
  final int id;
  final String username;
  final String email;
  final String role;
  final int? branchId;
  final String fullName;
  final String? profileImage;
  final int? studentId;
  final int? staffId;

  User({
    required this.id,
    required this.username,
    required this.email,
    required this.role,
    this.branchId,
    required this.fullName,
    this.profileImage,
    this.studentId,
    this.staffId,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['user_id'] ?? 0,
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? '',
      branchId: json['branch_id'],
      fullName: json['full_name'] ?? '',
      profileImage: json['profile_image'],
      studentId: json['student_id'],
      staffId: json['staff_id'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'user_id': id,
      'username': username,
      'email': email,
      'role': role,
      'branch_id': branchId,
      'full_name': fullName,
      'profile_image': profileImage,
      'student_id': studentId,
      'staff_id': staffId,
    };
  }
}
