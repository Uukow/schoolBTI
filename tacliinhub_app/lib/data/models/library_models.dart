/// Library Models for TacliinHub
library;

class Book {
  final int id;
  final String isbn;
  final String title;
  final String? author;
  final String? publisher;
  final String? edition;
  final String? category;
  final int totalCopies;
  final int availableCopies;
  final int issuedCopies;
  final String? description;
  final String? location;
  final String status; // Active, Inactive, Lost, Damaged
  final String? coverImage;
  final String createdAt;
  final String? updatedAt;

  Book({
    required this.id,
    required this.isbn,
    required this.title,
    this.author,
    this.publisher,
    this.edition,
    this.category,
    required this.totalCopies,
    required this.availableCopies,
    required this.issuedCopies,
    this.description,
    this.location,
    required this.status,
    this.coverImage,
    required this.createdAt,
    this.updatedAt,
  });

  factory Book.fromJson(Map<String, dynamic> json) {
    final totalCopies = _parseInt(json['quantity'] ?? json['total_copies'] ?? json['copies'] ?? 0);
    final availableCopies = _parseInt(json['available_quantity'] ?? json['available_copies'] ?? json['available'] ?? 0);
    final issuedCopies = totalCopies - availableCopies;
    
    return Book(
      id: _parseInt(json['id']),
      isbn: json['isbn'] ?? json['book_isbn'] ?? '',
      title: json['book_title'] ?? json['title'] ?? '',
      author: json['author'] ?? json['book_author'] ?? '',
      publisher: json['publisher'] ?? json['book_publisher'],
      edition: json['edition'] ?? json['book_edition'],
      category: json['category'] ?? json['book_category'],
      totalCopies: totalCopies,
      availableCopies: availableCopies,
      issuedCopies: issuedCopies,
      description: json['description'],
      location: json['location'] ?? json['book_location'],
      status: json['status'] ?? 'Active',
      coverImage: json['cover_image'] ?? json['cover'],
      createdAt: json['added_at'] ?? json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'isbn': isbn,
      'title': title,
      'author': author,
      'publisher': publisher,
      'edition': edition,
      'category': category,
      'total_copies': totalCopies,
      'available_copies': availableCopies,
      'issued_copies': issuedCopies,
      'description': description,
      'location': location,
      'status': status,
      'cover_image': coverImage,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }
}

class BookIssue {
  final int id;
  final int bookId;
  final String bookTitle;
  final String? bookIsbn;
  final int memberId; // Student ID or Staff ID
  final String memberName;
  final String memberType; // Student, Staff
  final String? memberIdNumber; // Admission No or Staff ID
  final String issueDate;
  final String? dueDate;
  final String? returnDate;
  final String status; // Issued, Returned, Overdue, Lost
  final double? fineAmount;
  final String? remarks;
  final int issuedBy;
  final String? issuedByName;
  final int? returnedBy;
  final String? returnedByName;
  final String createdAt;
  final String? updatedAt;

  BookIssue({
    required this.id,
    required this.bookId,
    required this.bookTitle,
    this.bookIsbn,
    required this.memberId,
    required this.memberName,
    required this.memberType,
    this.memberIdNumber,
    required this.issueDate,
    this.dueDate,
    this.returnDate,
    required this.status,
    this.fineAmount,
    this.remarks,
    required this.issuedBy,
    this.issuedByName,
    this.returnedBy,
    this.returnedByName,
    required this.createdAt,
    this.updatedAt,
  });

  factory BookIssue.fromJson(Map<String, dynamic> json) {
    return BookIssue(
      id: _parseInt(json['id']),
      bookId: _parseInt(json['book_id']),
      bookTitle: json['book_title'] ?? json['title'] ?? '',
      bookIsbn: json['book_isbn'] ?? json['isbn'],
      memberId: _parseInt(json['member_id'] ?? json['student_id'] ?? json['staff_id'] ?? 0),
      memberName: json['member_name'] ?? 
                  (json['student_name'] ?? json['staff_name'] ?? 
                   '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim()),
      memberType: json['member_type'] ?? 
                  (json['student_id'] != null ? 'Student' : 
                   json['staff_id'] != null ? 'Staff' : 'Student'),
      memberIdNumber: json['member_id_number'] ?? 
                       json['admission_no'] ?? 
                       json['staff_id_number'],
      issueDate: json['issue_date'] ?? json['issued_date'] ?? '',
      dueDate: json['due_date'],
      returnDate: json['return_date'] ?? json['returned_date'],
      status: json['status'] ?? 'Issued',
      fineAmount: json['fine_amount'] != null ? _parseDouble(json['fine_amount']) : null,
      remarks: json['remarks'] ?? json['notes'],
      issuedBy: _parseInt(json['issued_by'] ?? json['issued_by_user_id'] ?? 0),
      issuedByName: json['issued_by_name'] ?? json['issued_by_username'],
      returnedBy: json['returned_by'] != null ? _parseInt(json['returned_by']) : null,
      returnedByName: json['returned_by_name'] ?? json['returned_by_username'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'book_id': bookId,
      'book_title': bookTitle,
      'book_isbn': bookIsbn,
      'member_id': memberId,
      'member_name': memberName,
      'member_type': memberType,
      'member_id_number': memberIdNumber,
      'issue_date': issueDate,
      'due_date': dueDate,
      'return_date': returnDate,
      'status': status,
      'fine_amount': fineAmount,
      'remarks': remarks,
      'issued_by': issuedBy,
      'issued_by_name': issuedByName,
      'returned_by': returnedBy,
      'returned_by_name': returnedByName,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

class BookCategory {
  final int id;
  final String name;
  final String? description;
  final int bookCount;

  BookCategory({
    required this.id,
    required this.name,
    this.description,
    required this.bookCount,
  });

  factory BookCategory.fromJson(Map<String, dynamic> json) {
    return BookCategory(
      id: _parseInt(json['id']),
      name: json['name'] ?? json['category_name'] ?? '',
      description: json['description'],
      bookCount: _parseInt(json['book_count'] ?? json['count'] ?? 0),
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }
}

