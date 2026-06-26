class UserPermissions {
  final String role;
  final Map<String, List<String>> permissions;
  final Map<String, bool> moduleAccess;

  UserPermissions({
    required this.role,
    required this.permissions,
    required this.moduleAccess,
  });

  factory UserPermissions.fromJson(Map<String, dynamic> json) {
    return UserPermissions(
      role: json['role'] ?? '',
      permissions: (json['permissions'] as Map<String, dynamic>?)?.map(
        (key, value) => MapEntry(
          key,
          (value as List<dynamic>).map((e) => e.toString()).toList(),
        ),
      ) ?? {},
      moduleAccess: (json['module_access'] as Map<String, dynamic>?)?.map(
        (key, value) => MapEntry(key, value == true),
      ) ?? {},
    );
  }

  bool canAccessModule(String module) {
    return moduleAccess[module] == true;
  }

  bool hasPermission(String module, String permission) {
    final modulePerms = permissions[module] ?? [];
    return modulePerms.contains(permission);
  }
}

