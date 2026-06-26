import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/events_models.dart';

class EventsRepository {
  final String baseUrl = AppConstants.baseUrl.endsWith('/api') 
      ? AppConstants.baseUrl.replaceAll('/api', '')
      : AppConstants.baseUrl;

  Future<List<Event>> getEvents({
    int? userId,
    DateTime? startDate,
    DateTime? endDate,
    String? eventType,
    String? status,
    String? targetAudience,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/events/get-events.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (startDate != null) queryParams['start_date'] = startDate.toIso8601String().split('T')[0];
      if (endDate != null) queryParams['end_date'] = endDate.toIso8601String().split('T')[0];
      if (eventType != null) queryParams['event_type'] = eventType;
      if (status != null) queryParams['status'] = status;
      if (targetAudience != null) queryParams['target_audience'] = targetAudience;

      final fullUri = uri.replace(queryParameters: queryParams);
      print('Events Repository: Loading events from $fullUri');

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
          final List<dynamic> eventsJson = data['data'] ?? [];
          return eventsJson.map((json) => Event.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load events');
        }
      } else if (response.statusCode == 404) {
        throw Exception(
          'API endpoint not found (404). Please check:\n1. XAMPP is running\n2. File exists: ajax/events/get-events.php',
        );
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load events: $e');
    }
  }

  Future<Event?> getEvent(int eventId, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/events/get-event.php');
      final queryParams = <String, String>{'event_id': eventId.toString()};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return Event.fromJson(data['data']);
        }
        return null;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load event: $e');
    }
  }

  Future<bool> addEvent({
    required String title,
    required String description,
    required DateTime startDate,
    required DateTime endDate,
    String? startTime,
    String? endTime,
    required String eventType,
    String? location,
    String? color,
    required bool isAllDay,
    required bool isRecurring,
    String? recurrencePattern,
    int? recurrenceInterval,
    DateTime? recurrenceEndDate,
    String? targetAudience,
    int? classId,
    String? status,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/events/add-event.php');
      final requestBody = {
        'title': title,
        'description': description,
        'start_date': startDate.toIso8601String().split('T')[0],
        'end_date': endDate.toIso8601String().split('T')[0],
        if (startTime != null) 'start_time': startTime,
        if (endTime != null) 'end_time': endTime,
        'event_type': eventType,
        if (location != null) 'location': location,
        if (color != null) 'color': color,
        'is_all_day': isAllDay ? 1 : 0,
        'is_recurring': isRecurring ? 1 : 0,
        if (recurrencePattern != null) 'recurrence_pattern': recurrencePattern,
        if (recurrenceInterval != null) 'recurrence_interval': recurrenceInterval,
        if (recurrenceEndDate != null)
          'recurrence_end_date': recurrenceEndDate.toIso8601String().split('T')[0],
        if (targetAudience != null) 'target_audience': targetAudience,
        if (classId != null) 'class_id': classId,
        if (status != null) 'status': status,
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
      throw Exception('Failed to add event: $e');
    }
  }

  Future<bool> updateEvent({
    required int eventId,
    required String title,
    required String description,
    required DateTime startDate,
    required DateTime endDate,
    String? startTime,
    String? endTime,
    required String eventType,
    String? location,
    String? color,
    required bool isAllDay,
    required bool isRecurring,
    String? recurrencePattern,
    int? recurrenceInterval,
    DateTime? recurrenceEndDate,
    String? targetAudience,
    int? classId,
    String? status,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/events/update-event.php');
      final requestBody = {
        'event_id': eventId,
        'title': title,
        'description': description,
        'start_date': startDate.toIso8601String().split('T')[0],
        'end_date': endDate.toIso8601String().split('T')[0],
        if (startTime != null) 'start_time': startTime,
        if (endTime != null) 'end_time': endTime,
        'event_type': eventType,
        if (location != null) 'location': location,
        if (color != null) 'color': color,
        'is_all_day': isAllDay ? 1 : 0,
        'is_recurring': isRecurring ? 1 : 0,
        if (recurrencePattern != null) 'recurrence_pattern': recurrencePattern,
        if (recurrenceInterval != null) 'recurrence_interval': recurrenceInterval,
        if (recurrenceEndDate != null)
          'recurrence_end_date': recurrenceEndDate.toIso8601String().split('T')[0],
        if (targetAudience != null) 'target_audience': targetAudience,
        if (classId != null) 'class_id': classId,
        if (status != null) 'status': status,
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
      throw Exception('Failed to update event: $e');
    }
  }

  Future<bool> deleteEvent(int eventId, {int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/events/delete-event.php');
      final requestBody = {
        'event_id': eventId,
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
      throw Exception('Failed to delete event: $e');
    }
  }

  Future<List<Event>> getEventsByDateRange({
    required DateTime startDate,
    required DateTime endDate,
    int? userId,
  }) async {
    return getEvents(
      userId: userId,
      startDate: startDate,
      endDate: endDate,
    );
  }
}

