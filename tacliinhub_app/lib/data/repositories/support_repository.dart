import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/support_models.dart';

class SupportRepository {
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');

  // ========== GET TICKETS ==========
  Future<List<SupportTicket>> getTickets({
    int? userId,
    String? status,
    String? priority,
    String? category,
    int? assignedTo,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/support/get-tickets.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (status != null) queryParams['status'] = status;
      if (priority != null) queryParams['priority'] = priority;
      if (category != null) queryParams['category'] = category;
      if (assignedTo != null) queryParams['assigned_to'] = assignedTo.toString();

      final fullUri = uri.replace(queryParameters: queryParams);
      print('Support Repository: Loading tickets from $fullUri');

      final response = await http.get(fullUri);
      print('Tickets response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> ticketsJson = data['data'] ?? [];
          return ticketsJson.map((json) => SupportTicket.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load tickets');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/support/get-tickets.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load tickets: $e');
    }
  }

  // ========== GET SINGLE TICKET ==========
  Future<SupportTicket> getTicket(int ticketId, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/support/get-ticket.php');
      final queryParams = <String, String>{'ticket_id': ticketId.toString()};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return SupportTicket.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load ticket');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load ticket: $e');
    }
  }

  // ========== GET STATISTICS ==========
  Future<TicketStats> getTicketStats({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/support/get-ticket-stats.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        if (response.body.trim().startsWith('<!DOCTYPE') ||
            response.body.trim().startsWith('<html')) {
          throw Exception(
            'Server returned HTML instead of JSON. Check PHP errors.',
          );
        }

        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return TicketStats.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load statistics');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load ticket statistics: $e');
    }
  }

  // ========== CREATE TICKET ==========
  Future<Map<String, dynamic>> createTicket({
    required String subject,
    required String description,
    String? category,
    String? priority,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/support/create-ticket.php');
      final body = {
        'subject': subject,
        'description': description,
        'category': category ?? 'General',
        'priority': priority ?? 'Medium',
      };
      if (userId != null) body['user_id'] = userId.toString();

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          return data['data'] ?? {};
        } else {
          throw Exception(data['message'] ?? 'Failed to create ticket');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to create ticket: $e');
    }
  }

  // ========== ADD REPLY ==========
  Future<bool> addReply({
    required int ticketId,
    required String message,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/support/add-reply.php');
      final body = {
        'ticket_id': ticketId,
        'message': message,
      };
      if (userId != null) body['user_id'] = userId.toString();

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
      throw Exception('Failed to add reply: $e');
    }
  }

  // ========== UPDATE TICKET STATUS ==========
  Future<bool> updateTicketStatus({
    required int ticketId,
    required String status,
    String? resolution,
    int? assignedTo,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/support/update-ticket-status.php');
      final body = {
        'ticket_id': ticketId,
        'status': status,
      };
      if (resolution != null) body['resolution'] = resolution;
      if (assignedTo != null) body['assigned_to'] = assignedTo.toString();
      if (userId != null) body['user_id'] = userId.toString();

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
      throw Exception('Failed to update ticket status: $e');
    }
  }
}

