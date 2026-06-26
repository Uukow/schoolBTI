import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/settings_models.dart';

class SettingsRepository {
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');

  // ========== GENERAL SETTINGS ==========
  Future<GeneralSettings> getGeneralSettings({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/get-settings.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final fullUri = uri.replace(queryParameters: queryParams);
      print('Settings Repository: Loading general settings from $fullUri');

      final response = await http.get(fullUri);
      print('General Settings response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return GeneralSettings.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load settings');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/settings/get-settings.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load general settings: $e');
    }
  }

  Future<bool> saveGeneralSettings(GeneralSettings settings, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/save-settings.php');
      final body = settings.toJson();
      if (userId != null) body['user_id'] = userId;

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
      throw Exception('Failed to save general settings: $e');
    }
  }

  // ========== ACADEMIC SETTINGS ==========
  Future<Map<String, dynamic>> getAcademicSettings({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/get-academic-settings.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return data['data'];
        } else {
          throw Exception(data['message'] ?? 'Failed to load academic settings');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load academic settings: $e');
    }
  }

  Future<bool> saveAcademicSettings(Map<String, dynamic> settings, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/save-academic-settings.php');
      final body = settings;
      if (userId != null) body['user_id'] = userId;

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
      throw Exception('Failed to save academic settings: $e');
    }
  }

  // ========== USER MANAGEMENT ==========
  Future<List<User>> getUsers({
    int? userId,
    int? roleId,
    int? branchId,
    bool? isActive,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/get-users.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (roleId != null) queryParams['role_id'] = roleId.toString();
      if (branchId != null) queryParams['branch_id'] = branchId.toString();
      if (isActive != null) queryParams['is_active'] = isActive ? '1' : '0';

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> usersJson = data['data'] ?? [];
          return usersJson.map((json) => User.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load users');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load users: $e');
    }
  }

  // ========== ROLES & PERMISSIONS ==========
  Future<Map<String, dynamic>> getRolesAndPermissions({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/get-roles.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return data['data'];
        } else {
          throw Exception(data['message'] ?? 'Failed to load roles');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load roles and permissions: $e');
    }
  }

  Future<bool> saveRolePermissions(int roleId, List<int> permissionIds, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/save-role-permissions.php');
      final body = {
        'role_id': roleId,
        'permission_ids': permissionIds,
      };
      if (userId != null) body['user_id'] = userId;

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
      throw Exception('Failed to save role permissions: $e');
    }
  }

  // ========== BACKUP & RESTORE ==========
  Future<List<BackupInfo>> getBackups({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/get-backups.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> backupsJson = data['data'] ?? [];
          return backupsJson.map((json) => BackupInfo.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load backups');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load backups: $e');
    }
  }

  Future<bool> createBackup({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/create-backup.php');
      final body = <String, dynamic>{};
      if (userId != null) body['user_id'] = userId;

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
      throw Exception('Failed to create backup: $e');
    }
  }

  // ========== SYSTEM INFO ==========
  Future<SystemInfo> getSystemInfo({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/settings/get-system-info.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return SystemInfo.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load system info');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load system info: $e');
    }
  }
}

