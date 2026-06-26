import 'package:flutter/material.dart';

int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}

class SupportTicket {
  final int id;
  final String ticketNo;
  final int userId;
  final String? category;
  final String priority; // 'Low', 'Medium', 'High', 'Critical'
  final String subject;
  final String description;
  final String status; // 'Open', 'In Progress', 'Resolved', 'Closed', 'Reopened'
  final int? assignedTo;
  final String? assignedToName;
  final String? assignedToEmail;
  final String? resolution;
  final String createdAt;
  final String updatedAt;
  final String createdByName;
  final String? createdByEmail;
  final int replyCount;
  final List<TicketReply> replies;

  SupportTicket({
    required this.id,
    required this.ticketNo,
    required this.userId,
    this.category,
    required this.priority,
    required this.subject,
    required this.description,
    required this.status,
    this.assignedTo,
    this.assignedToName,
    this.assignedToEmail,
    this.resolution,
    required this.createdAt,
    required this.updatedAt,
    required this.createdByName,
    this.createdByEmail,
    this.replyCount = 0,
    this.replies = const [],
  });

  factory SupportTicket.fromJson(Map<String, dynamic> json) {
    return SupportTicket(
      id: _parseInt(json['id']),
      ticketNo: json['ticket_no'] ?? '',
      userId: _parseInt(json['user_id']),
      category: json['category'],
      priority: json['priority'] ?? 'Medium',
      subject: json['subject'] ?? '',
      description: json['description'] ?? '',
      status: json['status'] ?? 'Open',
      assignedTo: json['assigned_to'] != null ? _parseInt(json['assigned_to']) : null,
      assignedToName: json['assigned_to_name'],
      assignedToEmail: json['assigned_to_email'],
      resolution: json['resolution'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
      createdByName: json['created_by_name'] ?? '',
      createdByEmail: json['created_by_email'],
      replyCount: _parseInt(json['reply_count'] ?? 0),
      replies: (json['replies'] as List<dynamic>?)
              ?.map((r) => TicketReply.fromJson(r))
              .toList() ??
          [],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'subject': subject,
      'description': description,
      'category': category ?? 'General',
      'priority': priority,
    };
  }

  Color get priorityColor {
    switch (priority) {
      case 'Critical':
        return Colors.red;
      case 'High':
        return Colors.orange;
      case 'Medium':
        return Colors.blue;
      case 'Low':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  Color get statusColor {
    switch (status) {
      case 'Open':
        return Colors.blue;
      case 'In Progress':
        return Colors.orange;
      case 'Resolved':
        return Colors.green;
      case 'Closed':
        return Colors.grey;
      case 'Reopened':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }
}

class TicketReply {
  final int id;
  final int ticketId;
  final int userId;
  final String message;
  final String? attachment;
  final String createdAt;
  final String username;
  final String? email;
  final String? roleName;

  TicketReply({
    required this.id,
    required this.ticketId,
    required this.userId,
    required this.message,
    this.attachment,
    required this.createdAt,
    required this.username,
    this.email,
    this.roleName,
  });

  factory TicketReply.fromJson(Map<String, dynamic> json) {
    return TicketReply(
      id: _parseInt(json['id']),
      ticketId: _parseInt(json['ticket_id']),
      userId: _parseInt(json['user_id']),
      message: json['message'] ?? '',
      attachment: json['attachment'],
      createdAt: json['created_at'] ?? '',
      username: json['username'] ?? '',
      email: json['email'],
      roleName: json['role_name'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'ticket_id': ticketId,
      'message': message,
    };
  }
}

class TicketStats {
  final int total;
  final int open;
  final int inProgress;
  final int resolved;
  final int closed;
  final int reopened;
  final int critical;
  final int high;
  final int medium;
  final int low;

  TicketStats({
    required this.total,
    required this.open,
    required this.inProgress,
    required this.resolved,
    required this.closed,
    required this.reopened,
    required this.critical,
    required this.high,
    required this.medium,
    required this.low,
  });

  factory TicketStats.fromJson(Map<String, dynamic> json) {
    return TicketStats(
      total: _parseInt(json['total'] ?? 0),
      open: _parseInt(json['open'] ?? 0),
      inProgress: _parseInt(json['in_progress'] ?? 0),
      resolved: _parseInt(json['resolved'] ?? 0),
      closed: _parseInt(json['closed'] ?? 0),
      reopened: _parseInt(json['reopened'] ?? 0),
      critical: _parseInt(json['critical'] ?? 0),
      high: _parseInt(json['high'] ?? 0),
      medium: _parseInt(json['medium'] ?? 0),
      low: _parseInt(json['low'] ?? 0),
    );
  }
}

