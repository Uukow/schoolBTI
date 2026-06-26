import 'package:intl/intl.dart';

int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}

class GeneralSettings {
  final int id;
  final String schoolName;
  final String? schoolNameSomali;
  final String? schoolLogo;
  final String? schoolEmail;
  final String? schoolPhone;
  final String? schoolAddress;
  final String currency;
  final String currencySymbol;
  final String timezone;
  final String language;
  final String dateFormat;

  GeneralSettings({
    required this.id,
    required this.schoolName,
    this.schoolNameSomali,
    this.schoolLogo,
    this.schoolEmail,
    this.schoolPhone,
    this.schoolAddress,
    required this.currency,
    required this.currencySymbol,
    required this.timezone,
    required this.language,
    required this.dateFormat,
  });

  factory GeneralSettings.fromJson(Map<String, dynamic> json) {
    return GeneralSettings(
      id: _parseInt(json['id']),
      schoolName: json['school_name'] ?? '',
      schoolNameSomali: json['school_name_somali'],
      schoolLogo: json['school_logo'],
      schoolEmail: json['school_email'],
      schoolPhone: json['school_phone'],
      schoolAddress: json['school_address'],
      currency: json['currency'] ?? 'USD',
      currencySymbol: json['currency_symbol'] ?? '\$',
      timezone: json['timezone'] ?? 'Africa/Mogadishu',
      language: json['language'] ?? 'en',
      dateFormat: json['date_format'] ?? 'd-m-Y',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'school_name': schoolName,
      'school_name_somali': schoolNameSomali,
      'school_logo': schoolLogo,
      'school_email': schoolEmail,
      'school_phone': schoolPhone,
      'school_address': schoolAddress,
      'currency': currency,
      'currency_symbol': currencySymbol,
      'timezone': timezone,
      'language': language,
      'date_format': dateFormat,
    };
  }
}

class AcademicSettings {
  final int? currentSession;
  final String? academicYearStart;
  final String? academicYearEnd;
  final int? sessionId;
  final String? sessionName;
  final DateTime? sessionStartDate;
  final DateTime? sessionEndDate;
  final bool isActive;

  AcademicSettings({
    this.currentSession,
    this.academicYearStart,
    this.academicYearEnd,
    this.sessionId,
    this.sessionName,
    this.sessionStartDate,
    this.sessionEndDate,
    this.isActive = false,
  });

  factory AcademicSettings.fromJson(Map<String, dynamic> json) {
    return AcademicSettings(
      currentSession: json['current_session'] != null ? _parseInt(json['current_session']) : null,
      academicYearStart: json['academic_year_start'],
      academicYearEnd: json['academic_year_end'],
      sessionId: json['session_id'] != null ? _parseInt(json['session_id']) : null,
      sessionName: json['session_name'],
      sessionStartDate: json['start_date'] != null ? DateTime.parse(json['start_date']) : null,
      sessionEndDate: json['end_date'] != null ? DateTime.parse(json['end_date']) : null,
      isActive: json['is_active'] == 1 || json['is_active'] == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'current_session': currentSession,
      'academic_year_start': academicYearStart,
      'academic_year_end': academicYearEnd,
      'session_id': sessionId,
      'session_name': sessionName,
      'start_date': sessionStartDate != null ? DateFormat('yyyy-MM-dd').format(sessionStartDate!) : null,
      'end_date': sessionEndDate != null ? DateFormat('yyyy-MM-dd').format(sessionEndDate!) : null,
      'is_active': isActive ? 1 : 0,
    };
  }
}

class User {
  final int id;
  final String username;
  final String email;
  final String roleName;
  final String? branchName;
  final bool isActive;
  final DateTime? lastLogin;
  final DateTime createdAt;

  User({
    required this.id,
    required this.username,
    required this.email,
    required this.roleName,
    this.branchName,
    required this.isActive,
    this.lastLogin,
    required this.createdAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: _parseInt(json['id']),
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      roleName: json['role_name'] ?? '',
      branchName: json['branch_name'],
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      lastLogin: json['last_login'] != null ? DateTime.parse(json['last_login']) : null,
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}

class Role {
  final int id;
  final String roleName;
  final String? roleDescription;
  final bool isSystemRole;
  final List<Permission> permissions;

  Role({
    required this.id,
    required this.roleName,
    this.roleDescription,
    required this.isSystemRole,
    required this.permissions,
  });

  factory Role.fromJson(Map<String, dynamic> json) {
    return Role(
      id: _parseInt(json['id']),
      roleName: json['role_name'] ?? '',
      roleDescription: json['role_description'],
      isSystemRole: json['is_system_role'] == 1 || json['is_system_role'] == true,
      permissions: (json['permissions'] as List<dynamic>?)
              ?.map((p) => Permission.fromJson(p))
              .toList() ??
          [],
    );
  }
}

class Permission {
  final int id;
  final String permissionName;
  final String permissionKey;
  final String module;
  final String? description;

  Permission({
    required this.id,
    required this.permissionName,
    required this.permissionKey,
    required this.module,
    this.description,
  });

  factory Permission.fromJson(Map<String, dynamic> json) {
    return Permission(
      id: _parseInt(json['id']),
      permissionName: json['permission_name'] ?? '',
      permissionKey: json['permission_key'] ?? '',
      module: json['module'] ?? '',
      description: json['description'],
    );
  }
}

class BackupInfo {
  final String fileName;
  final String filePath;
  final DateTime createdAt;
  final int fileSize;
  final String? description;

  BackupInfo({
    required this.fileName,
    required this.filePath,
    required this.createdAt,
    required this.fileSize,
    this.description,
  });

  factory BackupInfo.fromJson(Map<String, dynamic> json) {
    return BackupInfo(
      fileName: json['file_name'] ?? '',
      filePath: json['file_path'] ?? '',
      createdAt: DateTime.parse(json['created_at']),
      fileSize: _parseInt(json['file_size']),
      description: json['description'],
    );
  }
}

class SystemInfo {
  final String appName;
  final String appVersion;
  final String phpVersion;
  final String databaseVersion;
  final String serverInfo;
  final DateTime? licenseExpiry;
  final String? licenseKey;
  final String? licenseType;

  SystemInfo({
    required this.appName,
    required this.appVersion,
    required this.phpVersion,
    required this.databaseVersion,
    required this.serverInfo,
    this.licenseExpiry,
    this.licenseKey,
    this.licenseType,
  });

  factory SystemInfo.fromJson(Map<String, dynamic> json) {
    return SystemInfo(
      appName: json['app_name'] ?? 'TacliinHub ERP System',
      appVersion: json['app_version'] ?? '1.0.0',
      phpVersion: json['php_version'] ?? '',
      databaseVersion: json['database_version'] ?? '',
      serverInfo: json['server_info'] ?? '',
      licenseExpiry: json['license_expiry'] != null ? DateTime.parse(json['license_expiry']) : null,
      licenseKey: json['license_key'],
      licenseType: json['license_type'],
    );
  }
}

