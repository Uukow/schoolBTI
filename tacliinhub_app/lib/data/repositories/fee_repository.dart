import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/fee_models.dart';

class FeeRepository {
  /// Get student fees summary
  Future<FeesSummary> getFeesSummary(int userId) async {
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/student/fees.php?user_id=$userId'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return FeesSummary.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load fees');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Network error: ${e.toString()}');
    }
  }
}














