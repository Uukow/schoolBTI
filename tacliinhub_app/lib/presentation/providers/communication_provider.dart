import 'package:flutter/material.dart';
import '../../data/repositories/communication_repository.dart';
import '../../data/models/communication_models.dart';

class CommunicationProvider with ChangeNotifier {
  final CommunicationRepository _repository = CommunicationRepository();

  List<Announcement> _announcements = [];
  List<Message> _messages = [];
  List<Sms> _smsHistory = [];
  List<Email> _emailHistory = [];
  bool _isLoading = false;
  String? _error;

  List<Announcement> get announcements => _announcements;
  List<Message> get messages => _messages;
  List<Sms> get smsHistory => _smsHistory;
  List<Email> get emailHistory => _emailHistory;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // ========== ANNOUNCEMENTS ==========
  Future<void> loadAnnouncements({
    int? userId,
    String? status,
    String? targetAudience,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _announcements = await _repository.getAnnouncements(
        userId: userId,
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

  Future<bool> addAnnouncement({
    required String title,
    required String content,
    String? targetAudience,
    int? classId,
    String? attachmentUrl,
    String? status,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addAnnouncement(
        title: title,
        content: content,
        targetAudience: targetAudience,
        classId: classId,
        attachmentUrl: attachmentUrl,
        status: status,
        userId: userId,
      );

      if (success) {
        await loadAnnouncements(userId: userId, status: status, targetAudience: targetAudience);
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

  // ========== MESSAGES ==========
  Future<void> loadMessages({
    int? userId,
    String? messageType,
    bool? unreadOnly,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _messages = await _repository.getMessages(
        userId: userId,
        messageType: messageType,
        unreadOnly: unreadOnly,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> sendMessage({
    required int toUserId,
    required String subject,
    required String message,
    String? attachmentUrl,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.sendMessage(
        toUserId: toUserId,
        subject: subject,
        message: message,
        attachmentUrl: attachmentUrl,
        userId: userId,
      );

      if (success) {
        await loadMessages(userId: userId, messageType: 'Sent');
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

  Future<bool> markMessageAsRead(int messageId, {int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.markMessageAsRead(messageId, userId: userId);

      if (success) {
        await loadMessages(userId: userId);
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

  // ========== SMS ==========
  Future<void> loadSmsHistory({
    int? userId,
    String? status,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _smsHistory = await _repository.getSmsHistory(
        userId: userId,
        status: status,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> sendSms({
    required String recipientType,
    int? recipientId,
    int? classId,
    required String message,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.sendSms(
        recipientType: recipientType,
        recipientId: recipientId,
        classId: classId,
        message: message,
        userId: userId,
      );

      if (success) {
        await loadSmsHistory(userId: userId);
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

  // ========== EMAIL ==========
  Future<void> loadEmailHistory({
    int? userId,
    String? status,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _emailHistory = await _repository.getEmailHistory(
        userId: userId,
        status: status,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.sendEmail(
        recipientType: recipientType,
        recipientId: recipientId,
        classId: classId,
        subject: subject,
        body: body,
        attachmentUrl: attachmentUrl,
        userId: userId,
      );

      if (success) {
        await loadEmailHistory(userId: userId);
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
}

