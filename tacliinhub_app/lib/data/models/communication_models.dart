class Announcement {
  final int id;
  final String title;
  final String content;
  final String?
  targetAudience; // 'All', 'Students', 'Teachers', 'Parents', 'Staff'
  final int? classId;
  final String? className;
  final String? attachmentUrl;
  final String status; // 'Draft', 'Published', 'Archived'
  final String createdBy;
  final String createdAt;
  final String? publishedAt;

  Announcement({
    required this.id,
    required this.title,
    required this.content,
    this.targetAudience,
    this.classId,
    this.className,
    this.attachmentUrl,
    required this.status,
    required this.createdBy,
    required this.createdAt,
    this.publishedAt,
  });

  factory Announcement.fromJson(Map<String, dynamic> json) {
    return Announcement(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      content: json['content'] ?? json['message'] ?? '',
      targetAudience: json['target_audience'] ?? json['targetAudience'],
      classId: json['class_id'] ?? json['classId'],
      className: json['class_name'] ?? json['className'],
      attachmentUrl: json['attachment_url'] ?? json['attachmentUrl'],
      status: json['status'] ?? 'Draft',
      createdBy: json['created_by'] ?? json['createdBy'] ?? '',
      createdAt: json['created_at'] ?? json['createdAt'] ?? '',
      publishedAt: json['published_at'] ?? json['publishedAt'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'content': content,
      'target_audience': targetAudience,
      'class_id': classId,
      'class_name': className,
      'attachment_url': attachmentUrl,
      'status': status,
      'created_by': createdBy,
      'created_at': createdAt,
      'published_at': publishedAt,
    };
  }
}

class Message {
  final int id;
  final int? fromUserId;
  final String? fromUserName;
  final int? toUserId;
  final String? toUserName;
  final String? toUserRole;
  final String subject;
  final String message;
  final String? attachmentUrl;
  final bool isRead;
  final String? readAt;
  final String createdAt;
  final String messageType; // 'Inbox', 'Sent', 'Draft'

  Message({
    required this.id,
    this.fromUserId,
    this.fromUserName,
    this.toUserId,
    this.toUserName,
    this.toUserRole,
    required this.subject,
    required this.message,
    this.attachmentUrl,
    required this.isRead,
    this.readAt,
    required this.createdAt,
    required this.messageType,
  });

  factory Message.fromJson(Map<String, dynamic> json) {
    return Message(
      id: json['id'] ?? 0,
      fromUserId: json['from_user_id'] ?? json['fromUserId'],
      fromUserName: json['from_user_name'] ?? json['fromUserName'],
      toUserId: json['to_user_id'] ?? json['toUserId'],
      toUserName: json['to_user_name'] ?? json['toUserName'],
      toUserRole: json['to_user_role'] ?? json['toUserRole'],
      subject: json['subject'] ?? '',
      message: json['message'] ?? json['content'] ?? '',
      attachmentUrl: json['attachment_url'] ?? json['attachmentUrl'],
      isRead:
          json['is_read'] == 1 ||
          json['is_read'] == true ||
          json['isRead'] == true,
      readAt: json['read_at'] ?? json['readAt'],
      createdAt: json['created_at'] ?? json['createdAt'] ?? '',
      messageType: json['message_type'] ?? json['messageType'] ?? 'Inbox',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'from_user_id': fromUserId,
      'from_user_name': fromUserName,
      'to_user_id': toUserId,
      'to_user_name': toUserName,
      'to_user_role': toUserRole,
      'subject': subject,
      'message': message,
      'attachment_url': attachmentUrl,
      'is_read': isRead ? 1 : 0,
      'read_at': readAt,
      'created_at': createdAt,
      'message_type': messageType,
    };
  }
}

class Sms {
  final int id;
  final String recipientType; // 'Student', 'Parent', 'Teacher', 'Staff', 'All'
  final int? recipientId;
  final String? recipientName;
  final String? recipientPhone;
  final String message;
  final String status; // 'Pending', 'Sent', 'Failed'
  final String? sentAt;
  final String? errorMessage;
  final String createdBy;
  final String createdAt;

  Sms({
    required this.id,
    required this.recipientType,
    this.recipientId,
    this.recipientName,
    this.recipientPhone,
    required this.message,
    required this.status,
    this.sentAt,
    this.errorMessage,
    required this.createdBy,
    required this.createdAt,
  });

  factory Sms.fromJson(Map<String, dynamic> json) {
    return Sms(
      id: json['id'] ?? 0,
      recipientType: json['recipient_type'] ?? json['recipientType'] ?? 'All',
      recipientId: json['recipient_id'] ?? json['recipientId'],
      recipientName: json['recipient_name'] ?? json['recipientName'],
      recipientPhone: json['recipient_phone'] ?? json['recipientPhone'],
      message: json['message'] ?? json['content'] ?? '',
      status: json['status'] ?? 'Pending',
      sentAt: json['sent_at'] ?? json['sentAt'],
      errorMessage: json['error_message'] ?? json['errorMessage'],
      createdBy: json['created_by'] ?? json['createdBy'] ?? '',
      createdAt: json['created_at'] ?? json['createdAt'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'recipient_type': recipientType,
      'recipient_id': recipientId,
      'recipient_name': recipientName,
      'recipient_phone': recipientPhone,
      'message': message,
      'status': status,
      'sent_at': sentAt,
      'error_message': errorMessage,
      'created_by': createdBy,
      'created_at': createdAt,
    };
  }
}

class Email {
  final int id;
  final String recipientType; // 'Student', 'Parent', 'Teacher', 'Staff', 'All'
  final int? recipientId;
  final String? recipientName;
  final String? recipientEmail;
  final String subject;
  final String body;
  final String? attachmentUrl;
  final String status; // 'Pending', 'Sent', 'Failed'
  final String? sentAt;
  final String? errorMessage;
  final String createdBy;
  final String createdAt;

  Email({
    required this.id,
    required this.recipientType,
    this.recipientId,
    this.recipientName,
    this.recipientEmail,
    required this.subject,
    required this.body,
    this.attachmentUrl,
    required this.status,
    this.sentAt,
    this.errorMessage,
    required this.createdBy,
    required this.createdAt,
  });

  factory Email.fromJson(Map<String, dynamic> json) {
    return Email(
      id: json['id'] ?? 0,
      recipientType: json['recipient_type'] ?? json['recipientType'] ?? 'All',
      recipientId: json['recipient_id'] ?? json['recipientId'],
      recipientName: json['recipient_name'] ?? json['recipientName'],
      recipientEmail: json['recipient_email'] ?? json['recipientEmail'],
      subject: json['subject'] ?? '',
      body: json['body'] ?? json['content'] ?? '',
      attachmentUrl: json['attachment_url'] ?? json['attachmentUrl'],
      status: json['status'] ?? 'Pending',
      sentAt: json['sent_at'] ?? json['sentAt'],
      errorMessage: json['error_message'] ?? json['errorMessage'],
      createdBy: json['created_by'] ?? json['createdBy'] ?? '',
      createdAt: json['created_at'] ?? json['createdAt'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'recipient_type': recipientType,
      'recipient_id': recipientId,
      'recipient_name': recipientName,
      'recipient_email': recipientEmail,
      'subject': subject,
      'body': body,
      'attachment_url': attachmentUrl,
      'status': status,
      'sent_at': sentAt,
      'error_message': errorMessage,
      'created_by': createdBy,
      'created_at': createdAt,
    };
  }
}
