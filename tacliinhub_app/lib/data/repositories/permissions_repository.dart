import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/permissions_model.dart';

class PermissionsRepository {
  Future<UserPermissions> getUserPermissions(int userId) async {
    try {
      final url = Uri.parse('${AppConstants.baseUrl.replaceAll('/api', '')}/ajax/auth/get-permissions.php?user_id=$userId');
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return UserPermissions.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load permissions');
        }
      } else {
        throw Exception('Server Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load permissions: $e');
    }
  }
}

