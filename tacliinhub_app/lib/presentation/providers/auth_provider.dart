import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../core/constants.dart';
import '../../data/models/user_model.dart';
import '../../data/repositories/auth_repository.dart';

class AuthProvider with ChangeNotifier {
  final AuthRepository _authRepository = AuthRepository();
  final FlutterSecureStorage _storage = const FlutterSecureStorage();
  User? _user;
  bool _isLoading = false;
  String? _error;

  User? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _user != null;

  // Constructor to check auth status on startup
  AuthProvider() {
    _checkCurrentUser();
  }

  Future<void> _checkCurrentUser() async {
    _isLoading = true;
    notifyListeners();
    try {
      // First try to load from storage
      try {
        final userDataString = await _storage.read(key: AppConstants.userDataKey);
        if (userDataString != null) {
          final userData = jsonDecode(userDataString);
          _user = User.fromJson(userData);
          _isLoading = false;
          notifyListeners();
          return;
        }
      } catch (e) {
        // If storage read fails, try API
        print('Error loading user from storage: $e');
      }

      // If not in storage, try API
      _user = await _authRepository.getCurrentUser();
      if (_user != null) {
        await _saveUserData(_user!);
      }
    } catch (e) {
      // Ignore error likely just not logged in
      print('Error checking current user: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> _saveUserData(User user) async {
    try {
      final userData = user.toJson();
      // Ensure both 'id' and 'user_id' are present for compatibility
      userData['id'] = user.id;
      userData['user_id'] = user.id;
      await _storage.write(
        key: AppConstants.userDataKey,
        value: jsonEncode(userData),
      );
      print('✅ User data saved to storage: user_id=${user.id}');
    } catch (e) {
      print('❌ Error saving user data: $e');
    }
  }

  Future<bool> login(String username, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _user = await _authRepository.login(username, password);
      if (_user != null) {
        await _saveUserData(_user!);
      }
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await _authRepository.logout();
    _user = null;
    await _storage.delete(key: AppConstants.userDataKey);
    await _storage.delete(key: AppConstants.tokenKey);
    notifyListeners();
  }
}
