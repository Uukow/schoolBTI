import 'package:flutter/material.dart';
import '../../data/models/settings_models.dart';
import '../../data/repositories/settings_repository.dart';

class SettingsProvider with ChangeNotifier {
  final SettingsRepository _repository;

  GeneralSettings? _generalSettings;
  Map<String, dynamic>? _academicSettings;
  List<User> _users = [];
  List<Role> _roles = [];
  List<Permission> _permissions = [];
  List<BackupInfo> _backups = [];
  SystemInfo? _systemInfo;

  bool _isLoading = false;
  String? _error;

  SettingsProvider(this._repository);

  GeneralSettings? get generalSettings => _generalSettings;
  Map<String, dynamic>? get academicSettings => _academicSettings;
  List<User> get users => _users;
  List<Role> get roles => _roles;
  List<Permission> get permissions => _permissions;
  List<BackupInfo> get backups => _backups;
  SystemInfo? get systemInfo => _systemInfo;
  bool get isLoading => _isLoading;
  String? get error => _error;

  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _setError(String? message) {
    _error = message;
    notifyListeners();
  }

  // General Settings
  Future<void> loadGeneralSettings({int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      _generalSettings = await _repository.getGeneralSettings(userId: userId);
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> saveGeneralSettings(GeneralSettings settings, {int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      final success = await _repository.saveGeneralSettings(settings, userId: userId);
      if (success) {
        _generalSettings = settings;
      }
      return success;
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Academic Settings
  Future<void> loadAcademicSettings({int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      _academicSettings = await _repository.getAcademicSettings(userId: userId);
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> saveAcademicSettings(Map<String, dynamic> settings, {int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      final success = await _repository.saveAcademicSettings(settings, userId: userId);
      if (success) {
        _academicSettings = settings;
      }
      return success;
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // User Management
  Future<void> loadUsers({
    int? userId,
    int? roleId,
    int? branchId,
    bool? isActive,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      _users = await _repository.getUsers(
        userId: userId,
        roleId: roleId,
        branchId: branchId,
        isActive: isActive,
      );
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  // Roles & Permissions
  Future<void> loadRolesAndPermissions({int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      final data = await _repository.getRolesAndPermissions(userId: userId);
      final List<dynamic> rolesJson = data['roles'] ?? [];
      final List<dynamic> permsJson = data['permissions'] ?? [];
      
      _roles = rolesJson.map((json) => Role.fromJson(json)).toList();
      _permissions = permsJson.map((json) => Permission.fromJson(json)).toList();
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> saveRolePermissions(int roleId, List<int> permissionIds, {int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      final success = await _repository.saveRolePermissions(roleId, permissionIds, userId: userId);
      if (success) {
        await loadRolesAndPermissions(userId: userId); // Refresh
      }
      return success;
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Backup & Restore
  Future<void> loadBackups({int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      _backups = await _repository.getBackups(userId: userId);
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  Future<bool> createBackup({int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      final success = await _repository.createBackup(userId: userId);
      if (success) {
        await loadBackups(userId: userId); // Refresh
      }
      return success;
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // System Info
  Future<void> loadSystemInfo({int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      _systemInfo = await _repository.getSystemInfo(userId: userId);
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }
}

