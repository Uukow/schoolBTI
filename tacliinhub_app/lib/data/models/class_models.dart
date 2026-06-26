/// Class Models for TacliinHub
library;

class SchoolClass {
  final int id;
  final String className;
  final String classCode;
  final int classOrder;
  final int branchId;
  final String? branchName;
  final bool isActive;
  final int totalStudents;
  final int totalSections;

  SchoolClass({
    required this.id,
    required this.className,
    required this.classCode,
    required this.classOrder,
    required this.branchId,
    this.branchName,
    required this.isActive,
    required this.totalStudents,
    required this.totalSections,
  });

  factory SchoolClass.fromJson(Map<String, dynamic> json) {
    return SchoolClass(
      id: _parseInt(json['id']),
      className: json['class_name'] ?? '',
      classCode: json['class_code'] ?? '',
      classOrder: _parseInt(json['class_order'] ?? 0),
      branchId: _parseInt(json['branch_id']),
      branchName: json['branch_name'],
      isActive: json['is_active'] == true || json['is_active'] == 1 || json['is_active'] == null,
      totalStudents: _parseInt(json['total_students'] ?? 0),
      totalSections: _parseInt(json['total_sections'] ?? json['section_count'] ?? 0),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'class_name': className,
      'class_code': classCode,
      'class_order': classOrder,
      'branch_id': branchId,
      'branch_name': branchName,
      'is_active': isActive,
      'total_students': totalStudents,
      'total_sections': totalSections,
    };
  }
}

class Section {
  final int id;
  final String sectionName;
  final int classId;
  final String? className;
  final int capacity;
  final int currentStudents;

  Section({
    required this.id,
    required this.sectionName,
    required this.classId,
    this.className,
    required this.capacity,
    required this.currentStudents,
  });

  bool get isFull => currentStudents >= capacity;
  int get availableSeats => capacity - currentStudents;

  factory Section.fromJson(Map<String, dynamic> json) {
    return Section(
      id: _parseInt(json['id']),
      sectionName: json['section_name'] ?? '',
      classId: _parseInt(json['class_id']),
      className: json['class_name'],
      capacity: _parseInt(json['capacity'] ?? 0),
      currentStudents: _parseInt(json['current_students'] ?? 0),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'section_name': sectionName,
      'class_id': classId,
      'class_name': className,
      'capacity': capacity,
      'current_students': currentStudents,
    };
  }
}

/// Helper function to parse int
int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}












