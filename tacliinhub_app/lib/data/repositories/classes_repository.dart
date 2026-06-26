import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants.dart';

class ClassesRepository {
  Future<Map<String, dynamic>> getClasses(int userId) async {
    final prefs = await SharedPreferences.getInstance();
    final cacheKey = 'classes_data_$userId';

    try {
      // Use the API endpoint which supports all roles
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/student/classes.php?user_id=$userId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final responseBody = response.body.trim();
        // Check if response starts with HTML (error output)
        if (responseBody.startsWith('<') || responseBody.isEmpty) {
          throw Exception('Server returned invalid response. Check server errors.');
        }
        
        final data = jsonDecode(responseBody) as Map<String, dynamic>;
        if (data['success'] == true) {
          final responseData = data['data'];
          
          // Check if data already has the expected format (from api/student/classes.php)
          if (responseData is Map<String, dynamic> && responseData.containsKey('subjects')) {
            await prefs.setString(cacheKey, jsonEncode(responseData));
            return responseData;
          }
          
          // Format the response to match what ClassesPage expects (from ajax/get-classes.php)
          final classes = responseData as List<dynamic>;
          final formattedData = <String, dynamic>{
            'class_name': classes.isNotEmpty ? (classes[0] as Map<String, dynamic>)['class_name'] : '',
            'section_name': '',
            'subjects': classes.map<Map<String, dynamic>>((cls) {
              final classMap = cls as Map<String, dynamic>;
              return <String, dynamic>{
                'subject_id': classMap['id'],
                'subject_name': classMap['class_name'],
                'subject_code': classMap['class_code'],
                'subject_type': 'Core',
                'teacher_name': '',
                'teacher_id': null,
              };
            }).toList(),
          };
          
          await prefs.setString(cacheKey, jsonEncode(formattedData));
          return formattedData;
        } else {
          throw Exception(data['message'] ?? 'Failed to load classes');
        }
      } else {
        throw Exception('Failed to load classes: ${response.statusCode}');
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
