import 'package:flutter/material.dart';
import '../../data/repositories/notification_repository.dart';
import '../../data/models/notification_models.dart';

class NotificationProvider with ChangeNotifier {
  final NotificationRepository _repository = NotificationRepository();
  List<AppNotification> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = false;
  String? _error;
  PaginationInfo? _pagination;

  List<AppNotification> get notifications => _notifications;
  int get unreadCount => _unreadCount;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasMore => _pagination?.hasMore ?? false;

  /// Load notifications
  Future<void> loadNotifications(int userId, {bool unreadOnly = false}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _repository.getNotifications(
        userId,
        unreadOnly: unreadOnly,
      );
      _notifications = response.notifications;
      _unreadCount = response.unreadCount;
      _pagination = response.pagination;
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load more notifications (pagination)
  Future<void> loadMore(int userId, {bool unreadOnly = false}) async {
    if (_pagination == null || !_pagination!.hasMore) return;

    try {
      final response = await _repository.getNotifications(
        userId,
        page: _pagination!.currentPage + 1,
        unreadOnly: unreadOnly,
      );
      _notifications.addAll(response.notifications);
      _pagination = response.pagination;
      notifyListeners();
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
    }
  }

  /// Mark notification as read
  Future<void> markAsRead(int userId, int notificationId) async {
    try {
      await _repository.markAsRead(userId, notificationId);
      
      // Update local state
      final index = _notifications.indexWhere((n) => n.id == notificationId);
      if (index != -1 && !_notifications[index].isRead) {
        _notifications[index] = AppNotification(
          id: _notifications[index].id,
          userId: _notifications[index].userId,
          type: _notifications[index].type,
          title: _notifications[index].title,
          message: _notifications[index].message,
          link: _notifications[index].link,
          isRead: true,
          readAt: DateTime.now().toString(),
          createdAt: _notifications[index].createdAt,
          timeAgo: _notifications[index].timeAgo,
        );
        _unreadCount = _unreadCount > 0 ? _unreadCount - 1 : 0;
        notifyListeners();
      }
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
    }
  }

  /// Mark all notifications as read
  Future<void> markAllAsRead(int userId) async {
    try {
      await _repository.markAllAsRead(userId);
      
      // Update local state
      _notifications = _notifications.map((n) {
        if (!n.isRead) {
          return AppNotification(
            id: n.id,
            userId: n.userId,
            type: n.type,
            title: n.title,
            message: n.message,
            link: n.link,
            isRead: true,
            readAt: DateTime.now().toString(),
            createdAt: n.createdAt,
            timeAgo: n.timeAgo,
          );
        }
        return n;
      }).toList();
      _unreadCount = 0;
      notifyListeners();
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
    }
  }

  /// Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }
}














