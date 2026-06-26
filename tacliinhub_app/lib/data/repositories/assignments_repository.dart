import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants.dart';

class AssignmentsRepository {
  Future<List<dynamic>> getAssignments(int userId) async {
    final prefs = await SharedPreferences.getInstance();
    final cacheKey = 'assignments_data_$userId';

    try {
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/student/assignments.php?user_id=$userId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          await prefs.setString(cacheKey, jsonEncode(data['data']));
          return data['data'];
        } else {
          throw Exception(data['message']);
        }
      } else {
        throw Exception('Failed to load assignments: ${response.statusCode}');
      }
    } catch (e) {
      final cachedString = prefs.getString(cacheKey);
      if (cachedString != null) {
        return jsonDecode(cachedString);
      }
      throw Exception('Connection error and no cached data: $e');
    }
  }
}
