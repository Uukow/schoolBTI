/// Notification Models for TacliinHub
library;

class NotificationResponse {
  final List<AppNotification> notifications;
  final int unreadCount;
  final PaginationInfo pagination;

  NotificationResponse({
    required this.notifications,
    required this.unreadCount,
    required this.pagination,
  });

  factory NotificationResponse.fromJson(Map<String, dynamic> json) {
    return NotificationResponse(
      notifications:
          (json['notifications'] as List<dynamic>?)
              ?.map((n) => AppNotification.fromJson(n))
              .toList() ??
          [],
      unreadCount: json['unread_count'] ?? 0,
      pagination: PaginationInfo.fromJson(json['pagination'] ?? {}),
    );
  }
}

class AppNotification {
  final int id;
  final int userId;
  final String type;
  final String title;
  final String message;
  final String? link;
  final bool isRead;
  final String? readAt;
  final String createdAt;
  final String timeAgo;

  AppNotification({
    required this.id,
    required this.userId,
    required this.type,
    required this.title,
    required this.message,
    this.link,
    required this.isRead,
    this.readAt,
    required this.createdAt,
    required this.timeAgo,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      type: json['type'] ?? 'info',
      title: json['title'] ?? '',
      message: json['message'] ?? '',
      link: json['link'],
      isRead: json['is_read'] == 1 || json['is_read'] == true,
      readAt: json['read_at'],
      createdAt: json['created_at'] ?? '',
      timeAgo: json['time_ago'] ?? '',
    );
  }
}

class PaginationInfo {
  final int currentPage;
  final int perPage;
  final int total;
  final int totalPages;
  final bool hasMore;

  PaginationInfo({
    required this.currentPage,
    required this.perPage,
    required this.total,
    required this.totalPages,
    required this.hasMore,
  });

  factory PaginationInfo.fromJson(Map<String, dynamic> json) {
    return PaginationInfo(
      currentPage: json['current_page'] ?? 1,
      perPage: json['per_page'] ?? 20,
      total: json['total'] ?? 0,
      totalPages: json['total_pages'] ?? 0,
      hasMore: json['has_more'] ?? false,
    );
  }
}












