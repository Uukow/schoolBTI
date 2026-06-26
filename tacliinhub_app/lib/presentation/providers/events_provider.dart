import 'package:flutter/material.dart';
import '../../data/repositories/events_repository.dart';
import '../../data/models/events_models.dart';

class EventsProvider with ChangeNotifier {
  final EventsRepository _repository = EventsRepository();

  List<Event> _events = [];
  Event? _selectedEvent;
  bool _isLoading = false;
  String? _error;

  List<Event> get events => _events;
  Event? get selectedEvent => _selectedEvent;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Get events for a specific date range
  List<Event> getEventsForDate(DateTime date) {
    return _events.where((event) {
      final eventDate = event.startDate;
      return eventDate.year == date.year &&
          eventDate.month == date.month &&
          eventDate.day == date.day;
    }).toList();
  }

  // Get events for a month
  List<Event> getEventsForMonth(DateTime month) {
    return _events.where((event) {
      return event.startDate.year == month.year &&
          event.startDate.month == month.month;
    }).toList();
  }

  // Get upcoming events
  List<Event> get upcomingEvents {
    final now = DateTime.now();
    return _events
        .where((event) => event.startDate.isAfter(now) && event.status != 'Cancelled')
        .toList()
      ..sort((a, b) => a.startDate.compareTo(b.startDate));
  }

  // Get events by type
  List<Event> getEventsByType(String eventType) {
    return _events.where((event) => event.eventType == eventType).toList();
  }

  Future<void> loadEvents({
    int? userId,
    DateTime? startDate,
    DateTime? endDate,
    String? eventType,
    String? status,
    String? targetAudience,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _events = await _repository.getEvents(
        userId: userId,
        startDate: startDate,
        endDate: endDate,
        eventType: eventType,
        status: status,
        targetAudience: targetAudience,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadEvent(int eventId, {int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _selectedEvent = await _repository.getEvent(eventId, userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addEvent(
        title: title,
        description: description,
        startDate: startDate,
        endDate: endDate,
        startTime: startTime,
        endTime: endTime,
        eventType: eventType,
        location: location,
        color: color,
        isAllDay: isAllDay,
        isRecurring: isRecurring,
        recurrencePattern: recurrencePattern,
        recurrenceInterval: recurrenceInterval,
        recurrenceEndDate: recurrenceEndDate,
        targetAudience: targetAudience,
        classId: classId,
        status: status,
        userId: userId,
      );

      if (success) {
        await loadEvents(
          userId: userId,
          eventType: eventType,
          status: status,
          targetAudience: targetAudience,
        );
      }

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.updateEvent(
        eventId: eventId,
        title: title,
        description: description,
        startDate: startDate,
        endDate: endDate,
        startTime: startTime,
        endTime: endTime,
        eventType: eventType,
        location: location,
        color: color,
        isAllDay: isAllDay,
        isRecurring: isRecurring,
        recurrencePattern: recurrencePattern,
        recurrenceInterval: recurrenceInterval,
        recurrenceEndDate: recurrenceEndDate,
        targetAudience: targetAudience,
        classId: classId,
        status: status,
        userId: userId,
      );

      if (success) {
        await loadEvents(userId: userId);
        await loadEvent(eventId, userId: userId);
      }

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteEvent(int eventId, {int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.deleteEvent(eventId, userId: userId);

      if (success) {
        _events.removeWhere((event) => event.id == eventId);
        if (_selectedEvent?.id == eventId) {
          _selectedEvent = null;
        }
      }

      _isLoading = false;
      notifyListeners();
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

