import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/user_model.dart';

class AuthRepository {
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');

  Future<User> login(String username, String password) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/auth/login.php');
      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'username': username,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return User.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Login failed');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Login failed: $e');
    }
  }

  Future<User?> getCurrentUser() async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/auth/current-user.php');
      final response = await http.get(uri);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return User.fromJson(data['data']);
        }
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  Future<void> logout() async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/auth/logout.php');
      await http.post(uri);
    } catch (e) {
      // Ignore logout errors
    }
  }

  // ========== FORGOT PASSWORD ==========
  Future<bool> requestPasswordReset(String email) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/auth/forgot-password.php');
      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'email': email}),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to request password reset: $e');
    }
  }

  // ========== RESET PASSWORD ==========
  Future<bool> resetPassword(String token, String newPassword) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/auth/reset-password.php');
      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'token': token,
          'password': newPassword,
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to reset password: $e');
    }
  }
}
