import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/communication_models.dart';

class CommunicationRepository {
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');

  // ========== ANNOUNCEMENTS ==========
  Future<List<Announcement>> getAnnouncements({
    int? userId,
    String? status,
    String? targetAudience,
  }) async {
    try {
      final uri = Uri.parse(
        '$baseUrl/ajax/communication/get-announcements.php',
      );
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (status != null) queryParams['status'] = status;
      if (targetAudience != null) {
        queryParams['target_audience'] = targetAudience;
      }

      final fullUri = uri.replace(queryParameters: queryParams);
      print('Communication Repository: Loading announcements from $fullUri');

      final response = await http.get(fullUri);

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> announcementsJson = data['data'] ?? [];
          return announcementsJson
              .map((json) => Announcement.fromJson(json))
              .toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load announcements');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/communication/get-announcements.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load announcements: $e');
    }
  }

  Future<bool> addAnnouncement({
    required String title,
    required String content,
    String? targetAudience,
    int? classId,
    String? attachmentUrl,
    String? status,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/communication/add-announcement.php');
      final body = {
        'title': title,
        'content': content,
        if (targetAudience != null) 'target_audience': targetAudience,
        if (classId != null) 'class_id': classId,
        if (attachmentUrl != null) 'attachment_url': attachmentUrl,
        if (status != null) 'status': status,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add announcement: $e');
    }
  }

  // ========== MESSAGES ==========
  Future<List<Message>> getMessages({
    int? userId,
    String? messageType, // 'Inbox', 'Sent', 'Draft'
    bool? unreadOnly,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/communication/get-messages.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (messageType != null) queryParams['message_type'] = messageType;
      if (unreadOnly == true) queryParams['unread_only'] = '1';

      final fullUri = uri.replace(queryParameters: queryParams);
      print('Communication Repository: Loading messages from $fullUri');

      final response = await http.get(fullUri);

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> messagesJson = data['data'] ?? [];
          return messagesJson.map((json) => Message.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load messages');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/communication/get-messages.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load messages: $e');
    }
  }

  Future<bool> sendMessage({
    required int toUserId,
    required String subject,
    required String message,
    String? attachmentUrl,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/communication/send-message.php');
      final body = {
        'to_user_id': toUserId,
        'subject': subject,
        'message': message,
        if (attachmentUrl != null) 'attachment_url': attachmentUrl,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to send message: $e');
    }
  }

  Future<bool> markMessageAsRead(int messageId, {int? userId}) async {
    try {
      final uri = Uri.parse(
        '$baseUrl/ajax/communication/mark-message-read.php',
      );
      final body = {
        'message_id': messageId,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to mark message as read: $e');
    }
  }

  // ========== SMS ==========
  Future<List<Sms>> getSmsHistory({int? userId, String? status}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/communication/get-sms-history.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (status != null) queryParams['status'] = status;

      final response = await http.get(
        uri.replace(queryParameters: queryParams),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> smsJson = data['data'] ?? [];
          return smsJson.map((json) => Sms.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load SMS history');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load SMS history: $e');
    }
  }

  Future<bool> sendSms({
    required String recipientType,
    int? recipientId,
    int? classId,
    required String message,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/communication/send-sms.php');
      final body = {
        'recipient_type': recipientType,
        if (recipientId != null) 'recipient_id': recipientId,
        if (classId != null) 'class_id': classId,
        'message': message,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to send SMS: $e');
    }
  }

  // ========== EMAIL ==========
  Future<List<Email>> getEmailHistory({int? userId, String? status}) async {
    try {
      final uri = Uri.parse(
        '$baseUrl/ajax/communication/get-email-history.php',
      );
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (status != null) queryParams['status'] = status;

      final response = await http.get(
        uri.replace(queryParameters: queryParams),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> emailsJson = data['data'] ?? [];
          return emailsJson.map((json) => Email.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load email history');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load email history: $e');
    }
  }

  Future<bool> sendEmail({
    required String recipientType,
    int? recipientId,
    int? classId,
    required String subject,
    required String body,
    String? attachmentUrl,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/communication/send-email.php');
      final requestBody = {
        'recipient_type': recipientType,
        if (recipientId != null) 'recipient_id': recipientId,
        if (classId != null) 'class_id': classId,
        'subject': subject,
        'body': body,
        if (attachmentUrl != null) 'attachment_url': attachmentUrl,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(requestBody),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to send email: $e');
    }
  }
}
