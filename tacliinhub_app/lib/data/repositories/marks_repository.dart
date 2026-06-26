import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants.dart';

class MarksRepository {
  Future<List<dynamic>> getMarks(int userId, {int? subjectId}) async {
    final prefs = await SharedPreferences.getInstance();
    final cacheKey = 'marks_data_${userId}_${subjectId ?? "all"}';

    try {
      String url = '${AppConstants.baseUrl}/student/marks.php?user_id=$userId';
      if (subjectId != null) {
        url += '&subject_id=$subjectId';
      }

      final response = await http.get(
        Uri.parse(url),
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
        throw Exception('Failed to load marks: ${response.statusCode}');
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
