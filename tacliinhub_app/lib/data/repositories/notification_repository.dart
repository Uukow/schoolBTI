import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/notification_models.dart';

class NotificationRepository {
  /// Get notifications for a user
  Future<NotificationResponse> getNotifications(
    int userId, {
    int page = 1,
    int limit = 20,
    bool unreadOnly = false,
  }) async {
    try {
      final queryParams = {
        'user_id': userId.toString(),
        'page': page.toString(),
        'limit': limit.toString(),
        if (unreadOnly) 'unread_only': 'true',
      };

      final uri = Uri.parse('${AppConstants.baseUrl}/notifications/index.php')
          .replace(queryParameters: queryParams);

      final response = await http.get(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          return NotificationResponse.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load notifications');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Network error: ${e.toString()}');
    }
  }

  /// Mark a notification as read
  Future<void> markAsRead(int userId, int notificationId) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConstants.baseUrl}/notifications/mark_read.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
          'notification_id': notificationId,
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] != true) {
          throw Exception(data['message'] ?? 'Failed to mark as read');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to mark notification as read: ${e.toString()}');
    }
  }

  /// Mark all notifications as read
  Future<void> markAllAsRead(int userId) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConstants.baseUrl}/notifications/mark_all_read.php'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'user_id': userId,
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] != true) {
          throw Exception(data['message'] ?? 'Failed to mark all as read');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to mark all as read: ${e.toString()}');
    }
  }
}














