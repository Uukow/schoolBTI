import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/class_models.dart';

class ClassRepository {
  /// Get all classes
  Future<List<SchoolClass>> getAllClasses() async {
    try {
      print('📚 Fetching all classes...');
      
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/classes/list.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final List<dynamic> classesJson = data['data'] ?? [];
          return classesJson.map((json) => SchoolClass.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load classes');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load classes: ${e.toString()}');
    }
  }

  /// Get sections for a specific class
  Future<List<Section>> getSectionsForClass(int classId) async {
    try {
      print('📋 Fetching sections for class $classId...');
      
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/classes/list.php?class_id=$classId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final List<dynamic> sectionsJson = data['data'] ?? [];
          return sectionsJson.map((json) => Section.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load sections');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load sections: ${e.toString()}');
    }
  }
}














