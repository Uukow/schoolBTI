import 'package:flutter/material.dart';
import '../../data/models/support_models.dart';
import '../../data/repositories/support_repository.dart';

class SupportProvider with ChangeNotifier {
  final SupportRepository _repository;

  List<SupportTicket> _tickets = [];
  SupportTicket? _currentTicket;
  TicketStats? _stats;

  bool _isLoading = false;
  String? _error;

  SupportProvider(this._repository);

  List<SupportTicket> get tickets => _tickets;
  SupportTicket? get currentTicket => _currentTicket;
  TicketStats? get stats => _stats;
  bool get isLoading => _isLoading;
  String? get error => _error;

  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _setError(String? message) {
    _error = message;
    notifyListeners();
  }

  // Load Tickets
  Future<void> loadTickets({
    int? userId,
    String? status,
    String? priority,
    String? category,
    int? assignedTo,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      _tickets = await _repository.getTickets(
        userId: userId,
        status: status,
        priority: priority,
        category: category,
        assignedTo: assignedTo,
      );
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  // Load Single Ticket
  Future<void> loadTicket(int ticketId, {int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      _currentTicket = await _repository.getTicket(ticketId, userId: userId);
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  // Load Statistics
  Future<void> loadStats({int? userId}) async {
    _setLoading(true);
    _setError(null);
    try {
      _stats = await _repository.getTicketStats(userId: userId);
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      _setLoading(false);
    }
  }

  // Create Ticket
  Future<Map<String, dynamic>?> createTicket({
    required String subject,
    required String description,
    String? category,
    String? priority,
    int? userId,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      final result = await _repository.createTicket(
        subject: subject,
        description: description,
        category: category,
        priority: priority,
        userId: userId,
      );
      await loadTickets(userId: userId); // Refresh list
      await loadStats(userId: userId); // Refresh stats
      return result;
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
      return null;
    } finally {
      _setLoading(false);
    }
  }

  // Add Reply
  Future<bool> addReply({
    required int ticketId,
    required String message,
    int? userId,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      final success = await _repository.addReply(
        ticketId: ticketId,
        message: message,
        userId: userId,
      );
      if (success) {
        await loadTicket(ticketId, userId: userId); // Refresh ticket
        await loadTickets(userId: userId); // Refresh list
      }
      return success;
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // Update Ticket Status
  Future<bool> updateTicketStatus({
    required int ticketId,
    required String status,
    String? resolution,
    int? assignedTo,
    int? userId,
  }) async {
    _setLoading(true);
    _setError(null);
    try {
      final success = await _repository.updateTicketStatus(
        ticketId: ticketId,
        status: status,
        resolution: resolution,
        assignedTo: assignedTo,
        userId: userId,
      );
      if (success) {
        await loadTicket(ticketId, userId: userId); // Refresh ticket
        await loadTickets(userId: userId); // Refresh list
        await loadStats(userId: userId); // Refresh stats
      }
      return success;
    } catch (e) {
      _setError(e.toString().replaceFirst('Exception: ', ''));
      return false;
    } finally {
      _setLoading(false);
    }
  }
}

