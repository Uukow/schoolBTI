import 'package:flutter/foundation.dart';
import '../../data/models/permissions_model.dart';
import '../../data/repositories/permissions_repository.dart';

class PermissionsProvider with ChangeNotifier {
  final PermissionsRepository _repository = PermissionsRepository();

  bool _isLoading = false;
  String? _error;
  UserPermissions? _permissions;

  bool get isLoading => _isLoading;
  String? get error => _error;
  UserPermissions? get permissions => _permissions;

  bool canAccessModule(String module) {
    return _permissions?.canAccessModule(module) ?? false;
  }

  bool hasPermission(String module, String permission) {
    return _permissions?.hasPermission(module, permission) ?? false;
  }

  Future<void> loadPermissions(int userId) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _permissions = await _repository.getUserPermissions(userId);
      _error = null;
    } catch (e) {
      _error = e.toString();
      if (kDebugMode) {
        print('Error loading permissions: $e');
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

